<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\PageSection;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Services\BookingFareCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Turns this install into a polished, client-facing "LimoSchedule" demo:
 * a fictional NYC chauffeur brand, 9 named vehicles, 3 customer + 3 driver
 * demo logins, ~18 realistic bookings, reviews, homepage/about/services
 * copy, team, and FAQ content.
 *
 * Deliberately additive — every write here is keyed by a unique value
 * (email, plate number, page-section id) via updateOrCreate/firstOrCreate,
 * so re-running this seeder never duplicates records. It never touches
 * pre-existing non-demo records (e.g. a real driver/customer/vehicle that
 * predates this demo pass) — those are left exactly as found.
 */
class ClientDemoSeeder extends Seeder
{
    /** @var array<string, Vehicle> keyed by plate number */
    private array $vehicles = [];

    /** @var array<string, Customer> keyed by email */
    private array $customers = [];

    /** @var array<string, Driver> keyed by email */
    private array $drivers = [];

    public function run(): void
    {
        $this->seedSettings();
        // Ensures the 6 generic vehicle categories (Sedan/SUV/Van/Luxury/...)
        // exist — idempotent (firstOrCreate by slug), safe even if this
        // install's categories were only ever partially seeded.
        $this->call(VehicleCategorySeeder::class);
        // Backfills a PricingRule per category now that the categories
        // above actually exist — this is why every vehicle card was
        // showing "Starting from $0.00" (falling back to the $0 global
        // rule) before this ran.
        $this->call(PricingRuleSeeder::class);
        $this->seedVehicles();
        $this->seedCustomers();
        $this->seedDrivers();
        $this->seedBookingsAndReviews();
        $this->seedHomepageAndAboutContent();
        $this->seedTeamAndFaq();
        $this->seedBlogPosts();
        $this->syncContactPageContent();
        $this->rewritePriorBusinessCopy();
        $this->rewriteAreas();
        $this->seedCurrency();
        $this->seedPopularRoutes();
    }

    /**
     * Rebrands the site's Settings row as the fictional "LimoSchedule"
     * NYC chauffeur brand used throughout this demo (explicitly requested
     * — see the demo brief's About/Contact sections).
     */
    private function seedSettings(): void
    {
        Setting::current()->update([
            'company_name' => 'LimoSchedule',
            'business_type' => 'chauffeur',
            'tagline' => 'Premium Chauffeur & Limousine Service',
            'meta_title' => 'LimoSchedule — Premium Chauffeur Service in New York',
            'meta_description' => 'Book a professional chauffeur in minutes. Airport transfers, corporate travel, and luxury rides across New York City with transparent, upfront pricing.',
            'meta_keywords' => 'limo service, chauffeur service, airport transportation, luxury transportation, corporate transportation, New York limousine',
            'address' => 'New York, NY',
            'office_lat' => 40.7580,
            'office_lng' => -73.9855,
            'timezone' => 'America/New_York',
            'default_country' => 'US',
            'email' => 'info@limoschedule.com',
            'phone' => '+1 (212) 555-0188',
            'whatsapp' => '+1 (212) 555-0188',
            'google_maps_embed_url' => 'https://www.google.com/maps?q=Times+Square,+New+York,+NY&output=embed',
            'business_hours' => collect(Setting::DAYS)->mapWithKeys(fn ($day) => [
                $day => ['open' => '00:00', 'close' => '23:59', 'closed' => false],
            ])->all(),
        ]);
    }

    /**
     * 9 named demo vehicles. Category assignment deliberately reuses the
     * site's existing 6 generic categories (Sedan/SUV/Van/Luxury) rather
     * than inventing new ones, chosen so every service page's existing
     * fleet-by-category filter (see PageSeeder::servicePageSections) still
     * resolves to real vehicles instead of an empty state. The requested
     * "Luxury Sedan" / "Premium SUV" / etc. type distinction is carried in
     * each vehicle's name/description instead of a separate category.
     */
    private function seedVehicles(): void
    {
        $categoryIds = VehicleCategory::pluck('id', 'slug');
        $image = 'vehicle-1785665666-qasim-front.webp'; // the one existing vehicle photo, reused per instruction

        $vehicles = [
            [
                'plate_number' => 'LMS-1001', 'name' => 'Mercedes-Benz S-Class', 'brand' => 'Mercedes-Benz', 'model' => 'S-Class', 'year' => 2024,
                'category' => 'luxury', 'seats' => 3, 'luggage' => 2,
                'description' => 'Our flagship luxury sedan. Whisper-quiet, impeccably appointed, and the natural choice for executive travel, VIP arrivals, and airport transfers where first impressions matter.',
                'base_fare' => 55, 'price_per_km' => 3.20, 'price_per_hour' => 65, 'airport_price' => 95, 'night_charges' => 20,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => false,
            ],
            [
                'plate_number' => 'LMS-1002', 'name' => 'Cadillac Escalade', 'brand' => 'Cadillac', 'model' => 'Escalade', 'year' => 2024,
                'category' => 'suv', 'seats' => 6, 'luggage' => 5,
                'description' => 'A commanding luxury SUV with three rows of premium seating — ideal for families, small groups, and clients who need extra room without compromising on comfort.',
                'base_fare' => 70, 'price_per_km' => 3.80, 'price_per_hour' => 80, 'airport_price' => 115, 'night_charges' => 25,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => true,
            ],
            [
                'plate_number' => 'LMS-1003', 'name' => 'Chevrolet Suburban', 'brand' => 'Chevrolet', 'model' => 'Suburban', 'year' => 2023,
                'category' => 'suv', 'seats' => 6, 'luggage' => 5,
                'description' => 'A spacious, dependable premium SUV built for group airport runs and full-day charters, with generous cargo room for luggage-heavy trips.',
                'base_fare' => 65, 'price_per_km' => 3.50, 'price_per_hour' => 72, 'airport_price' => 105, 'night_charges' => 22,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => true,
            ],
            [
                'plate_number' => 'LMS-1004', 'name' => 'Lincoln Navigator', 'brand' => 'Lincoln', 'model' => 'Navigator', 'year' => 2024,
                'category' => 'suv', 'seats' => 6, 'luggage' => 5,
                'description' => 'American luxury SUV craftsmanship with a hushed cabin and captain\'s-chair seating — a favorite for corporate teams and special-occasion group travel.',
                'base_fare' => 68, 'price_per_km' => 3.60, 'price_per_hour' => 75, 'airport_price' => 110, 'night_charges' => 24,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => true,
            ],
            [
                'plate_number' => 'LMS-1005', 'name' => 'Mercedes-Benz Sprinter', 'brand' => 'Mercedes-Benz', 'model' => 'Sprinter', 'year' => 2023,
                'category' => 'van', 'seats' => 10, 'luggage' => 8,
                'description' => 'Our executive-class passenger van, configured with airline-style captain\'s seating — built for larger corporate groups, wedding parties, and airport shuttle runs.',
                'base_fare' => 75, 'price_per_km' => 3.00, 'price_per_hour' => 85, 'airport_price' => 120, 'night_charges' => 28,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => true,
            ],
            [
                'plate_number' => 'LMS-1006', 'name' => 'BMW 7 Series', 'brand' => 'BMW', 'model' => '7 Series', 'year' => 2024,
                'category' => 'luxury', 'seats' => 3, 'luggage' => 2,
                'description' => 'A driver-focused luxury sedan with a serene rear cabin — precise, refined, and equally at home on a corporate transfer or a quiet evening ride.',
                'base_fare' => 58, 'price_per_km' => 3.30, 'price_per_hour' => 68, 'airport_price' => 98, 'night_charges' => 21,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => false,
            ],
            [
                'plate_number' => 'LMS-1007', 'name' => 'GMC Yukon XL', 'brand' => 'GMC', 'model' => 'Yukon XL', 'year' => 2023,
                'category' => 'suv', 'seats' => 6, 'luggage' => 5,
                'description' => 'An extended-length luxury SUV with class-leading cargo space — the practical pick for groups traveling with extra bags, equipment, or gear.',
                'base_fare' => 66, 'price_per_km' => 3.50, 'price_per_hour' => 73, 'airport_price' => 108, 'night_charges' => 23,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => true,
            ],
            [
                'plate_number' => 'LMS-1008', 'name' => 'Lincoln Continental', 'brand' => 'Lincoln', 'model' => 'Continental', 'year' => 2022,
                'category' => 'sedan', 'seats' => 3, 'luggage' => 2,
                'description' => 'A classic premium sedan with a smooth, composed ride — a reliable, elegant choice for everyday point-to-point trips and business travel.',
                'base_fare' => 50, 'price_per_km' => 2.90, 'price_per_hour' => 58, 'airport_price' => 88, 'night_charges' => 18,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => false, 'has_baby_seat' => false,
            ],
            [
                'plate_number' => 'LMS-1009', 'name' => 'Mercedes-Benz V-Class', 'brand' => 'Mercedes-Benz', 'model' => 'V-Class', 'year' => 2024,
                'category' => 'van', 'seats' => 7, 'luggage' => 6,
                'description' => 'A luxury van that seats like a limousine — lounge-style rear seating in a compact footprint, perfect for small groups who still want a premium experience.',
                'base_fare' => 62, 'price_per_km' => 3.10, 'price_per_hour' => 70, 'airport_price' => 100, 'night_charges' => 20,
                'has_wifi' => true, 'has_water' => true, 'has_charger' => true, 'has_baby_seat' => true,
            ],
        ];

        foreach ($vehicles as $attributes) {
            $category = $attributes['category'];
            unset($attributes['category']);

            $vehicle = Vehicle::updateOrCreate(
                ['plate_number' => $attributes['plate_number']],
                $attributes + [
                    'vehicle_category_id' => $categoryIds[$category] ?? null,
                    'transmission' => 'automatic',
                    'fuel_type' => 'petrol',
                    'image' => $image,
                    'has_ac' => true,
                    'status' => true,
                ]
            );

            $this->vehicles[$vehicle->plate_number] = $vehicle;
        }
    }

