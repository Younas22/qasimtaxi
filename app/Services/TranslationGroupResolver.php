<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Splits the (currently ~1,500-string) flat translation key list into
 * per-page/per-module tabs, so translating doesn't mean scrolling one giant
 * list to find, say, just the checkout strings.
 *
 * There is no stored "which page does this key belong to" metadata —
 * translation keys are simply the literal English source text, reused
 * as-is wherever __() is called. So this resolves membership by actually
 * searching the source tree: for each known key, which Blade views (and,
 * for strings that come from a PHP constant/label rather than a literal in
 * a Blade file — e.g. Booking::STATUSES — which model/controller files)
 * contain that exact string, then maps each hit's file path to a tab.
 *
 * Admin/Customer/Driver panel files get a further, dynamic split — e.g.
 * "Admin: Bookings", "Admin: Vehicles", "Customer: Wallet" — one tab per
 * `resources/views/{role}/{module}/` folder, since "Admin Panel" alone
 * would otherwise still swallow roughly two-thirds of every string in the
 * app into one unusably long tab.
 *
 * A key can textually appear in files belonging to several tabs (e.g. a
 * generic "Delete" button used in both the admin and customer panels) —
 * rather than showing the same row in multiple tabs, it's placed under the
 * highest-priority one it matches, per PRIORITY_GROUPS' order, so panel-
 * specific areas win over generic/shared ones.
 *
 * Computed once and cached forever (same pattern as the Setting/
 * BookingSetting singletons elsewhere in this app) since scanning ~480
 * files × ~1,500 keys on every page load would be wasteful (it takes a
 * couple of seconds) — call refresh() (wired to the "Rescan" button in the
 * translation editor) after adding new pages/strings so newly-added keys
 * get picked up.
 */
class TranslationGroupResolver
{
    public const CACHE_KEY = 'translations.key_groups';

    public const OTHER_GROUP = 'Other / Uncategorized';

    /**
     * Checked before the dynamic per-module admin/customer/driver split
     * below, so a login/register page under e.g. resources/views/customer/
     * auth/ lands in this shared bucket rather than being split out as its
     * own "Customer: Auth" tab.
     */
    private const AUTH_PATTERN = '/auth/';

    /**
     * The three panels that get dynamically split into one tab per
     * `resources/views/{prefix}/{module}/` folder — see moduleGroup().
     *
     * @var array<string, string>
     */
    private const DYNAMIC_PANELS = [
        'resources/views/admin/' => 'Admin',
        'resources/views/customer/' => 'Customer',
        'resources/views/driver/' => 'Driver',
    ];

    /**
     * View-folder segments that describe layout/plumbing rather than a
     * distinct page — folded into "Header, Footer & Navigation" instead of
     * becoming their own (rather thin) "Admin: Partials" style tab.
     */
    private const LAYOUT_SEGMENTS = ['partials', 'layouts'];

    /**
     * Ordered [group label => patterns] list for everything other than the
     * three dynamically-split panels — a file's group is the label of the
     * FIRST rule (checked top to bottom) whose pattern is found anywhere in
     * that file's path. Patterns are plain lowercase substrings (not
     * regex), matched against the file's path relative to the project root
     * with backslashes normalized to slashes.
     *
     * @var array<string, array<int, string>>
     */
    private const STATIC_GROUP_RULES = [
        'Emails & Notifications' => ['resources/views/emails/', 'app/notifications/'],
        'Error Pages' => ['resources/views/errors/'],
        'Booking Widget & Checkout' => [
            'booking-search-box', 'resources/views/booking/',
            'bookingrequestcontroller', 'bookingpaymentcontroller', 'bookingcreationservice',
        ],
        'Homepage & Site Pages' => ['resources/views/pages/'],
        'Blog' => ['resources/views/blog/', 'blogcontroller.php', 'blogpost.php'],
        'Header, Footer & Navigation' => [
            'components/header.blade.php', 'components/footer.blade.php', 'components/sidebar',
            'components/bottom-nav.blade.php',
        ],
        'Shared Components' => ['resources/views/components/'],
        'System Labels (Statuses, Types, etc.)' => ['app/models/', 'app/http/controllers/', 'app/services/'],
    ];

    public function groupedKeys(): Collection
    {
        $byGroup = Cache::rememberForever(self::CACHE_KEY, fn () => $this->scan());

        // Sort so the dynamic per-module panel tabs cluster together
        // (Admin: ... together, then Customer: ..., then Driver: ...) and
        // "Other" always sits last, rather than in raw discovery order.
        return collect($byGroup)
            ->sortBy(fn ($keys, $label) => $this->sortWeight($label))
            ->map(fn ($keys) => collect($keys)->sort()->values())
            ->filter(fn (Collection $keys) => $keys->isNotEmpty());
    }

