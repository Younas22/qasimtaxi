<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A browser/device's PushManager subscription. Polymorphic rather than
     * a plain user_id — this app has no single "users" table, Admin,
     * Customer, and Driver are entirely separate guards/models (see
     * config/auth.php). One person can hold many rows here: the same
     * account subscribed from desktop Chrome, a laptop, and a phone all get
     * their own row, each pushed to independently.
     */
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->morphs('subscribable'); // subscribable_type, subscribable_id — Admin|Customer|Driver

            // The endpoint URL is often 300+ chars (FCM/Mozilla endpoints
            // embed a long token) — too long to index directly under
            // MySQL's utf8mb4 767/3072-byte key-length limits, so a SHA-256
            // hash carries the uniqueness constraint while the full value
            // is kept in a TEXT column for actually sending the push.
            $table->text('endpoint');
            $table->string('endpoint_hash', 64)->unique();

            $table->text('public_key'); // p256dh
            $table->text('auth_token'); // auth
            $table->string('content_encoding')->nullable(); // aesgcm | aes128gcm

            $table->string('device_name')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();

            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            // morphs() above already indexes (subscribable_type, subscribable_id).
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