    /**
     * The 3 required demo customer logins. Renames/updates the
     * already-present "Demo Customer" (customer@limoschedule.com) row in
     * place rather than creating a duplicate, then adds the other two.
     */
    private function seedCustomers(): void
    {
        $customers = [
            ['email' => 'customer@limoschedule.com', 'password' => 'democustomer', 'name' => 'John Anderson', 'phone' => '+1 (646) 555-0110'],
            ['email' => 'customer1@limoschedule.com', 'password' => 'democustomer1', 'name' => 'Michael Carter', 'phone' => '+1 (646) 555-0121'],
            ['email' => 'customer2@limoschedule.com', 'password' => 'democustomer2', 'name' => 'Daniel Wilson', 'phone' => '+1 (646) 555-0132'],
        ];

        foreach ($customers as $attributes) {
            $email = $attributes['email'];
            $password = $attributes['password'];
            unset($attributes['email'], $attributes['password']);

            $customer = Customer::updateOrCreate(
                ['email' => $email],
                $attributes + [
                    'password' => $password, // hashed automatically via the model's 'hashed' cast
                    'status' => true,
                    'email_verified_at' => now(),
                ]
            );

            $this->customers[$email] = $customer;
        }
    }

    /**
     * The 3 required demo driver logins, each assigned one of the new
     * demo vehicles.
     */
    private function seedDrivers(): void
    {
        $drivers = [
            [
                'email' => 'driver@limoschedule.com', 'password' => 'demodriver', 'name' => 'James Miller', 'phone' => '+1 (718) 555-0143',
                'vehicle_plate' => 'LMS-1001', 'license_number' => 'NY-DL-884521', 'is_online' => true, 'is_available' => true,
            ],
            [
                'email' => 'driver1@limoschedule.com', 'password' => 'demodriver1', 'name' => 'Robert Davis', 'phone' => '+1 (718) 555-0154',
                'vehicle_plate' => 'LMS-1002', 'license_number' => 'NY-DL-884522', 'is_online' => true, 'is_available' => false,
            ],
            [
                'email' => 'driver2@limoschedule.com', 'password' => 'demodriver2', 'name' => 'William Thompson', 'phone' => '+1 (718) 555-0165',
                'vehicle_plate' => 'LMS-1005', 'license_number' => 'NY-DL-884523', 'is_online' => false, 'is_available' => false,
            ],
        ];

        foreach ($drivers as $attributes) {
            $email = $attributes['email'];
            $password = $attributes['password'];
            $vehicle = $this->vehicles[$attributes['vehicle_plate']] ?? null;
            unset($attributes['email'], $attributes['password'], $attributes['vehicle_plate']);

            $driver = Driver::updateOrCreate(
                ['email' => $email],
                $attributes + [
                    'password' => $password,
                    'address' => 'New York, NY',
                    'passport_number' => 'US'.fake()->numerify('#######'),
                    'national_id' => fake()->numerify('###-##-####'),
                    'commission_rate' => 20,
                    'vehicle_id' => $vehicle?->id,
                    'status' => true,
                ]
            );

            $this->drivers[$email] = $driver;
        }
    }

