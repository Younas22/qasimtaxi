<?php

namespace Tests\Unit;

use App\Models\PricingRule;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_stepped_distance_pricing_uses_requested_km_bands(): void
    {
        $rule = new PricingRule([
            'km_fare' => 2.3,
            'mid_distance_threshold_km' => 50,
            'mid_distance_km_fare' => 1.7,
            'long_distance_threshold_km' => 100,
            'long_distance_km_fare' => 1.5,
            'very_long_distance_threshold_km' => 200,
            'very_long_distance_km_fare' => 1.25,
        ]);

        $this->assertSame(2.3, $rule->effectiveKmFare(10));
        $this->assertSame(1.7, $rule->effectiveKmFare(70));
        $this->assertSame(1.5, $rule->effectiveKmFare(120));
        $this->assertSame(1.25, $rule->effectiveKmFare(250));
        $this->assertSame(149.0, $rule->distanceFare(70));
        $this->assertSame(230.0, $rule->distanceFare(120));
        $this->assertSame(412.5, $rule->distanceFare(250));
    }
}
