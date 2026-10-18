<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $driver = Auth::guard('driver')->user();

        $reviews = $driver->reviews()
            ->approved()
            ->with(['customer', 'booking'])
            ->paginate(10);

        return view('driver.reviews.index', [
            'reviews' => $reviews,
            'averageRating' => $driver->average_rating,
        ]);
    }
}