    /**
     * ~18 realistic NYC bookings across every status/type, tied only to
     * the demo customers/drivers/vehicles above — plus a hand-written,
     * naturally-worded review for every completed one (richer and more
     * varied than CustomerActivitySeeder's generic comment pool, which
     * still safely no-ops here afterward since it also keys on
     * customer_id + booking_id).
     */
    private function seedBookingsAndReviews(): void
    {
        $customer = fn (string $email) => $this->customers[$email];
        $driver = fn (string $email) => $this->drivers[$email];
        $vehicle = fn (string $plate) => $this->vehicles[$plate];

        $c1 = 'customer@limoschedule.com';
        $c2 = 'customer1@limoschedule.com';
        $c3 = 'customer2@limoschedule.com';
        $d1 = 'driver@limoschedule.com';
        $d2 = 'driver1@limoschedule.com';
        $d3 = 'driver2@limoschedule.com';

        $now = Carbon::now();

        $plan = [
            // --- Completed trips (drive reviews + revenue history) ---
            ['ref' => 'DEMO-01', 'customer' => $c2, 'driver' => $d1, 'vehicle' => 'LMS-1001', 'type' => 'airport_transfer', 'pickup' => 'John F. Kennedy International Airport, Queens, NY', 'dropoff' => 'Midtown Manhattan, NY', 'distance' => 26.4, 'when' => (clone $now)->subDays(14)->setTime(9, 30), 'status' => 'completed', 'payment' => 'paid',
                'review' => ['rating' => 5, 'comment' => "Flight landed early and our driver was already there waiting with a sign. Smooth pickup, spotless S-Class, and we made it to our hotel in great time.", 'featured' => true]],
            ['ref' => 'DEMO-02', 'customer' => $c3, 'driver' => $d2, 'vehicle' => 'LMS-1002', 'type' => 'airport_transfer', 'pickup' => 'Midtown Manhattan, NY', 'dropoff' => 'LaGuardia Airport, East Elmhurst, NY', 'distance' => 14.8, 'when' => (clone $now)->subDays(12)->setTime(6, 0), 'status' => 'completed', 'payment' => 'paid',
                'review' => ['rating' => 5, 'comment' => "Booked this for an early morning flight and everything went exactly as planned. Driver confirmed the pickup the night before, which put my mind at ease.", 'featured' => false]],
            ['ref' => 'DEMO-03', 'customer' => $c1, 'driver' => $d3, 'vehicle' => 'LMS-1008', 'type' => 'one_way', 'pickup' => 'Wall Street, Manhattan, NY', 'dropoff' => 'Times Square, Manhattan, NY', 'distance' => 8.2, 'when' => (clone $now)->subDays(11)->setTime(18, 15), 'status' => 'completed', 'payment' => 'paid',
                'review' => ['rating' => 4, 'comment' => "Easy to book on the website and the confirmation email had everything I needed. Car was clean and the ride across town was comfortable.", 'featured' => true]],
            ['ref' => 'DEMO-04', 'customer' => $c2, 'driver' => $d1, 'vehicle' => 'LMS-1006', 'type' => 'corporate', 'pickup' => 'Midtown Manhattan, NY', 'dropoff' => 'Newark Liberty International Airport, Newark, NJ', 'distance' => 24.1, 'when' => (clone $now)->subDays(10)->setTime(13, 0), 'status' => 'completed', 'payment' => 'paid',
                'review' => ['rating' => 5, 'comment' => "We use this for all of our executive airport runs now. The chauffeur was in a suit, opened the door for our client, and didn't say a word unless spoken to — exactly the professional image we wanted.", 'featured' => false]],
            ['ref' => 'DEMO-05', 'customer' => $c3, 'driver' => $d2, 'vehicle' => 'LMS-1003', 'type' => 'hourly', 'pickup' => 'Central Park, Manhattan, NY', 'dropoff' => 'Central Park, Manhattan, NY', 'hours' => 4, 'when' => (clone $now)->subDays(9)->setTime(11, 0), 'status' => 'completed', 'payment' => 'paid',
                'review' => ['rating' => 5, 'comment' => "Hired the Suburban by the hour for a day of meetings around the city. Driver waited at each stop without any hassle and the vehicle had plenty of room for our bags and samples.", 'featured' => true]],
            ['ref' => 'DEMO-06', 'customer' => $c1, 'driver' => $d3, 'vehicle' => 'LMS-1004', 'type' => 'round_trip', 'pickup' => 'Manhattan Cruise Terminal, Manhattan, NY', 'dropoff' => 'Brooklyn Heights, Brooklyn, NY', 'distance' => 9.6, 'when' => (clone $now)->subDays(8)->setTime(15, 45), 'status' => 'completed', 'payment' => 'paid',
                'review' => ['rating' => 4, 'comment' => "Picked us up from the cruise terminal with all our luggage and brought us home, then came back a week later for the return trip right on schedule. Very reliable.", 'featured' => false]],
            ['ref' => 'DEMO-07', 'customer' => $c2, 'driver' => $d2, 'vehicle' => 'LMS-1005', 'type' => 'airport_transfer', 'pickup' => 'Newark Liberty International Airport, Newark, NJ', 'dropoff' => 'Times Square, Manhattan, NY', 'distance' => 27.3, 'when' => (clone $now)->subDays(7)->setTime(20, 30), 'status' => 'completed', 'payment' => 'paid',
                'review' => ['rating' => 5, 'comment' => "Our whole family of six plus luggage fit comfortably in the Sprinter. The kids were tired after the flight and the driver was patient and helpful getting everyone loaded up.", 'featured' => true]],
            ['ref' => 'DEMO-08', 'customer' => $c3, 'driver' => $d1, 'vehicle' => 'LMS-1009', 'type' => 'one_way', 'pickup' => 'The Plaza Hotel, Midtown Manhattan, NY', 'dropoff' => 'John F. Kennedy International Airport, Queens, NY', 'distance' => 27.9, 'when' => (clone $now)->subDays(5)->setTime(7, 15), 'status' => 'completed', 'payment' => 'paid',
                'review' => ['rating' => 5, 'comment' => "The hotel concierge recommended this service and I understand why. Driver texted me 20 minutes before arrival and helped with every bag. Would book again in a heartbeat.", 'featured' => false]],
            ['ref' => 'DEMO-09', 'customer' => $c1, 'driver' => $d1, 'vehicle' => 'LMS-1007', 'type' => 'corporate', 'pickup' => 'Wall Street, Manhattan, NY', 'dropoff' => 'One World Trade Center, Manhattan, NY', 'distance' => 1.4, 'when' => (clone $now)->subDays(4)->setTime(9, 0), 'status' => 'completed', 'payment' => 'refunded',
                'review' => ['rating' => 4, 'comment' => "Short trip but the driver still treated it like any other booking — courteous and on time. Had to request a refund due to a scheduling mix-up on our end and support handled it quickly.", 'featured' => false]],
            ['ref' => 'DEMO-10', 'customer' => $c2, 'driver' => $d3, 'vehicle' => 'LMS-1002', 'type' => 'hourly', 'pickup' => 'Madison Square Garden, Manhattan, NY', 'dropoff' => 'Madison Square Garden, Manhattan, NY', 'hours' => 3, 'when' => (clone $now)->subDays(2)->setTime(19, 0), 'status' => 'completed', 'payment' => 'paid',
                'review' => ['rating' => 5, 'comment' => "Booked the Escalade for a concert night out with friends. Communication before the ride was excellent — the driver called to confirm the exact pickup spot outside the venue.", 'featured' => false]],

            // --- In progress today (feeds "active rides" + today's stats) ---
            ['ref' => 'DEMO-11', 'customer' => $c3, 'driver' => $d2, 'vehicle' => 'LMS-1001', 'type' => 'one_way', 'pickup' => 'Astoria, Queens, NY', 'dropoff' => 'LaGuardia Airport, East Elmhurst, NY', 'distance' => 9.1, 'when' => (clone $now)->subMinutes(20), 'status' => 'in_progress', 'payment' => 'pending', 'in_progress' => true],

            // --- Assigned (driver confirmed, upcoming) ---
            ['ref' => 'DEMO-12', 'customer' => $c1, 'driver' => $d3, 'vehicle' => 'LMS-1003', 'type' => 'airport_transfer', 'pickup' => 'Garden City, Long Island, NY', 'dropoff' => 'John F. Kennedy International Airport, Queens, NY', 'distance' => 22.7, 'when' => (clone $now)->addDay()->setTime(5, 30), 'status' => 'assigned', 'payment' => 'pending'],
            ['ref' => 'DEMO-13', 'customer' => $c2, 'driver' => $d1, 'vehicle' => 'LMS-1008', 'type' => 'corporate', 'pickup' => 'Midtown Manhattan, NY', 'dropoff' => 'Wall Street, Manhattan, NY', 'distance' => 6.9, 'when' => (clone $now)->addDays(2)->setTime(8, 45), 'status' => 'assigned', 'payment' => 'pending'],

            // --- Confirmed, not yet assigned a driver ---
            ['ref' => 'DEMO-14', 'customer' => $c3, 'driver' => null, 'vehicle' => 'LMS-1004', 'type' => 'one_way', 'pickup' => 'Central Park, Manhattan, NY', 'dropoff' => 'Brooklyn Heights, Brooklyn, NY', 'distance' => 12.3, 'when' => (clone $now)->addDays(3)->setTime(16, 0), 'status' => 'confirmed', 'payment' => 'pending'],
            ['ref' => 'DEMO-15', 'customer' => $c1, 'driver' => null, 'vehicle' => 'LMS-1006', 'type' => 'round_trip', 'pickup' => 'Times Square, Manhattan, NY', 'dropoff' => 'Manhattan Cruise Terminal, Manhattan, NY', 'distance' => 7.5, 'when' => (clone $now)->addDays(4)->setTime(10, 0), 'status' => 'confirmed', 'payment' => 'pending'],

            // --- Pending (new, unactioned) ---
            ['ref' => 'DEMO-16', 'customer' => $c2, 'driver' => null, 'vehicle' => 'LMS-1005', 'type' => 'airport_transfer', 'pickup' => 'John F. Kennedy International Airport, Queens, NY', 'dropoff' => 'Astoria, Queens, NY', 'distance' => 13.6, 'when' => (clone $now)->addDays(5)->setTime(22, 0), 'status' => 'pending', 'payment' => 'pending'],
            ['ref' => 'DEMO-17', 'customer' => $c3, 'driver' => null, 'vehicle' => 'LMS-1009', 'type' => 'hourly', 'pickup' => 'Wall Street, Manhattan, NY', 'dropoff' => 'Wall Street, Manhattan, NY', 'hours' => 2, 'when' => (clone $now)->addDays(6)->setTime(14, 0), 'status' => 'pending', 'payment' => 'pending'],

            // --- Cancelled ---
            ['ref' => 'DEMO-18', 'customer' => $c1, 'driver' => $d2, 'vehicle' => 'LMS-1007', 'type' => 'one_way', 'pickup' => 'Times Square, Manhattan, NY', 'dropoff' => 'John F. Kennedy International Airport, Queens, NY', 'distance' => 25.2, 'when' => (clone $now)->subDay()->setTime(12, 0), 'status' => 'cancelled', 'payment' => 'refunded', 'cancellation_reason' => 'change_of_plans'],
        ];

        $calculator = app(BookingFareCalculator::class);

        foreach ($plan as $row) {
            $vehicleModel = $vehicle($row['vehicle']);
            $pickupDateTime = Carbon::instance($row['when']);
            $distanceKm = $row['distance'] ?? null;
            $hours = $row['hours'] ?? null;

            $breakdown = $calculator->breakdown($vehicleModel, $row['type'], $distanceKm, $hours, $pickupDateTime, 0, false);

            $data = [
                'customer_id' => $customer($row['customer'])->id,
                'driver_id' => $row['driver'] ? $driver($row['driver'])->id : null,
                'vehicle_id' => $vehicleModel->id,
                'type' => $row['type'],
                'pickup_location' => $row['pickup'],
                'dropoff_location' => $row['dropoff'],
                'stops' => [],
                'pickup_datetime' => $pickupDateTime,
                'return_datetime' => $row['type'] === 'round_trip' ? (clone $pickupDateTime)->addDays(5) : null,
                'hours' => $hours,
                'distance_km' => $distanceKm,
                'passengers' => min($vehicleModel->seats, fake()->numberBetween(1, $vehicleModel->seats)),
                'luggage' => fake()->numberBetween(0, $vehicleModel->luggage),
                'waiting_minutes' => 0,
                'has_toll' => false,
                'fare_amount' => $breakdown['total'],
                'fare_breakdown' => $breakdown,
                'status' => $row['status'],
                'payment_status' => $row['payment'],
                'cancellation_reason' => $row['cancellation_reason'] ?? null,
                'cancelled_by' => isset($row['cancellation_reason']) ? 'customer' : null,
            ];

            if ($row['payment'] === 'paid') {
                $data['paid_at'] = $pickupDateTime;
                $data['payment_gateway'] = 'stripe';
                $data['transaction_id'] = 'ch_demo_'.strtolower($row['ref']);
            } elseif ($row['payment'] === 'refunded') {
                $data['paid_at'] = $pickupDateTime;
                $data['payment_gateway'] = 'stripe';
                $data['transaction_id'] = 'ch_demo_'.strtolower($row['ref']);
                $data['refund_status'] = 'refunded';
            }

            if (! empty($row['in_progress'])) {
                $data['ride_started_at'] = $pickupDateTime;
                $data['estimated_arrival_at'] = (clone $pickupDateTime)->addMinutes(35);
            }

            // booking_number doubles as our idempotency key across re-runs.
            $existing = Booking::where('booking_number', $row['ref'])->first();

            if ($existing) {
                $existing->update($data);
                $booking = $existing;
            } else {
                $booking = Booking::create(['booking_number' => $row['ref']] + $data);
            }

            if ($row['status'] === 'completed' && isset($row['review'])) {
                Review::firstOrCreate(
                    ['customer_id' => $booking->customer_id, 'booking_id' => $booking->id],
                    [
                        'driver_id' => $booking->driver_id,
                        'vehicle_id' => $booking->vehicle_id,
                        'rating' => $row['review']['rating'],
                        'comment' => $row['review']['comment'],
                        'status' => 'approved',
                        'is_featured' => $row['review']['featured'],
                    ]
                );
            }
        }
    }

