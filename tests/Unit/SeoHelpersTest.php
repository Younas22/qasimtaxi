<?php

namespace Tests\Unit;

use App\Models\Page;
use Tests\TestCase;

class SeoHelpersTest extends TestCase
{
    public function test_root_relative_links_resolve_to_absolute_urls(): void
    {
        $html = resolve_internal_links('<a href="/contact">Contact</a> <a href="/#booking-widget">Boek</a>');

        $this->assertStringContainsString('href="'.url('/contact').'"', $html);
        $this->assertStringContainsString('href="'.url('/#booking-widget').'"', $html);
    }

    public function test_external_protocol_relative_and_tel_links_are_left_alone(): void
    {
        $html = '<a href="https://example.com/x">a</a> <a href="//cdn.example.com/y">b</a> <a href="tel:+32467614860">c</a> <a href="#faq">d</a>';

        $this->assertSame($html, resolve_internal_links($html));
    }

    public function test_airport_landing_page_route_slug_is_registered(): void
    {
        $this->assertArrayHasKey('taxi-maastricht-brussels-airport', Page::PAGES);
        $this->assertContains('taxi-maastricht-brussels-airport', Page::LANDING_PAGES);
    }

    public function test_landing_page_uses_service_layout_but_stays_out_of_menus(): void
    {
        $this->assertTrue(Page::isServiceLike('taxi-maastricht-brussels-airport'));
        $this->assertTrue(Page::isExcludedFromNav('taxi-maastricht-brussels-airport'));
        $this->assertNotContains('taxi-maastricht-brussels-airport', Page::SERVICE_PAGES);
    }

    public function test_existing_navigation_rules_are_unchanged(): void
    {
        foreach (Page::SERVICE_PAGES as $slug) {
            $this->assertTrue(Page::isServiceLike($slug));
            $this->assertTrue(Page::isExcludedFromNav($slug));
        }

        foreach (Page::LEGAL_PAGES as $slug) {
            $this->assertFalse(Page::isServiceLike($slug));
            $this->assertTrue(Page::isExcludedFromNav($slug));
        }

        foreach (['home', 'about', 'services', 'areas', 'faq', 'contact'] as $slug) {
            $this->assertFalse(Page::isExcludedFromNav($slug));
        }
    }
}
