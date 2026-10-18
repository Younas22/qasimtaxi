<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (BlogPost::query()->exists()) {
            return;
        }

        $author = Admin::first();
        $categories = BlogCategory::all()->keyBy('slug');

        $posts = [
            [
                'category' => 'travel-tips',
                'title' => '10 Tips for a Stress-Free Airport Transfer',
                'excerpt' => 'From flight tracking to curbside pickup, here is how to make your next airport transfer completely seamless.',
                'body' => '<h2>Plan Ahead</h2><p>Booking your chauffeur at least 24 hours in advance ensures the best vehicle availability and gives your driver time to review your itinerary.</p><h2>Share Your Flight Details</h2><p>Providing your flight number lets us track delays automatically, so your chauffeur is always there when you land — never too early, never too late.</p><ul><li>Confirm your pickup point in the app</li><li>Keep your phone charged for driver updates</li><li>Allow extra time during peak travel seasons</li></ul><h3>Curbside Comfort</h3><p>Our chauffeurs meet you at a designated curbside point with a name sign, so you can skip the confusion of rideshare pickup zones entirely.</p>',
                'tags' => ['airport', 'travel tips', 'chauffeur'],
                'is_featured' => true,
                'views_count' => 482,
                'days_ago' => 12,
            ],
            [
                'category' => 'company-news',
                'title' => 'Introducing Our Expanded Luxury SUV Fleet',
                'excerpt' => 'We are excited to announce five new premium SUVs joining our fleet this quarter.',
                'body' => '<h2>Meeting Growing Demand</h2><p>As more clients request spacious, comfortable vehicles for group travel, we have expanded our SUV lineup with the latest models featuring premium leather interiors and extended legroom.</p><p>Each new vehicle undergoes a rigorous 40-point inspection before entering service, ensuring the same reliability you expect from every ride with us.</p>',
                'tags' => ['fleet', 'announcement'],
                'is_featured' => true,
                'views_count' => 356,
                'days_ago' => 5,
            ],
            [
                'category' => 'luxury-lifestyle',
                'title' => 'The Art of Arriving: Why First Impressions Matter',
                'excerpt' => 'How you arrive says as much as how you present yourself. Here is why executives trust chauffeur service for high-stakes meetings.',
                'body' => '<h2>Confidence Before You Walk In</h2><p>Arriving calm, composed, and precisely on time sets the tone for any important meeting. A dedicated chauffeur removes the stress of navigation and parking, letting you focus entirely on your agenda.</p><h3>A Mobile Office</h3><p>Our vehicles are equipped with Wi-Fi and charging ports, turning your commute into productive time rather than dead time.</p>',
                'tags' => ['luxury', 'business travel'],
                'is_featured' => false,
                'views_count' => 214,
                'days_ago' => 20,
            ],
            [
                'category' => 'industry-insights',
                'title' => 'How Dynamic Pricing Is Reshaping Ground Transportation',
                'excerpt' => 'A look at how modern fare engines balance fairness and flexibility for both riders and operators.',
                'body' => '<h2>Beyond Flat Rates</h2><p>Traditional flat-rate pricing struggles to account for real-world variables like demand, distance, and time of day. Dynamic pricing engines factor in base fare, mileage, waiting time, and surcharges to produce fairer, more transparent quotes.</p><p>For riders, this means paying for exactly what they use — no more, no less.</p>',
                'tags' => ['industry', 'pricing'],
                'is_featured' => false,
                'views_count' => 128,
                'days_ago' => 30,
            ],
            [
                'category' => 'travel-tips',
                'title' => 'Corporate Travel Checklist: Booking Group Transportation',
                'excerpt' => 'Coordinating transportation for a team offsite? Here is a checklist to keep everyone on schedule.',
                'body' => '<h2>Start With Headcount</h2><p>Confirm passenger and luggage counts early so we can recommend the right vehicle mix — sedans for executives, SUVs or vans for larger groups.</p><h3>Centralize Communication</h3><p>Assign one point of contact to manage pickup times and location changes, keeping coordination simple for both your team and our dispatch.</p>',
                'tags' => ['corporate', 'travel tips'],
                'is_featured' => false,
                'views_count' => 97,
                'days_ago' => 8,
            ],
            [
                'category' => 'company-news',
                'title' => 'Our Commitment to Sustainable Luxury Travel',
                'excerpt' => 'We are adding hybrid and electric vehicles to our fleet as part of our sustainability roadmap.',
                'body' => '<h2>Reducing Our Footprint</h2><p>Luxury and sustainability are not mutually exclusive. Our newest hybrid additions offer the same premium comfort with a significantly reduced environmental impact.</p>',
                'tags' => ['sustainability', 'fleet', 'announcement'],
                'is_featured' => false,
                'views_count' => 63,
                'days_ago' => 2,
            ],
            [
                'category' => 'luxury-lifestyle',
                'title' => 'Wedding Day Transportation: A Complete Planning Guide',
                'excerpt' => 'From the bridal party to the getaway car, here is how to plan flawless wedding day logistics.',
                'body' => '<h2>Timing Is Everything</h2><p>Build a transportation timeline alongside your wedding day schedule, with buffer time for photos and unexpected delays.</p><h3>The Getaway</h3><p>A classic limousine or luxury sedan for the newlyweds\' departure creates a memorable final moment for guests.</p>',
                'tags' => ['weddings', 'luxury'],
                'is_featured' => true,
                'views_count' => 301,
                'days_ago' => 45,
            ],
            [
                'category' => 'industry-insights',
                'title' => 'The Future of Chauffeur Booking: What We\'re Building Next',
                'excerpt' => 'A behind-the-scenes look at upcoming features, still in draft as we finalize details.',
                'body' => '<h2>Work in Progress</h2><p>This post is still being drafted — check back soon for details on what is coming next to our booking platform.</p>',
                'tags' => ['product', 'roadmap'],
                'is_featured' => false,
                'views_count' => 0,
                'days_ago' => null,
                'status' => 'draft',
            ],
        ];

        foreach ($posts as $data) {
            $publishedAt = $data['days_ago'] !== null ? Carbon::now()->subDays($data['days_ago']) : null;
            $status = $data['status'] ?? 'published';

            $post = BlogPost::create([
                'blog_category_id' => $categories[$data['category']]->id,
                'author_id' => $author?->id,
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'body' => $data['body'],
                'status' => $status,
                'is_featured' => $data['is_featured'],
                'views_count' => $data['views_count'],
                'published_at' => $status === 'published' ? $publishedAt : null,
                'meta_title' => $data['title'],
                'meta_description' => $data['excerpt'],
            ]);

            $post->tags()->sync(Tag::resolveByNames($data['tags'])->pluck('id'));
        }
    }
}
