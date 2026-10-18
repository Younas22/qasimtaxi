<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        $promotions = Promotion::orderBy('sort_order')->latest()->paginate(15);

        return view('admin.promotions.index', compact('promotions'));
    }

    public function create(): View
    {
        return view('admin.promotions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePromotion($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'));
        }

        Promotion::create($data);

        return redirect()->route('admin.promotions.index')->with('status', 'Promotion created successfully.');
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $data = $this->validatePromotion($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'), $promotion->image);
        }

        $promotion->update($data);

        return redirect()->route('admin.promotions.index')->with('status', 'Promotion updated successfully.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        if ($promotion->image) {
            $path = public_path('uploads/promotions/'.$promotion->image);

            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $promotion->delete();

        return back()->with('status', 'Promotion deleted successfully.');
    }

    private function validatePromotion(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'badge_text' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'max:2048'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        unset($data['image']);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function storeUpload($file, ?string $previousFilename = null): string
    {
        $directory = public_path('uploads/promotions');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'promotion-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        if ($previousFilename && file_exists($directory.DIRECTORY_SEPARATOR.$previousFilename)) {
            @unlink($directory.DIRECTORY_SEPARATOR.$previousFilename);
        }

        return $filename;
    }
}
