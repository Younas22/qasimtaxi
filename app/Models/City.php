<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'country_id',
        'state_id',
        'name',
        'google_place_id',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function airports(): HasMany
    {
        return $this->hasMany(Airport::class);
    }

    public function trainStations(): HasMany
    {
        return $this->hasMany(TrainStation::class);
    }

    public function pickupPoints(): HasMany
    {
        return $this->hasMany(PickupPoint::class);
    }
}
