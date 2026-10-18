<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_enabled',
        'mode',
        'sandbox_key_1',
        'sandbox_key_2',
        'live_key_1',
        'live_key_2',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sandbox_key_1' => 'encrypted',
            'sandbox_key_2' => 'encrypted',
            'live_key_1' => 'encrypted',
            'live_key_2' => 'encrypted',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function config(): array
    {
        return config('payment_gateways.gateways.'.$this->code, []);
    }

    public function isLive(): bool
    {
        return $this->mode === 'live';
    }

    public function activeKey1(): ?string
    {
        return $this->isLive() ? $this->live_key_1 : $this->sandbox_key_1;
    }

    public function activeKey2(): ?string
    {
        return $this->isLive() ? $this->live_key_2 : $this->sandbox_key_2;
    }

    /**
     * Whether both credentials for the currently selected mode are present,
     * regardless of the enabled flag — i.e. this gateway is safe to enable.
     */
    public function hasActiveKeys(): bool
    {
        return filled($this->activeKey1()) && filled($this->activeKey2());
    }

    /**
     * Whether this gateway is enabled and has both credentials for its
     * currently selected mode — i.e. actually usable to process a payment.
     */
    public function isReady(): bool
    {
        return $this->is_enabled && $this->hasActiveKeys();
    }
}
