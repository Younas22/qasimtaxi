<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketReply extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'customer_id',
        'admin_id',
        'message',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function isFromAdmin(): bool
    {
        return ! is_null($this->admin_id);
    }

    public function authorName(): string
    {
        return $this->isFromAdmin()
            ? ($this->admin?->name ?? 'Support Team')
            : ($this->customer?->name ?? 'You');
    }
}
