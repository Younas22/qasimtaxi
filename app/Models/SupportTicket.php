<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory;

    public const STATUSES = [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'closed' => 'Closed',
    ];

    protected $fillable = [
        'ticket_number',
        'customer_id',
        'booking_id',
        'subject',
        'message',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket) {
            $ticket->ticket_number ??= 'TKT-'.strtoupper(Str::random(8));
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class)->oldest();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}
