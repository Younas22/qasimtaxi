<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketCreatedNotification extends Notification
{
    public function __construct(public SupportTicket $ticket)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Support Ticket — {$this->ticket->ticket_number}")
            ->greeting('Hello '.$notifiable->name.',')
            ->line("A new support ticket has been raised: {$this->ticket->ticket_number}.")
            ->line("Customer: {$this->ticket->customer?->name}")
            ->line("Subject: {$this->ticket->subject}")
            ->action('View Ticket', route('admin.support-tickets.show', $this->ticket))
            ->salutation('— '.setting('company_name', config('app.name')));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'event_type' => 'support_ticket_created',
            'title' => 'New Support Ticket',
            'message' => "{$this->ticket->customer?->name} raised a new ticket: \"{$this->ticket->subject}\".",
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'url' => route('admin.support-tickets.show', $this->ticket),
        ];
    }
}