    /**
     * Realistic, modest homepage/about stats (500+ clients, the fleet's
     * real vehicle count, 24/7, 5+ years) in place of the seeder's
     * placeholder six-figure numbers — the brief specifically calls out
     * avoiding "obviously fake-looking numbers."
     */
    private function seedHomepageAndAboutContent(): void
    {
        $realisticStats = [
            ['icon' => 'heart', 'label' => 'Happy Clients', 'value' => 500, 'suffix' => '+'],
            ['icon' => 'car', 'label' => 'Premium Vehicles', 'value' => Vehicle::count(), 'suffix' => ''],
            ['icon' => 'clock', 'label' => 'Customer Support', 'value' => 24, 'suffix' => '/7'],
            ['icon' => 'trending-up', 'label' => 'Years of Experience', 'value' => 5, 'suffix' => '+'],
        ];

        PageSection::whereHas('page', fn ($q) => $q->whereIn('slug', ['home', 'about']))
            ->where('type', 'stats')
            ->each(fn (PageSection $section) => $section->update(['content' => $realisticStats]));

        // Homepage hero copy exactly as specified in the demo brief.
        PageSection::whereHas('page', fn ($q) => $q->where('slug', 'home'))
            ->where('type', 'hero')
            ->update([
                'heading' => 'Premium Chauffeur Service, Wherever You Need to Go',
                'subheading' => 'Reliable, professional and comfortable transportation for airport transfers, corporate travel, special events and everyday journeys.',
                'button_text' => 'Book Your Ride',
                'button_url' => '/#booking-widget',
                'button_text_2' => 'Get in Touch',
                'button_url_2' => '/contact',
            ]);
    }

