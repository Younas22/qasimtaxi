<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('driver.profile.edit', [
            'driver' => Auth::guard('driver')->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $driver = Auth::guard('driver')->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storeUpload($request->file('photo'), $driver->photo);
        }

        $driver->update($data);

        return back()->with('status', __('Profile updated successfully.'));
    }

    private function storeUpload($file, ?string $previousFilename): string
    {
        $directory = public_path('uploads/drivers');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'driver-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        if ($previousFilename && file_exists($directory.DIRECTORY_SEPARATOR.$previousFilename)) {
            @unlink($directory.DIRECTORY_SEPARATOR.$previousFilename);
        }

        return $filename;
    }
}
