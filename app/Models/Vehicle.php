<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_category_id',
        'name',
        'brand',
        'model',
        'year',
        'plate_number',
        'seats',
        'luggage',
        'transmission',
        'fuel_type',
        'description',
        'image',
        'base_fare',
        'price_per_km',
        'price_per_hour',
        'airport_price',
        'night_charges',
        'has_wifi',
        'has_water',
        'has_charger',
        'has_baby_seat',
        'has_ac',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'has_wifi' => 'boolean',
            'has_water' => 'boolean',
            'has_charger' => 'boolean',
            'has_baby_seat' => 'boolean',
            'has_ac' => 'boolean',
            'base_fare' => 'decimal:2',
            'price_per_km' => 'decimal:2',
            'price_per_hour' => 'decimal:2',
            'airport_price' => 'decimal:2',
            'night_charges' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class)->orderBy('sort_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function getAverageRatingAttribute(): ?float
    {
        $average = $this->reviews()->approved()->avg('rating');

        return $average ? round((float) $average, 1) : null;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('public/uploads/vehicles/'.$this->image) : null;
    }

    /** @return array<int, string> */
    public function getFeatureListAttribute(): array
    {
        $labels = [
            'has_wifi' => 'WiFi',
            'has_water' => 'Water',
            'has_charger' => 'Charger',
            'has_baby_seat' => 'Baby Seat',
            'has_ac' => 'AC',
        ];

        return collect($labels)
            ->filter(fn ($label, $key) => $this->{$key})
            ->values()
            ->all();
    }
}
