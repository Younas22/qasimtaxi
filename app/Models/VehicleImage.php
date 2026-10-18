<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleImage extends Model
{
    protected $fillable = [
        'vehicle_id',
        'image',
        'sort_order',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('public/uploads/vehicles/'.$this->image) : null;
    }
}
