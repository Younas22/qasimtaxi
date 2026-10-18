<?php

namespace App\Notifications\Customer;

use App\Models\SupportTicket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketRepliedNotification extends Notification
{
    public function __construct(public SupportTicket $ticket)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email_notifications_enabled) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Reply — {$this->ticket->ticket_number}")
            ->greeting('Hello '.$notifiable->name.',')
            ->line("Our support team replied to your ticket {$this->ticket->ticket_number}: \"{$this->ticket->subject}\".")
            ->action('View Ticket', route('customer.support.show', $this->ticket))
            ->salutation('— '.setting('company_name', config('app.name')));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'event_type' => 'support_ticket_replied',
            'title' => 'New Support Reply',
            'message' => "Our support team replied to your ticket {$this->ticket->ticket_number}.",
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'url' => route('customer.support.show', $this->ticket),
        ];
    }
}
