<?php

namespace App\Jobs;

use App\Models\PushSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

/**
 * Does the actual Web Push delivery for one PushNotificationService::send()
 * call — deliberately the only place in the app that talks to
 * Minishlink\WebPush directly. Queued so a slow/unreachable push service
 * never adds latency to whatever triggered the notification (booking
 * creation, payment, etc.), and isolated in its own try/catch per
 * subscription so one bad endpoint never sinks the rest of the batch.
 */
class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    /**
     * @param  array<int, int>  $subscriptionIds
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public array $subscriptionIds,
        public array $payload
    ) {
    }

    public function handle(): void
    {
        $subscriptions = PushSubscription::whereIn('id', $this->subscriptionIds)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        // See AppServiceProvider::configureWebPushOpenSsl() — must run
        // before the first EC operation, which happens the moment WebPush
        // signs a VAPID JWT below.
        $this->applyWindowsOpenSslWorkaround();

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => config('webpush.subject'),
                    'publicKey' => config('webpush.public_key'),
                    'privateKey' => config('webpush.private_key'),
                ],
            ], [
                'TTL' => config('webpush.ttl'),
                // "high" makes FCM/APNs wake a locked, dozing phone and
                // deliver immediately — at the default "normal" urgency
                // Android batches the push until the device next wakes,
                // so it arrives late and without an audible alert.
                'urgency' => 'high',
            ]);
        } catch (Throwable $e) {
            Log::error('Web Push is not configured correctly: '.$e->getMessage());

            return;
        }

        $payloadJson = json_encode(array_filter($this->payload, fn ($value) => $value !== null));

        foreach ($subscriptions as $subscription) {
            try {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->public_key,
                        'authToken' => $subscription->auth_token,
                        'contentEncoding' => $subscription->content_encoding ?: 'aesgcm',
                    ]),
                    $payloadJson
                );
            } catch (Throwable $e) {
                Log::warning("Skipped malformed push subscription #{$subscription->id}: ".$e->getMessage());
            }
        }

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getEndpoint();

            if ($report->isSuccess()) {
                PushSubscription::where('endpoint_hash', PushSubscription::hashEndpoint($endpoint))
                    ->update(['last_used_at' => now()]);

                continue;
            }

            if ($report->isSubscriptionExpired()) {
                // 404/410 from the push service — the browser unsubscribed,
                // or the endpoint itself expired. No point retrying it.
                PushSubscription::where('endpoint_hash', PushSubscription::hashEndpoint($endpoint))->delete();

                continue;
            }

            Log::warning('Push delivery failed', [
                'endpoint' => $endpoint,
                'reason' => $report->getReason(),
            ]);
        }
    }

    /**
     * A push failure must never surface as a failed booking/payment
     * operation — this job runs fully detached from whatever triggered it,
     * so a queue-level failure here only ever produces a log entry.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('SendPushNotificationJob failed permanently: '.$exception->getMessage());
    }

    private function applyWindowsOpenSslWorkaround(): void
    {
        if (getenv('OPENSSL_CONF') || PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        foreach (array_filter([config('webpush.openssl_conf'), 'E:/sv26/apache/conf/openssl.cnf', 'C:/xampp/apache/conf/openssl.cnf']) as $path) {
            if (is_string($path) && file_exists($path)) {
                putenv('OPENSSL_CONF='.$path);

                return;
            }
        }
    }
}
