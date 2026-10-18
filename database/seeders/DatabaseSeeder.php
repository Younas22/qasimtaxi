<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            LanguageSeeder::class,
            TranslationSeeder::class,
            CurrencySeeder::class,
            PaymentGatewaySeeder::class,
            NotificationSettingSeeder::class,
            VehicleCategorySeeder::class,
            PricingRuleSeeder::class,
            PopularRouteSeeder::class,
            CouponSeeder::class,
            PromotionSeeder::class,
            VehicleSeeder::class,
            DriverSeeder::class,
            PageSeeder::class,
            BlogCategorySeeder::class,
            LocationSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            AdminSeeder::class,
            BlogPostSeeder::class,
            DemoDataSeeder::class,
            ClientDemoSeeder::class,
            CustomerActivitySeeder::class,
        ]);
    }
}
