<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestEmail;
use App\Models\EmailSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmailSettingController extends Controller
{
    public function edit(): View
    {
        $settings = EmailSetting::current();

        return view('admin.email-settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = EmailSetting::current();

        $data = $request->validate([
            'mailer' => ['required', Rule::in(array_keys(EmailSetting::MAILERS))],
            'resend_api_key' => ['nullable', 'string', 'max:500'],
            'from_address' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'admin_notification_email' => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                foreach (explode(',', $value) as $email) {
                    if (! filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                        $fail('Enter valid email address(es), separated by commas.');

                        return;
                    }
                }
            }],
        ]);

        if (blank($data['resend_api_key'] ?? null)) {
            unset($data['resend_api_key']);
        }

        $settings->fill($data);

        if ($settings->mailer === 'resend' && blank($settings->resend_api_key)) {
            throw ValidationException::withMessages([
                'resend_api_key' => 'A Resend API key is required while Resend is the selected mailer.',
            ]);
        }

        $settings->save();
        $settings->applyToRuntimeConfig();

        return redirect()
            ->route('admin.email-settings.edit')
            ->with('status', 'Email settings updated successfully.');
    }

    public function sendTest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        $settings = EmailSetting::current();
        $settings->applyToRuntimeConfig();

        try {
            Mail::to($data['test_email'])->send(new TestEmail($settings->mailer, Carbon::now()));
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send test email: '.$e->getMessage());
        }

        return back()->with('status', "Test email sent to {$data['test_email']} via {$settings->mailer}.");
    }
}