    public function refresh(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function scan(): array
    {
        $keys = Translation::knownKeys()->all();

        if ($keys === []) {
            return [];
        }

        // Read every candidate file's contents and resolve its group ONCE
        // up front — the alternative (re-opening files from disk once per
        // key) would mean hundreds of thousands of disk reads instead of a
        // few hundred, for the exact same result.
        $fileEntries = [];

        foreach ([...File::allFiles(resource_path('views')), ...File::allFiles(app_path())] as $file) {
            $relativePath = str_replace('\\', '/', str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname()));

            $group = $this->groupForPath($relativePath);

            if ($group === null) {
                continue;
            }

            // Weight resolved once per FILE here, not once per (file, key)
            // pair inside bestGroupForKey()'s hot loop below — with ~485
            // files x ~1,500 keys, recomputing it per pair turned a
            // sub-second scan into one that took ~20x longer for no
            // benefit, since a file's group (and therefore its weight)
            // never changes across keys.
            $fileEntries[] = [
                'contents' => $file->getContents(),
                'group' => $group,
                'weight' => $this->sortWeight($group),
            ];
        }

        $result = [];

        foreach ($keys as $key) {
            $result[$this->bestGroupForKey($key, $fileEntries)][] = $key;
        }

        return $result;
    }

    private function groupForPath(string $relativePath): ?string
    {
        $lower = strtolower($relativePath);

        if (str_contains($lower, self::AUTH_PATTERN)) {
            return 'Login, Register & Password Reset';
        }

        foreach (self::DYNAMIC_PANELS as $prefix => $roleLabel) {
            if (str_starts_with($lower, $prefix)) {
                return $this->moduleGroup($roleLabel, $lower, $prefix);
            }
        }

        // A controller under app/Http/Controllers/{Admin,Customer,Driver}/
        // belongs to that same dynamically-split panel, not the generic
        // "System Labels" bucket further down — checked before the static
        // rules below since one of those (System Labels) would otherwise
        // swallow every controller, admin ones included.
        $controllerPanels = [
            'app/http/controllers/admin/' => 'Admin',
            'app/http/controllers/customer/' => 'Customer',
            'app/http/controllers/driver/' => 'Driver',
        ];

        foreach ($controllerPanels as $prefix => $roleLabel) {
            if (str_starts_with($lower, $prefix)) {
                return "{$roleLabel}: General";
            }
        }

        foreach (self::STATIC_GROUP_RULES as $label => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($lower, $pattern)) {
                    return $label;
                }
            }
        }

        if (str_starts_with($lower, 'app/models/') || str_starts_with($lower, 'app/http/controllers/') || str_starts_with($lower, 'app/services/')) {
            return 'System Labels (Statuses, Types, etc.)';
        }

        return null;
    }

    /**
     * "resources/views/admin/popular-routes/index.blade.php" -> "Admin: Popular Routes"
     * "resources/views/admin/dashboard.blade.php" (no subfolder) -> "Admin: Dashboard"
     * "resources/views/admin/partials/topbar.blade.php" -> "Header, Footer & Navigation"
     */
    private function moduleGroup(string $roleLabel, string $lowerPath, string $prefix): string
    {
        $remainder = substr($lowerPath, strlen($prefix));
        $segment = str_contains($remainder, '/')
            ? strstr($remainder, '/', true)
            : preg_replace('/\.(blade\.php|php)$/', '', $remainder);

        if (in_array($segment, self::LAYOUT_SEGMENTS, true)) {
            return 'Header, Footer & Navigation';
        }

        $label = ucwords(str_replace(['-', '_'], ' ', $segment));

        return "{$roleLabel}: {$label}";
    }

    /**
     * @param  array<int, array{contents: string, group: string, weight: int}>  $fileEntries
     */
    private function bestGroupForKey(string $key, array $fileEntries): string
    {
        // A blank/whitespace-only key can't meaningfully be searched for
        // (it would "match" every file's contents trivially) — park it in
        // Other rather than mis-grouping it.
        if (trim($key) === '') {
            return self::OTHER_GROUP;
        }

        $best = null;
        $bestWeight = PHP_INT_MAX;

        foreach ($fileEntries as $entry) {
            // Cheap check first (str_contains) — only a genuine match ever
            // needs the (already-precomputed, but still a comparison)
            // weight looked at, and most (key, file) pairs don't match.
            if ($entry['weight'] < $bestWeight && str_contains($entry['contents'], $key)) {
                $best = $entry['group'];
                $bestWeight = $entry['weight'];

                if ($bestWeight === 0) {
                    break; // can't beat the top priority tier
                }
            }
        }

        return $best ?? self::OTHER_GROUP;
    }

    /**
     * Lower sorts first. Auth, then the three panels' modules (alphabetical
     * within each), then the static groups in their declared order, then
     * Other last — used both to pick the winning group for a key that
     * matches several, and to order the tabs themselves.
     */
    private function sortWeight(string $label): int
    {
        if ($label === 'Login, Register & Password Reset') {
            return 0;
        }

        foreach (array_values(self::DYNAMIC_PANELS) as $i => $roleLabel) {
            if (str_starts_with($label, "{$roleLabel}: ")) {
                return 10 + $i;
            }
        }

        $staticIndex = array_search($label, array_keys(self::STATIC_GROUP_RULES), true);

        if ($staticIndex !== false) {
            return 20 + $staticIndex;
        }

        if ($label === self::OTHER_GROUP) {
            return PHP_INT_MAX;
        }

        return 100; // any other static label added later, before Other
    }
}