    /**
     * Adds a 5th team member (chauffeur management wasn't yet represented)
     * and one more FAQ, and updates the one FAQ answer that referenced the
     * old single-vehicle fleet by name.
     */
    private function seedTeamAndFaq(): void
    {
        $team = PageSection::whereHas('page', fn ($q) => $q->where('slug', 'about'))
            ->where('type', 'team')->first();

        if ($team) {
            $members = $team->content ?? [];
            $hasChauffeurManager = collect($members)->contains(fn ($m) => str_contains($m['role'] ?? '', 'Chauffeur'));

            if (! $hasChauffeurManager) {
                $members[] = [
                    'name' => 'Thomas Reed',
                    'role' => 'Chauffeur Manager',
                    'bio' => 'Trains and oversees every chauffeur on our team, upholding the professionalism and discretion our clients expect.',
                ];
                $team->update(['content' => $members]);
            }
        }

        $faq = PageSection::whereHas('page', fn ($q) => $q->where('slug', 'faq'))
            ->where('type', 'faq')->first();

        if ($faq) {
            $items = collect($faq->content ?? [])->map(function ($item) {
                // Rewritten to reflect the full 9-vehicle demo fleet instead of a single named car.
                if (str_contains($item['answer'] ?? '', 'Toyota C-HR')) {
                    $item['answer'] = 'Our fleet ranges from executive sedans seating up to 4 passengers with 2 pieces of luggage, to full-size SUVs and vans seating up to 10 passengers with room for 8 pieces of luggage — perfect for solo travelers, couples, families, and larger groups.';
                }

                return $item;
            })->all();

            $hasVehicleRequestFaq = collect($items)->contains(fn ($i) => str_contains($i['question'] ?? '', 'specific vehicle'));

            if (! $hasVehicleRequestFaq) {
                $items[] = [
                    'category' => 'Booking',
                    'question' => 'Can I request a specific vehicle?',
                    'answer' => 'Yes — you can choose your preferred vehicle category (sedan, SUV, or van) when booking. If you have a specific vehicle in mind from our fleet page, mention it in the notes and we will do our best to accommodate it, subject to availability.',
                ];
            }

            $faq->update(['content' => array_values($items)]);
        }
    }

