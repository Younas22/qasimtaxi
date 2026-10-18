<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Handles inline image uploads for the shared rich-text editor component
 * (blog posts, page "rich_text" sections) — separate from any single
 * feature's controller since the editor is reused across them.
 */
class RichTextUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $directory = public_path('uploads/content');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $request->file('image');
        $filename = 'content-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return response()->json([
            'url' => asset('uploads/content/'.$filename),
        ]);
    }
}
