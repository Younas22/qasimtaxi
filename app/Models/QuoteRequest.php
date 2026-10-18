<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A logged snapshot of a live quote calculated on the public booking widget
 * — purely for analytics/follow-up (e.g. abandoned-quote marketing). Never
 * read back by the calculator itself; each row is a point-in-time record.
 */
class QuoteRequest extends Model
{
    protected $fillable = [
        'pickup_location',
        'pickup_lat',
        'pickup_lng',
        'pickup_place_id',
        'dropoff_location',
        'dropoff_lat',
        'dropoff_lng',
        'dropoff_place_id',
        'vehicle_category_id',
        'type',
        'distance_km',
        'duration_minutes',
        'return_pickup_location',
        'return_pickup_lat',
        'return_pickup_lng',
        'return_pickup_place_id',
        'return_dropoff_location',
        'return_dropoff_lat',
        'return_dropoff_lng',
        'return_dropoff_place_id',
        'return_distance_km',
        'return_duration_minutes',
        'hours',
        'passengers',
        'fare_breakdown',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'pickup_lat' => 'decimal:7',
            'pickup_lng' => 'decimal:7',
            'dropoff_lat' => 'decimal:7',
            'dropoff_lng' => 'decimal:7',
            'return_pickup_lat' => 'decimal:7',
            'return_pickup_lng' => 'decimal:7',
            'return_dropoff_lat' => 'decimal:7',
            'return_dropoff_lng' => 'decimal:7',
            'distance_km' => 'decimal:2',
            'return_distance_km' => 'decimal:2',
            'fare_breakdown' => 'array',
            'total' => 'decimal:2',
        ];
    }

    public function vehicleCategory(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class);
    }
}