    /**
     * Rewrites the 3 existing blog posts in place (rather than adding 3
     * more alongside them) so the site ends up with exactly the 3 posts
     * the demo brief asks for. Keeps each post's existing featured image
     * and category/tag wiring — only title/slug/excerpt/body change.
     */
    private function seedBlogPosts(): void
    {
        $categories = \App\Models\BlogCategory::pluck('id', 'name');
        $tags = \App\Models\Tag::pluck('id', 'name');
        $author = \App\Models\Admin::first();

        $posts = [
            1 => [
                'title' => 'How to Choose the Right Luxury Car Service for Airport Transfers',
                'excerpt' => 'From vehicle selection to chauffeur professionalism, here is what actually separates a great airport car service from a disappointing one.',
                'category' => 'Travel Tips',
                'tags' => ['airport', 'travel tips'],
                'body' => <<<'HTML'
                    <p>Airport travel is stressful enough without wondering whether your ride will actually show up. Choosing the right luxury car service can turn the most unpredictable leg of your trip into the most relaxing one — but not every service delivers on that promise equally.</p>
                    <h2>Why Professional Airport Transportation Matters</h2>
                    <p>Your airport transfer sets the tone for the entire trip. A professional chauffeur service tracks your flight, adjusts for delays automatically, and has a driver waiting when you land — no circling the arrivals curb, no phone tag, no surprises.</p>
                    <h2>What to Look For in a Limo Service</h2>
                    <p>Look past the marketing photos and check for the fundamentals: transparent upfront pricing, real customer reviews, clear cancellation policies, and a fleet that is actually maintained and insured. A company that publishes its pricing and policies openly is usually one that stands behind its service.</p>
                    <h2>Vehicle Selection</h2>
                    <p>Match the vehicle to the trip. A luxury sedan is ideal for solo or two-passenger business travel, while a premium SUV or executive van makes more sense for families or groups with extra luggage. A good service will help you pick the right size rather than upselling the largest vehicle by default.</p>
                    <h2>Chauffeur Professionalism</h2>
                    <p>The vehicle is only half the experience. A trained chauffeur is licensed, background-checked, dressed appropriately, and knows the terminal layout well enough to meet you at the right door — not just "somewhere near arrivals."</p>
                    <h2>Punctuality</h2>
                    <p>On-time performance for an airport transfer isn't optional — it's the entire point. Ask how the company handles flight delays and early arrivals before you book, not after.</p>
                    <h2>Booking Tips</h2>
                    <p>Book at least a day or two in advance when you can, provide your flight number so the pickup time can be adjusted automatically, and confirm the exact pickup point (curbside, a specific terminal door, or a meet-and-greet inside baggage claim).</p>
                    <h2>Conclusion</h2>
                    <p>The right airport car service isn't the one with the flashiest fleet — it's the one that shows up on time, communicates clearly, and gets you where you're going without a second thought. Keep these fundamentals in mind and your next transfer will feel effortless.</p>
                    HTML,
            ],
            2 => [
                'title' => 'Corporate Transportation: Why Professional Chauffeur Services Matter',
                'excerpt' => 'Reliable executive transportation is more than a convenience — it protects your schedule, your image, and your team\'s time.',
                'category' => 'Industry Insights',
                'tags' => ['corporate', 'business travel'],
                'body' => <<<'HTML'
                    <p>For companies that move executives, clients, and teams around a city regularly, transportation isn't a minor logistics detail — it's part of how the business presents itself.</p>
                    <h2>Corporate Travel Is Different</h2>
                    <p>A missed pickup or a late arrival before a client meeting has consequences that a delayed rideshare app simply doesn't carry the same weight for. Corporate accounts need transportation that treats every trip like it matters, because it does.</p>
                    <h2>Airport Pickups</h2>
                    <p>Flying in executives or clients for a meeting means the first and last impression of the visit is the ride to and from the airport. A professional chauffeur waiting with a name sign, flight tracking built in, and a clean, quiet vehicle sets the right tone before a single word of business is discussed.</p>
                    <h2>Executive Meetings</h2>
                    <p>Between meetings across a city, a dedicated hourly chauffeur keeps a tight schedule realistic — no searching for parking, no navigating traffic between calls, and a private space to prepare or take calls between stops.</p>
                    <h2>Employee Transportation</h2>
                    <p>For team offsites, client dinners, or moving staff to and from events, reliable group transportation removes a logistical headache and keeps everyone arriving together, on time.</p>
                    <h2>Reliability</h2>
                    <p>The single biggest reason companies switch to a dedicated chauffeur service is consistency — the same standard of vehicle, professionalism, and punctuality every time, regardless of which driver is assigned.</p>
                    <h2>Professional Image</h2>
                    <p>How a company moves its people and clients reflects on the brand. A well-presented vehicle and a courteous, discreet chauffeur communicate the same standards a business holds itself to internally.</p>
                    <h2>Benefits of Pre-Booking</h2>
                    <p>Corporate accounts that pre-book recurring transportation — daily airport runs, weekly client visits, or regular executive schedules — get priority availability, consolidated billing, and one less thing to coordinate on a busy day.</p>
                    HTML,
            ],
            3 => [
                'title' => '5 Tips for a Smooth and Stress-Free Airport Transfer',
                'excerpt' => 'A few small habits make the difference between a relaxed airport transfer and a stressful scramble to the curb.',
                'category' => 'Travel Tips',
                'tags' => ['airport', 'travel tips'],
                'body' => <<<'HTML'
                    <p>Airport transfers are one of the easiest parts of a trip to get right — and one of the most stressful when they go wrong. These five habits keep things simple.</p>
                    <h2>1. Book in Advance</h2>
                    <p>Booking your transfer even a day or two ahead guarantees availability and gives the company time to match you with the right vehicle for your group size and luggage.</p>
                    <h2>2. Confirm Pickup Details</h2>
                    <p>Double-check the exact pickup point — terminal, curb zone, or meeting location — along with your flight number, so your chauffeur can track delays and adjust automatically.</p>
                    <h2>3. Allow Enough Travel Time</h2>
                    <p>Traffic and security lines are unpredictable. Build in extra time on both ends of the trip rather than cutting it close, especially during peak travel hours.</p>
                    <h2>4. Keep Communication Available</h2>
                    <p>Keep your phone charged and reachable in case your driver needs to confirm your location, especially at busy terminals with multiple pickup zones.</p>
                    <h2>5. Choose a Professional Transportation Provider</h2>
                    <p>A licensed, well-reviewed chauffeur service with transparent pricing and real customer support will make every other tip on this list unnecessary to worry about — they handle the details so you don't have to.</p>
                    HTML,
            ],
        ];

        foreach ($posts as $id => $data) {
            $post = \App\Models\BlogPost::find($id);

            if (! $post) {
                continue;
            }

            $post->update([
                'title' => $data['title'],
                'slug' => \Illuminate\Support\Str::slug($data['title']),
                'excerpt' => $data['excerpt'],
                'body' => $data['body'],
                'blog_category_id' => $categories[$data['category']] ?? $post->blog_category_id,
                'author_id' => $post->author_id ?? $author?->id,
                'status' => 'published',
                'meta_title' => $data['title'],
                'meta_description' => $data['excerpt'],
            ]);

            $tagIds = collect($data['tags'])->map(fn ($name) => $tags[$name] ?? null)->filter()->values();

            if ($tagIds->isNotEmpty()) {
                $post->tags()->sync($tagIds);
            }
        }
    }

    /**
     * Keeps the Contact page's own content block (a separate, pre-existing
     * hardcoded array — see PageSeeder) in sync with the Settings values
     * above, so both places agree.
     */
    private function syncContactPageContent(): void
    {
        PageSection::whereHas('page', fn ($q) => $q->where('slug', 'contact'))
            ->where('type', 'contact_info')
            ->update([
                'content' => [
                    'address' => 'New York, NY',
                    'phone' => '+1 (212) 555-0188',
                    'email' => 'info@limoschedule.com',
                    'hours' => 'Monday – Sunday, 24/7 Customer Support',
                    'whatsapp' => '+1 (212) 555-0188',
                ],
            ]);
    }

