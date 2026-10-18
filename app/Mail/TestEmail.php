<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $mailerName, public Carbon $sentAt)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Email from '.setting('company_name', config('app.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test',
        );
    }
}
