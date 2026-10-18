<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ISO country reference data for the booking widget's phone country-code
 * selector. Deliberately separate from App\Models\Country (the admin
 * Location feature's country/state/city hierarchy) — different purpose,
 * different table.
 */
class DialCode extends Model
{
    protected $table = 'dial_codes';

    public $timestamps = false;

    protected $fillable = [
        'name', 'iso3', 'iso2', 'phone_code', 'capital',
        'currency', 'currency_symbol', 'latitude', 'longitude', 'region', 'subregion',
    ];

    public function getDialAttribute(): string
    {
        return '+'.ltrim((string) $this->phone_code, '+');
    }
}