    /**
     * This install's page content had already been hand-customized by an
     * admin for a real, unrelated single-vehicle local taxi business
     * (city/airport names, a named vehicle, etc.) well before this demo
     * pass — so PageSeeder's own generic defaults were never applied (it
     * skips seeding sections once a page already has any). This restores
     * that same generic, well-written copy (lightly adapted) in place of
     * the business-specific text, keyed by exact page slug + section
     * type so it only ever touches the sections identified as carrying
     * that business's name/city.
     */
    private function rewritePriorBusinessCopy(): void
    {
        $updates = [
            ['page' => 'home', 'type' => 'fleet', 'data' => [
                'heading' => 'Our Fleet',
                'subheading' => 'From executive sedans to premium SUVs and vans — find the right vehicle for every occasion.',
            ]],
            ['page' => 'home', 'type' => 'items', 'data' => [
                'heading' => 'Why Choose Us',
                'content' => [
                    ['icon' => 'user', 'title' => 'Professional Chauffeurs', 'description' => 'Licensed, background-checked, and impeccably trained drivers.'],
                    ['icon' => 'clock', 'title' => 'On-Time Pickup', 'description' => 'Flight tracking and proactive scheduling mean we\'re there when you need us.'],
                    ['icon' => 'car', 'title' => 'Premium Vehicles', 'description' => 'A fleet of well-maintained sedans, SUVs, and vans for every occasion.'],
                    ['icon' => 'calendar', 'title' => 'Easy Online Booking', 'description' => 'Reserve your ride in minutes, right from our website.'],
                    ['icon' => 'chat', 'title' => '24/7 Customer Support', 'description' => 'Round-the-clock service, whenever you need us.'],
                    ['icon' => 'cash', 'title' => 'Transparent Pricing', 'description' => 'No hidden fees — know your fare before you ride.'],
                ],
            ]],
            ['page' => 'about', 'type' => 'rich_text', 'data' => [
                'body' => '<p>LimoSchedule was founded on a simple idea: getting to the airport, a meeting, or a special event shouldn\'t be stressful. What began as a small chauffeur operation has grown into a full-service luxury transportation company trusted by travelers, executives, and families across New York City.</p><p>Our mission is to deliver flawless, chauffeur-driven experiences — powered by a professional team, a meticulously maintained fleet, and a booking process that takes the guesswork out of getting where you need to go. Every driver is licensed and background-checked, every vehicle is inspected and cleaned between rides, and every booking is backed by real customer support, day or night.</p><p>We built LimoSchedule because we believe premium transportation should mean more than a nice car — it should mean punctuality you can set your watch to, a driver who treats every passenger with the same care, and pricing you can trust before you ever get in the vehicle.</p>',
            ]],
            ['page' => 'about', 'type' => 'vision_mission', 'data' => [
                'content' => [
                    'vision_icon' => 'eye',
                    'vision_title' => 'Our Vision',
                    'vision_body' => 'To be New York\'s most trusted name in luxury ground transportation — redefining what it means to arrive in style, every single time.',
                    'mission_icon' => 'trending-up',
                    'mission_title' => 'Our Mission',
                    'mission_body' => 'To deliver safe, comfortable, and reliable chauffeur-driven journeys, powered by professional drivers, a well-maintained fleet, and technology that makes booking effortless.',
                ],
            ]],
            ['page' => 'about', 'type' => 'items', 'data' => [
                'heading' => 'Our Values',
                'content' => [
                    ['icon' => 'shield', 'title' => 'Reliability', 'description' => 'On time, every time — book with confidence.'],
                    ['icon' => 'users', 'title' => 'Client First', 'description' => 'Every ride is tailored to you and treated with care.'],
                    ['icon' => 'globe', 'title' => 'Excellence', 'description' => 'Uncompromising standards across our fleet and team.'],
                ],
            ]],
            ['page' => 'services', 'type' => 'items', 'data' => [
                'content' => [
                    ['icon' => 'plane', 'title' => 'Airport Transfer', 'description' => 'Punctual pickups and drop-offs with complimentary flight tracking at JFK, LaGuardia, and Newark.', 'link' => '/airport-transfer'],
                    ['icon' => 'user', 'title' => 'Chauffeur Service', 'description' => 'A dedicated professional chauffeur for any occasion, anywhere in the city.', 'link' => '/chauffeur-service'],
                    ['icon' => 'calendar', 'title' => 'Corporate Transfer', 'description' => 'Reliable, professional transport for executives and teams.', 'link' => '/corporate-transfer'],
                    ['icon' => 'car', 'title' => 'City Rides', 'description' => 'On-demand luxury rides across town, day or night.', 'link' => '/city-rides'],
                    ['icon' => 'clock', 'title' => 'Hourly Rides', 'description' => 'A dedicated vehicle and driver, booked by the hour.', 'link' => '/hourly-rides'],
                    ['icon' => 'sparkles', 'title' => 'VIP Transport', 'description' => 'Our finest vehicles and chauffeurs for VIP occasions and special events.', 'link' => '/vip-transport'],
                ],
            ]],
            ['page' => 'airport-transfer', 'type' => 'rich_text', 'data' => [
                'body' => '<p>Never worry about a late flight again. Our airport transfer service includes real-time flight tracking, so your chauffeur automatically adjusts your pickup time for delays or early arrivals.</p><p>Your driver will be waiting with a name sign, ready to assist with luggage and get you on the road in comfort — whether you\'re flying through JFK, LaGuardia, or Newark.</p>',
            ]],
            ['page' => 'chauffeur-service', 'type' => 'hero', 'data' => [
                'subheading' => 'A dedicated professional chauffeur for any occasion, anywhere in the city.',
            ]],
            ['page' => 'chauffeur-service', 'type' => 'rich_text', 'data' => [
                'body' => '<p>Our professional chauffeurs are licensed, background-checked, and trained to the highest standard of hospitality and discretion.</p><p>Whether it\'s a single trip or a full-day booking, your chauffeur is dedicated to you — punctual, courteous, and always in complete control of the journey.</p>',
            ]],
            ['page' => 'corporate-transfer', 'type' => 'rich_text', 'data' => [
                'body' => '<p>Make the right impression with reliable, executive-grade transportation for meetings, conferences, and roadshows across the city.</p><p>We offer corporate accounts with consolidated billing, priority booking, and dedicated account management for teams that travel often.</p>',
            ]],
            ['page' => 'city-rides', 'type' => 'hero', 'data' => [
                'subheading' => 'On-demand luxury rides across town, day or night.',
            ]],
            ['page' => 'city-rides', 'type' => 'rich_text', 'data' => [
                'body' => '<p>Skip the ride-share lottery. Book a premium vehicle for errands, dinners, or nights out, and travel across the city in comfort and privacy.</p><p>Our fleet of well-maintained sedans is ready whenever you need to get around town.</p>',
            ]],
            ['page' => 'hourly-rides', 'type' => 'rich_text', 'data' => [
                'body' => '<p>Have several stops to make — shopping, meetings, or errands? Book by the hour and keep the same driver and vehicle for the whole trip.</p><p>No waiting between stops and no re-booking: your chauffeur stays with you for the entire duration.</p>',
            ]],
            ['page' => 'vip-transport', 'type' => 'rich_text', 'data' => [
                'body' => '<p>For clients who expect the very best, our VIP transport service pairs our most prestigious vehicles with our most experienced chauffeurs.</p><p>Ideal for red-carpet arrivals, weddings, and milestone celebrations where every detail matters.</p>',
            ]],
            ['page' => 'areas', 'type' => 'areas', 'data' => [
                'subheading' => 'Premium chauffeur service available across these boroughs, neighborhoods, and airports.',
            ]],
            ['page' => 'home', 'type' => 'areas', 'data' => [
                'subheading' => 'Premium chauffeur service available across these boroughs, neighborhoods, and airports.',
            ]],
        ];

        foreach ($updates as $update) {
            PageSection::whereHas('page', fn ($q) => $q->where('slug', $update['page']))
                ->where('type', $update['type'])
                ->update($update['data']);
        }

        // Every service sub-page's own "fleet" showcase subheading previously
        // named the prior business's single named vehicle.
        PageSection::whereIn('page_id', \App\Models\Page::whereIn('slug', \App\Models\Page::SERVICE_PAGES)->pluck('id'))
            ->where('type', 'fleet')
            ->update(['subheading' => 'A curated selection from our fleet, matched to this service.']);

        // Page-level SEO meta, previously named after the prior business.
        $pageMeta = [
            'home' => ['meta_title' => 'LimoSchedule | Premium Chauffeur & Limousine Service in New York', 'meta_description' => 'Book a professional chauffeur in minutes. Airport transfers, corporate travel, and luxury rides across New York City with transparent, upfront pricing.'],
            'about' => ['meta_title' => 'About LimoSchedule | Premium Chauffeur Service', 'meta_description' => 'LimoSchedule is a premium chauffeur and limousine service based in New York City, offering reliable, professional transportation across the region.'],
            'services' => ['meta_title' => 'Our Services | LimoSchedule', 'meta_description' => 'Airport transfers, chauffeur service, corporate transfers, and more from LimoSchedule in New York City.'],
            'faq' => ['meta_title' => 'Frequently Asked Questions | LimoSchedule', 'meta_description' => 'Answers to common questions about booking, pricing, and payments with LimoSchedule.'],
            'airport-transfer' => ['meta_title' => 'Airport Transfer | LimoSchedule', 'meta_description' => 'Fixed-price airport transfers to and from JFK, LaGuardia, and Newark Liberty International Airport.'],
            'chauffeur-service' => ['meta_title' => 'Chauffeur Service | LimoSchedule', 'meta_description' => 'A dedicated professional chauffeur for any occasion, anywhere in New York City.'],
            'corporate-transfer' => ['meta_title' => 'Corporate Transportation | LimoSchedule', 'meta_description' => 'Punctual, professional transport for meetings and business trips across New York City.'],
            'city-rides' => ['meta_title' => 'City Rides | LimoSchedule', 'meta_description' => 'On-demand luxury rides across New York City, any time of day.'],
            'hourly-rides' => ['meta_title' => 'Hourly Rides | LimoSchedule', 'meta_description' => 'Book a dedicated chauffeur and vehicle by the hour for multiple stops, meetings, or a full day of appointments.'],
            'vip-transport' => ['meta_title' => 'VIP Transport | LimoSchedule', 'meta_description' => 'Our finest vehicles and chauffeurs for VIP occasions and special events.'],
        ];

        foreach ($pageMeta as $slug => $meta) {
            \App\Models\Page::where('slug', $slug)->update($meta);
        }
    }

