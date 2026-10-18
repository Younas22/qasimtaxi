<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $vehicles = Auth::guard('customer')->user()->favoriteVehicles()->with('category')->paginate(12);

        return view('customer.favorites.index', compact('vehicles'));
    }

    public function toggle(Vehicle $vehicle): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        if ($customer->hasFavorited($vehicle)) {
            $customer->favoriteVehicles()->detach($vehicle);
            $status = __('Removed from favorites.');
        } else {
            $customer->favoriteVehicles()->syncWithoutDetaching($vehicle);
            $status = __('Added to favorites.');
        }

        return back()->with('status', $status);
    }
}
