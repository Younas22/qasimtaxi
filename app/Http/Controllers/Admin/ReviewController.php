<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Review;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = Review::with(['customer', 'driver', 'vehicle'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->query('type') === 'driver', fn ($q) => $q->whereNotNull('driver_id'))
            ->when($request->query('type') === 'vehicle', fn ($q) => $q->whereNotNull('vehicle_id'))
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', $request->query('rating')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function create(): View
    {
        return view('admin.reviews.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateReview($request);

        Review::create($data);

        return redirect()
            ->route('admin.reviews.index')
            ->with('status', 'Review added successfully.');
    }

    public function edit(Review $review): View
    {
        return view('admin.reviews.edit', ['review' => $review] + $this->formData());
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $review->update($this->validateReview($request));

        return redirect()
            ->route('admin.reviews.index')
            ->with('status', 'Review updated successfully.');
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['status' => 'approved']);

        return back()->with('status', 'Review approved.');
    }

    public function reject(Review $review): RedirectResponse
    {
        $review->update(['status' => 'rejected']);

        return back()->with('status', 'Review rejected.');
    }

    /**
     * Pins/unpins a review to the front of the public testimonials slider
     * regardless of recency — see resources/views/pages/sections/testimonials.blade.php.
     */
    public function toggleFeatured(Review $review): RedirectResponse
    {
        $review->update(['is_featured' => ! $review->is_featured]);

        return back()->with('status', $review->is_featured ? 'Review featured.' : 'Review unfeatured.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Review deleted.');
    }

    /**
     * @return array{customers: \Illuminate\Support\Collection, drivers: \Illuminate\Support\Collection, vehicles: \Illuminate\Support\Collection}
     */
    private function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'email']),
            'drivers' => Driver::orderBy('name')->get(['id', 'name']),
            'vehicles' => Vehicle::orderBy('name')->get(['id', 'name']),
        ];
    }

    private function validateReview(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys(Review::STATUSES))],
        ]);
    }
}
