<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('payment_gateways.gateways', []) as $code => $gateway) {
            PaymentGateway::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $gateway['name'],
                    'is_enabled' => false,
                    'mode' => 'sandbox',
                ]
            );
        }
    }
}
