<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return $this->render('home');
    }

    public function show(string $slug): View
    {
        return $this->render($slug);
    }

    private function render(string $slug): View
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $page->load('activeSections');

        return view('pages.show', [
            'page' => $page,
            'sections' => $page->activeSections,
            'navPages' => Page::where('is_active', true)->get()->keyBy('slug'),
        ]);
    }
}
