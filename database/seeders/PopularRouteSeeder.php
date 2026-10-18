<?php

namespace Database\Seeders;

use App\Models\PopularRoute;
use App\Models\RouteType;
use Illuminate\Database\Seeder;

class PopularRouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $typeIds = RouteType::pluck('id', 'slug');

        $routes = [
            ['type' => 'airport', 'pickup' => 'JFK International Airport', 'dropoff' => 'Manhattan, NYC', 'distance' => 26, 'estimated_price' => 95],
            ['type' => 'airport', 'pickup' => 'LaGuardia Airport', 'dropoff' => 'Downtown Brooklyn', 'distance' => 18, 'estimated_price' => 70],
            ['type' => 'airport', 'pickup' => 'Newark Liberty Airport', 'dropoff' => 'Times Square, NYC', 'distance' => 24, 'estimated_price' => 90],
            ['type' => 'city', 'pickup' => 'Times Square', 'dropoff' => 'Central Park', 'distance' => 3, 'estimated_price' => 35],
            ['type' => 'city', 'pickup' => 'Wall Street', 'dropoff' => 'Brooklyn Bridge', 'distance' => 4, 'estimated_price' => 38],
            ['type' => 'city', 'pickup' => 'Upper East Side', 'dropoff' => 'SoHo', 'distance' => 6, 'estimated_price' => 42],
            ['type' => 'intercity', 'pickup' => 'New York City', 'dropoff' => 'Philadelphia, PA', 'distance' => 154, 'estimated_price' => 320],
            ['type' => 'intercity', 'pickup' => 'New York City', 'dropoff' => 'Boston, MA', 'distance' => 346, 'estimated_price' => 620],
            ['type' => 'intercity', 'pickup' => 'New York City', 'dropoff' => 'Washington, D.C.', 'distance' => 362, 'estimated_price' => 650],
        ];

        foreach ($routes as $route) {
            $routeTypeId = $typeIds[$route['type']] ?? $typeIds->first();
            unset($route['type']);

            PopularRoute::firstOrCreate(
                ['pickup' => $route['pickup'], 'dropoff' => $route['dropoff']],
                $route + ['route_type_id' => $routeTypeId, 'distance_unit' => 'mi', 'is_active' => true]
            );
        }
    }
}
