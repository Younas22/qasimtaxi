<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function show(Area $area): View
    {
        abort_unless($area->is_active, 404);

        $nearbyAreas = Area::active()
            ->where('id', '!=', $area->id)
            ->ordered()
            ->limit(8)
            ->get();

        return view('pages.areas.show', compact('area', 'nearbyAreas'));
    }
}
