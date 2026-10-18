<?php

namespace Database\Seeders;

use App\Models\Airport;
use App\Models\City;
use App\Models\Country;
use App\Models\PickupPoint;
use App\Models\State;
use App\Models\TrainStation;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usa = Country::firstOrCreate(['code' => 'US'], ['name' => 'United States']);
        $uae = Country::firstOrCreate(['code' => 'AE'], ['name' => 'United Arab Emirates']);

        $california = State::firstOrCreate(['country_id' => $usa->id, 'name' => 'California'], ['code' => 'CA']);
        $newYork = State::firstOrCreate(['country_id' => $usa->id, 'name' => 'New York'], ['code' => 'NY']);
        $dubaiEmirate = State::firstOrCreate(['country_id' => $uae->id, 'name' => 'Dubai'], ['code' => 'DU']);

        $losAngeles = City::firstOrCreate(
            ['state_id' => $california->id, 'name' => 'Los Angeles'],
            ['country_id' => $usa->id]
        );
        $newYorkCity = City::firstOrCreate(
            ['state_id' => $newYork->id, 'name' => 'New York City'],
            ['country_id' => $usa->id]
        );
        $dubaiCity = City::firstOrCreate(
            ['state_id' => $dubaiEmirate->id, 'name' => 'Dubai'],
            ['country_id' => $uae->id]
        );

        Airport::firstOrCreate(['code' => 'LAX'], ['city_id' => $losAngeles->id, 'name' => 'Los Angeles International Airport']);
        Airport::firstOrCreate(['code' => 'JFK'], ['city_id' => $newYorkCity->id, 'name' => 'John F. Kennedy International Airport']);
        Airport::firstOrCreate(['code' => 'DXB'], ['city_id' => $dubaiCity->id, 'name' => 'Dubai International Airport']);

        TrainStation::firstOrCreate(
            ['city_id' => $losAngeles->id, 'name' => 'Los Angeles Union Station'],
            ['code' => 'LAUS']
        );
        TrainStation::firstOrCreate(
            ['city_id' => $newYorkCity->id, 'name' => 'Grand Central Terminal'],
            ['code' => 'GCT']
        );

        PickupPoint::firstOrCreate(
            ['city_id' => $losAngeles->id, 'name' => 'The Beverly Hills Hotel'],
            ['type' => 'hotel', 'address' => '9641 Sunset Blvd, Beverly Hills, CA 90210']
        );
        PickupPoint::firstOrCreate(
            ['city_id' => $newYorkCity->id, 'name' => 'The Plaza Hotel'],
            ['type' => 'hotel', 'address' => '768 5th Ave, New York, NY 10019']
        );
        PickupPoint::firstOrCreate(
            ['city_id' => $dubaiCity->id, 'name' => 'Burj Al Arab'],
            ['type' => 'landmark', 'address' => 'Jumeirah St, Dubai']
        );
    }
}
