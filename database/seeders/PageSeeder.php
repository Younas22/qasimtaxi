<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Page::PAGES as $slug => $name) {
            $page = Page::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'meta_title' => $slug === 'home' ? 'Premium Chauffeur & Limousine Service' : $name,
                    'meta_description' => $slug === 'home'
                        ? 'Book a professional chauffeur in minutes. Airport transfers, corporate travel, and luxury rides with transparent, upfront pricing.'
                        : "Learn more about {$name} at ".setting('company_name', config('app.name', 'Limo Schedule')).'.',
                    'is_active' => true,
                ]
            );

            if ($page->sections()->exists()) {
                continue;
            }

            foreach ($this->sectionsFor($slug) as $order => $section) {
                $page->sections()->create($section + ['sort_order' => $order, 'is_active' => true]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sectionsFor(string $slug): array
    {
        return match ($slug) {
            'home' => [
                [
                    'type' => 'hero',
                    'heading' => 'Arrive in Absolute Style',
                    'subheading' => 'Premium chauffeur-driven transportation for airport transfers, corporate travel, and special occasions.',
                    'button_text' => 'Book Now',
                    'button_url' => '/contact',
                    'button_text_2' => 'Explore Fleet',
                    'button_url_2' => '/services',
                ],
                [
                    'type' => 'promotions',
                    'heading' => 'Special Offers',
                    'subheading' => 'Limited-time deals on select routes and services.',
                ],
                [
                    'type' => 'items',
                    'heading' => 'Why Choose Us',
                    'subheading' => 'A fleet and service built around your comfort.',
                    'content' => [
                        ['icon' => 'user', 'title' => 'Professional Drivers', 'description' => 'Licensed, background-checked, and impeccably trained chauffeurs.'],
                        ['icon' => 'car', 'title' => 'Luxury Fleet', 'description' => 'Sedans, SUVs, and limousines maintained to the highest standard.'],
                        ['icon' => 'clock', 'title' => '24/7 Support', 'description' => 'Round-the-clock customer service, whenever you need us.'],
                        ['icon' => 'cash', 'title' => 'Fixed Pricing', 'description' => 'No hidden fees — know your fare before you ride.'],
                        ['icon' => 'lock', 'title' => 'Secure Payments', 'description' => 'Encrypted, PCI-compliant payment processing for total peace of mind.'],
                        ['icon' => 'plane', 'title' => 'Airport Transfers', 'description' => 'Flight tracking and meet-and-greet service for seamless arrivals.'],
                    ],
                ],
                [
                    'type' => 'stats',
                    'content' => [
                        ['icon' => 'trending-up', 'label' => 'Total Rides', 'value' => 50000, 'suffix' => '+'],
                        ['icon' => 'heart', 'label' => 'Happy Customers', 'value' => 25000, 'suffix' => '+'],
                        ['icon' => 'car', 'label' => 'Vehicles', 'value' => 120, 'suffix' => '+'],
                        ['icon' => 'globe', 'label' => 'Cities Covered', 'value' => 35, 'suffix' => '+'],
                    ],
                ],
                [
                    'type' => 'fleet',
                    'heading' => 'Our Fleet',
                    'subheading' => 'From executive sedans to stretch limousines — find the right vehicle for every occasion.',
                    'content' => ['limit' => 12],
                ],
                [
                    'type' => 'routes',
                    'heading' => 'Popular Routes',
                    'subheading' => 'Transparent, upfront pricing for the trips our clients book most.',
                    'content' => ['limit' => 3],
                ],
                [
                    'type' => 'areas',
                    'heading' => 'Areas We Serve',
                    'subheading' => 'Premium chauffeur service available across these towns and cities.',
                    'content' => [],
                ],
                [
                    'type' => 'testimonials',
                    'heading' => 'What Our Clients Say',
                    'subheading' => 'Real feedback from riders who booked with us.',
                    'content' => ['limit' => 6, 'min_rating' => 4],
                ],
                [
                    'type' => 'blog',
                    'heading' => 'From the Blog',
                    'subheading' => 'Travel tips, company news, and stories from the road.',
                    'content' => ['limit' => 6],
                ],
                [
                    'type' => 'cta',
                    'heading' => 'Ready to ride?',
                    'subheading' => 'Reserve your chauffeur in minutes.',
                    'button_text' => 'Get in Touch',
                    'button_url' => '/contact',
                ],
            ],
            'about' => [
                [
                    'type' => 'rich_text',
                    'heading' => 'Our Story',
                    'body' => '<p>For over a decade, we have delivered premium chauffeur services to discerning clients who expect nothing less than excellence. What began as a single-vehicle operation has grown into a full luxury fleet trusted by executives, celebrities, and travelers who refuse to compromise on comfort.</p><p>Our commitment to safety, punctuality, and impeccable service has made us a trusted name in luxury transportation across every city we serve.</p>',
                ],
                [
                    'type' => 'vision_mission',
                    'content' => [
                        'vision_icon' => 'eye',
                        'vision_title' => 'Our Vision',
                        'vision_body' => 'To be the world\'s most trusted name in luxury ground transportation — redefining what it means to arrive in style, every single time.',
                        'mission_icon' => 'trending-up',
                        'mission_title' => 'Our Mission',
                        'mission_body' => 'To deliver flawless, chauffeur-driven experiences powered by professional drivers, a meticulously maintained fleet, and technology that makes booking effortless.',
                    ],
                ],
                [
                    'type' => 'items',
                    'heading' => 'Our Values',
                    'content' => [
                        ['icon' => 'shield', 'title' => 'Reliability', 'description' => 'On time, every time.'],
                        ['icon' => 'users', 'title' => 'Client First', 'description' => 'Every ride is tailored to you.'],
                        ['icon' => 'globe', 'title' => 'Excellence', 'description' => 'Uncompromising standards across our fleet.'],
                    ],
                ],
                [
                    'type' => 'stats',
                    'heading' => 'By the Numbers',
                    'content' => [
                        ['icon' => 'trending-up', 'label' => 'Total Rides', 'value' => 50000, 'suffix' => '+'],
                        ['icon' => 'heart', 'label' => 'Happy Customers', 'value' => 25000, 'suffix' => '+'],
                        ['icon' => 'car', 'label' => 'Vehicles', 'value' => 120, 'suffix' => '+'],
                        ['icon' => 'globe', 'label' => 'Cities Covered', 'value' => 35, 'suffix' => '+'],
                    ],
                ],
                [
                    'type' => 'team',
                    'heading' => 'Meet Our Leadership',
                    'subheading' => 'The people behind every flawless ride.',
                    'content' => [
                        ['name' => 'Amanda Whitfield', 'role' => 'Chief Executive Officer', 'bio' => 'Two decades in luxury hospitality, driving our vision forward.'],
                        ['name' => 'Marcus Chen', 'role' => 'Head of Operations', 'bio' => 'Keeps every vehicle and driver running on time, every time.'],
                        ['name' => 'Sophia Rossi', 'role' => 'Fleet Director', 'bio' => 'Oversees fleet standards, maintenance, and acquisitions.'],
                        ['name' => 'David Okafor', 'role' => 'Customer Experience Lead', 'bio' => 'Ensures every client interaction meets our luxury standard.'],
                    ],
                ],
            ],
            'services' => [
                [
                    'type' => 'items',
                    'heading' => 'Our Services',
                    'subheading' => 'Tailored transportation for every occasion.',
                    'content' => [
                        ['icon' => 'plane', 'title' => 'Airport Transfer', 'description' => 'Punctual pickups and drop-offs with complimentary flight tracking.', 'link' => '/airport-transfer'],
                        ['icon' => 'user', 'title' => 'Chauffeur Service', 'description' => 'A dedicated professional chauffeur for any occasion.', 'link' => '/chauffeur-service'],
                        ['icon' => 'calendar', 'title' => 'Corporate Transfer', 'description' => 'Reliable, professional transport for executives and teams.', 'link' => '/corporate-transfer'],
                        ['icon' => 'car', 'title' => 'City Rides', 'description' => 'On-demand luxury rides across town, day or night.', 'link' => '/city-rides'],
                        ['icon' => 'clock', 'title' => 'Hourly Rides', 'description' => 'A dedicated vehicle and driver, booked by the hour.', 'link' => '/hourly-rides'],
                        ['icon' => 'sparkles', 'title' => 'VIP Transport', 'description' => 'Our finest vehicles and chauffeurs for VIP occasions.', 'link' => '/vip-transport'],
                    ],
                ],
            ],
            'airport-transfer' => $this->servicePageSections(
                heading: 'Airport Transfer',
                subheading: 'Punctual pickups and drop-offs with complimentary flight tracking.',
                body: '<p>Never worry about a late flight again. Our airport transfer service includes real-time flight tracking, so your chauffeur automatically adjusts your pickup time for delays or early arrivals.</p><p>Your driver will be waiting inside the terminal with a name sign, ready to assist with luggage and get you on the road in comfort.</p>',
                categorySlug: 'sedan',
            ),
            'chauffeur-service' => $this->servicePageSections(
                heading: 'Chauffeur Service',
                subheading: 'A dedicated professional chauffeur for any occasion.',
                body: '<p>Our professional chauffeurs are licensed, background-checked, and trained to the highest standard of hospitality and discretion.</p><p>Whether it\'s a single trip or a full-day booking, your chauffeur is dedicated to you — punctual, courteous, and always in complete control of the journey.</p>',
                categorySlug: 'luxury',
            ),
            'corporate-transfer' => $this->servicePageSections(
                heading: 'Corporate Transfer',
                subheading: 'Reliable, professional transport for executives and teams.',
                body: '<p>Make the right impression with reliable, executive-grade transportation for meetings, conferences, and roadshows.</p><p>We offer corporate accounts with consolidated billing, priority booking, and dedicated account management for teams that travel often.</p>',
                categorySlug: 'sedan',
            ),
            'city-rides' => $this->servicePageSections(
                heading: 'City Rides',
                subheading: 'On-demand luxury rides across town, day or night.',
                body: '<p>Skip the ride-share lottery. Book a premium vehicle for errands, dinners, or nights out, and travel across the city in comfort and privacy.</p><p>Our city fleet includes eco-friendly electric vehicles for clients who want a greener way to arrive in style.</p>',
                categorySlug: 'electric',
            ),
            'hourly-rides' => $this->servicePageSections(
                heading: 'Hourly Rides',
                subheading: 'A dedicated vehicle and driver, booked by the hour.',
                body: '<p>Keep a vehicle and chauffeur on standby for as long as you need — perfect for shopping trips, multi-stop errands, or a full day of meetings.</p><p>No waiting between stops and no re-booking: your driver stays with you for the entire duration.</p>',
                categorySlug: 'suv',
            ),
            'vip-transport' => $this->servicePageSections(
                heading: 'VIP Transport',
                subheading: 'Our finest vehicles and chauffeurs for VIP occasions.',
                body: '<p>For clients who expect the very best, our VIP transport service pairs our most prestigious vehicles with our most experienced chauffeurs.</p><p>Ideal for red-carpet arrivals, weddings, and milestone celebrations where every detail matters.</p>',
                categorySlug: 'limousine',
            ),
            'areas' => [
                [
                    'type' => 'areas',
                    'heading' => 'Areas We Serve',
                    'subheading' => 'Premium chauffeur service available across these towns and cities.',
                    'content' => [],
                ],
                [
                    'type' => 'cta',
                    'heading' => 'Don\'t see your area?',
                    'subheading' => 'Get in touch — we may still be able to accommodate your booking.',
                    'button_text' => 'Contact Us',
                    'button_url' => '/contact',
                ],
            ],
            'faq' => [
                [
                    'type' => 'faq',
                    'heading' => 'Frequently Asked Questions',
                    'subheading' => 'Everything you need to know before you ride.',
                    'content' => [
                        ['category' => 'Booking', 'question' => 'How far in advance should I book?', 'answer' => 'We recommend booking at least 24 hours in advance, though we do accommodate last-minute requests when availability allows.'],
                        ['category' => 'Booking', 'question' => 'Can I request a specific vehicle?', 'answer' => 'Absolutely — you can select your preferred vehicle category when booking, subject to availability.'],
                        ['category' => 'Airport Transfers', 'question' => 'Do you track flight delays?', 'answer' => 'Yes, all airport transfers include complimentary flight tracking so your chauffeur adjusts pickup time automatically.'],
                        ['category' => 'Payments', 'question' => 'What payment methods do you accept?', 'answer' => 'We accept all major credit and debit cards, along with digital wallets, through our secure encrypted payment gateway.'],
                        ['category' => 'Payments', 'question' => 'Is gratuity included in the fare?', 'answer' => 'Gratuity is not included by default, but you may add it at checkout or directly with your chauffeur.'],
                        ['category' => 'Cancellations', 'question' => 'What is your cancellation policy?', 'answer' => 'Cancellations made more than 24 hours before pickup are fully refundable. Please see our Terms page for full details.'],
                    ],
                ],
            ],
            'contact' => [
                [
                    'type' => 'contact_info',
                    'heading' => 'Get in Touch',
                    'subheading' => "We're here to help plan your next ride.",
                    'content' => [
                        'address' => '123 Luxury Ave, Suite 100, New York, NY 10001',
                        'phone' => '+1 (555) 123-4567',
                        'email' => 'bookings@limoschedule.test',
                        'hours' => 'Available 24/7',
                        'whatsapp' => '+1 (555) 123-4567',
                    ],
                ],
            ],
            'privacy-policy' => [
                [
                    'type' => 'rich_text',
                    'heading' => 'Privacy Policy',
                    'body' => '<p>We collect only the information necessary to provide our transportation services, including your name, contact details, and pickup/drop-off locations. We never sell your personal data to third parties.</p><p>Your payment information is processed securely through our payment providers and is never stored on our servers.</p>',
                ],
            ],
            'terms' => [
                [
                    'type' => 'rich_text',
                    'heading' => 'Terms & Conditions',
                    'body' => '<p>By booking a ride with us, you agree to arrive at the designated pickup point on time. Cancellations made less than 24 hours before pickup may be subject to a cancellation fee.</p><p>We reserve the right to refuse service to any passenger who violates our code of conduct.</p>',
                ],
            ],
            'refund-policy' => [
                [
                    'type' => 'rich_text',
                    'heading' => 'Refund Policy',
                    'body' => '<p>We want you to book with confidence. Cancellations made more than 24 hours before the scheduled pickup time are eligible for a full refund to your original payment method.</p><p>Cancellations made within 24 hours of pickup may be subject to a cancellation fee of up to 50% of the fare. No-shows and cancellations made after the chauffeur has arrived at the pickup location are non-refundable.</p><p>Approved refunds are processed within 5–10 business days, depending on your bank or card issuer. For refund requests related to service issues, please contact our support team with your booking reference number.</p>',
                ],
            ],
            'cookie-policy' => [
                [
                    'type' => 'rich_text',
                    'heading' => 'Cookie Policy',
                    'body' => '<p>Our website uses cookies and similar technologies to keep you signed in, remember your language and currency preferences, and understand how our site is used so we can improve it.</p><p><strong>Essential cookies</strong> are required for core functionality such as booking and checkout, and cannot be disabled.</p><p><strong>Preference cookies</strong> remember choices like your selected language, currency, and light/dark theme.</p><p><strong>Analytics cookies</strong> help us understand site traffic and usage patterns so we can improve our services.</p><p>You can control or delete cookies at any time through your browser settings. Disabling certain cookies may affect the functionality of our booking system.</p>',
                ],
            ],
            default => [],
        };
    }

    /**
     * Standard banner / description / fleet / CTA layout shared by every
     * dedicated service page (Airport Transfer, Chauffeur Service, etc.).
     *
     * @return array<int, array<string, mixed>>
     */
    private function servicePageSections(string $heading, string $subheading, string $body, string $categorySlug): array
    {
        $categoryId = \App\Models\VehicleCategory::where('slug', $categorySlug)->value('id');

        return [
            [
                'type' => 'hero',
                'heading' => $heading,
                'subheading' => $subheading,
                'button_text' => 'Book Now',
                'button_url' => '/contact',
            ],
            [
                'type' => 'rich_text',
                'body' => $body,
            ],
            [
                'type' => 'fleet',
                'heading' => 'The Right Vehicle for the Job',
                'subheading' => 'A curated selection from our fleet, matched to this service.',
                'content' => array_filter(['limit' => 6, 'category_id' => $categoryId]),
            ],
            [
                'type' => 'cta',
                'heading' => 'Ready to Book?',
                'subheading' => "Reserve your {$heading} in minutes.",
                'button_text' => 'Get in Touch',
                'button_url' => '/contact',
            ],
        ];
    }
}