    /**
     * The Areas We Serve list previously covered a real, unrelated local
     * business's actual service region (a cluster of Belgian towns and
     * European airports). Renames the most prominent rows in place to
     * NYC boroughs/landmarks/airports for this demo, and deactivates
     * (not deletes) the remaining town-level rows that have no sensible
     * NYC equivalent, so they simply stop appearing in the public list.
     */
    private function rewriteAreas(): void
    {
        $renames = [
            'bilzen' => ['name' => 'Manhattan', 'description' => 'Premium chauffeur service throughout Manhattan — from the Financial District to Harlem.'],
            'diepenbeek' => ['name' => 'Brooklyn', 'description' => 'Reliable, comfortable rides across Brooklyn, day or night.'],
            'hoeselt' => ['name' => 'Queens', 'description' => 'Airport-ready chauffeur service across Queens, close to JFK and LaGuardia.'],
            'riemst' => ['name' => 'The Bronx', 'description' => 'Professional transportation across the Bronx.'],
            'lanaken' => ['name' => 'Staten Island', 'description' => 'Chauffeur service to and from Staten Island.'],
            'zutendaal' => ['name' => 'Long Island', 'description' => 'Airport transfers and point-to-point service across Long Island.'],
            'genk' => ['name' => 'Midtown Manhattan', 'description' => 'Fast, reliable pickups across Midtown Manhattan\'s hotels and offices.'],
            'hasselt' => ['name' => 'Times Square', 'description' => 'Convenient pickup and drop-off around Times Square and the Theater District.'],
            'tongeren' => ['name' => 'Wall Street', 'description' => 'Executive transportation for the Financial District and Wall Street.'],
            'maastricht' => ['name' => 'Central Park', 'description' => 'Chauffeur service to and from Central Park and the Upper East/West Sides.'],
            'brussels-zaventem-airport' => ['name' => 'John F. Kennedy International Airport', 'description' => 'Fixed-price chauffeur transfers to and from JFK, with complimentary flight tracking.'],
            'antwerp-airport' => ['name' => 'LaGuardia Airport', 'description' => 'Fixed-price chauffeur transfers to and from LaGuardia Airport.'],
            'charleroi-airport' => ['name' => 'Newark Liberty International Airport', 'description' => 'Fixed-price chauffeur transfers to and from Newark Liberty International Airport.'],
        ];

        foreach ($renames as $slug => $attrs) {
            $area = \App\Models\Area::where('slug', $slug)->first();

            if (! $area) {
                continue;
            }

            $area->update([
                'name' => $attrs['name'],
                'slug' => \Illuminate\Support\Str::slug($attrs['name']),
                'description' => $attrs['description'],
                'meta_title' => $attrs['name'].' Chauffeur Service | LimoSchedule',
                'meta_description' => $attrs['description'],
                'is_active' => true,
            ]);
        }

        \App\Models\Area::whereNotIn('slug', array_map(fn ($a) => \Illuminate\Support\Str::slug($a['name']), $renames))
            ->update(['is_active' => false]);
    }

    /**
     * The site's active/default currency was EUR (left over from the same
     * prior business) — switched to USD so every price shown across the
     * NYC-branded demo (fleet, fares, popular routes) reads consistently.
     */
    private function seedCurrency(): void
    {
        \App\Models\Currency::where('code', 'USD')->update(['is_active' => true, 'is_default' => true]);
        \App\Models\Currency::where('code', 'EUR')->update(['is_active' => false, 'is_default' => false]);
    }

    /**
     * The homepage's "Popular Routes" section was populated with the same
     * prior business's real (Belgian) routes — rewritten in place to NYC
     * routes at realistic USD fares. Reuses each row's existing
     * route_type_id/distance_unit rather than touching that structure.
     */
    private function seedPopularRoutes(): void
    {
        $routes = \App\Models\PopularRoute::orderBy('id')->get();

        $nyc = [
            ['pickup' => 'Manhattan', 'dropoff' => 'John F. Kennedy International Airport', 'price' => 85],
            ['pickup' => 'Manhattan', 'dropoff' => 'LaGuardia Airport', 'price' => 65],
            ['pickup' => 'Manhattan', 'dropoff' => 'Newark Liberty International Airport', 'price' => 95],
            ['pickup' => 'Midtown Manhattan', 'dropoff' => 'Brooklyn', 'price' => 55],
            ['pickup' => 'Manhattan', 'dropoff' => 'Queens', 'price' => 60],
            ['pickup' => 'Brooklyn', 'dropoff' => 'John F. Kennedy International Airport', 'price' => 75],
            ['pickup' => 'Queens', 'dropoff' => 'LaGuardia Airport', 'price' => 45],
            ['pickup' => 'Manhattan', 'dropoff' => 'Long Island', 'price' => 120],
            ['pickup' => 'Wall Street', 'dropoff' => 'John F. Kennedy International Airport', 'price' => 90],
            ['pickup' => 'Manhattan Cruise Terminal', 'dropoff' => 'Times Square', 'price' => 40],
            ['pickup' => 'Central Park', 'dropoff' => 'Newark Liberty International Airport', 'price' => 100],
            ['pickup' => 'Times Square', 'dropoff' => 'Brooklyn', 'price' => 50],
        ];

        foreach ($routes as $index => $route) {
            if (! isset($nyc[$index])) {
                $route->update(['is_active' => false]);

                continue;
            }

            $route->update([
                'pickup' => $nyc[$index]['pickup'],
                'dropoff' => $nyc[$index]['dropoff'],
                'estimated_price' => $nyc[$index]['price'],
                'original_price' => null,
                'is_active' => true,
            ]);
        }
    }
}
