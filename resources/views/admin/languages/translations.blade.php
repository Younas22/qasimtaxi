<x-admin.layouts.app :title="__('Translate — :name', ['name' => $language->name])">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Translation Editor') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">
                {{ __('Editing') }} <span class="text-luxury-white">{{ $language->name }}</span> ({{ $language->native_name }}) &mdash;
                {{ __('changes apply across the panel immediately after saving.') }}
            </p>
        </div>
        <a href="{{ route('admin.languages.index') }}" class="flex items-center gap-1.5 text-sm text-luxury-muted hover:text-luxury-white">
            <svg class="h-4 w-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            {{ __('Back to Languages') }}
        </a>
    </div>

    <div x-data="translationEditor(
            {{ \Illuminate\Support\Js::from(route('admin.languages.translations.destroy', $language)) }},
            {{ \Illuminate\Support\Js::from($groupedKeys->keys()->first()) }},
            {{ \Illuminate\Support\Js::from($groupedKeys->map->values()) }}
        )"
        class="space-y-6">

        {{-- The real save form: deliberately empty here — every field that
             belongs to it lives physically inside the "Existing Strings" /
             "Add New String" cards below instead, associated via
             form="translationForm" rather than DOM nesting. That's because
             the "Rescan Pages" mini-form sits, visually, between those two
             cards' header and their fields — and a <form> can't legally
             close and later "resume" around it, nor can two <form>s nest.
             The form="id" attribute is the standard way to associate an
             input with a form it isn't a descendant of. --}}
        <form id="translationForm" method="POST" action="{{ route('admin.languages.translations.update', $language) }}">
            @csrf
            @method('PUT')
        </form>

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Existing Strings') }}</h3>
                <div class="flex flex-wrap items-center gap-2">
                    <select x-model="category" @change="onCategoryChange()"
                        class="rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <option value="Admin Panel">{{ __('Admin Panel') }}</option>
                        <option value="Customer Panel">{{ __('Customer Panel') }}</option>
                        <option value="Driver Panel">{{ __('Driver Panel') }}</option>
                        <option value="Website">{{ __('Website') }}</option>
                    </select>
                    <input type="search" x-model="search" placeholder="{{ __('Search in this tab...') }}"
                        class="w-full max-w-xs rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                    <form method="POST" action="{{ route('admin.languages.translations.rescan', $language) }}">
                        @csrf
                        <button type="submit" title="{{ __('Re-scan the site for new/changed strings and refresh these tabs.') }}"
                            class="whitespace-nowrap rounded-lg border border-luxury-border px-3 py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            {{ __('Rescan Pages') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Tabs — one per page/area a string was actually found in (see
                 TranslationGroupResolver); "Rescan Pages" above refreshes
                 this if a page was added/changed since it was last computed.
                 Wraps onto as many lines as needed (no horizontal scrolling)
                 and is filtered down to the selected Category above, so a
                 panel with dozens of modules doesn't turn into one long
                 unreadable scroll strip. --}}
            <div class="-mx-1 mb-4 flex flex-wrap gap-1.5 px-1 pb-2">
                @foreach ($groupedKeys as $group => $groupKeys)
                    <button type="button" x-show="categoryOf({{ \Illuminate\Support\Js::from($group) }}) === category"
                        @click="activeTab = {{ \Illuminate\Support\Js::from($group) }}; search = ''"
                        :class="activeTab === {{ \Illuminate\Support\Js::from($group) }} ? 'bg-luxury-gold text-luxury-black border-luxury-gold' : 'border-luxury-border text-luxury-muted hover:border-luxury-gold/40 hover:text-luxury-white'"
                        class="shrink-0 cursor-pointer whitespace-nowrap rounded-lg border px-3.5 py-2 text-xs font-semibold transition">
                        {{ $group }}
                        <span class="ms-1 opacity-70">{{ $groupKeys->count() }}</span>
                    </button>
                @endforeach
            </div>

            <div class="scrollbar-luxury max-h-[32rem] space-y-3 overflow-y-auto pe-1">
                @forelse ($groupedKeys as $group => $groupKeys)
                    @foreach ($groupKeys as $key)
                        <div x-show="activeTab === {{ \Illuminate\Support\Js::from($group) }} && matches({{ \Illuminate\Support\Js::from($key) }})"
                            class="grid grid-cols-1 gap-2 rounded-lg border border-luxury-border/60 p-3 sm:grid-cols-[1fr_1fr_auto] sm:gap-4">
                            {{-- min-w-0 on both text columns: a bare 1fr grid track's
                                 implicit minimum size is its content's min-content width,
                                 not 0 — without this, one long source string with no
                                 natural break point (a long key, or a URL) forces its own
                                 1fr track to blow out far past the container, and every
                                 other row sharing this list's parent stretches to match
                                 since block children are 100% of their parent's width —
                                 silently pushing every field in the tab off-screen. --}}
                            <div class="min-w-0">
                                <p class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ __('Source (English)') }}</p>
                                <p class="mt-1 break-words text-sm text-luxury-white">{{ $key }}</p>
                            </div>
                            <div class="min-w-0">
                                <label class="text-[11px] uppercase tracking-wide text-luxury-muted">{{ $language->name }}</label>
                                <textarea form="translationForm" name="translations[{{ $key }}]" rows="2"
                                    class="mt-1 w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition"
                                    dir="{{ $language->direction }}">{{ old("translations.$key", $values[$key] ?? '') }}</textarea>
                            </div>
                            <div class="flex items-start sm:items-end sm:pb-0.5">
                                <button type="button" @click="deleteKey({{ \Illuminate\Support\Js::from($key) }}, $el)"
                                    class="w-full rounded-lg border border-red-500/30 px-3 py-2 text-xs font-medium text-red-400 transition hover:bg-red-500/10 sm:w-auto">
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <p class="py-6 text-center text-sm text-luxury-muted">{{ __('No translation strings yet. Add one below.') }}</p>
                @endforelse

                <template x-if="visibleCountInActiveTab() === 0">
                    <p class="py-6 text-center text-sm text-luxury-muted">{{ __('No strings match your search in this tab.') }}</p>
                </template>
            </div>
        </div>

        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Add New String') }}</h3>
                <button type="button" @click="addRow()" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                    {{ __('+ Add Row') }}
                </button>
            </div>

            <template x-if="rows.length === 0">
                <p class="text-sm text-luxury-muted">{{ __('No new strings queued. Click "Add Row" to create one.') }}</p>
            </template>

            <div class="space-y-3">
                <template x-for="(row, index) in rows" :key="row.id">
                    <div class="grid grid-cols-1 gap-2 rounded-lg border border-luxury-border/60 p-3 sm:grid-cols-[1fr_1fr_auto] sm:gap-3">
                        <input type="text" form="translationForm" :name="'new_translations['+index+'][key]'" x-model="row.key" placeholder="{{ __('Source key (English text)') }}"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <input type="text" form="translationForm" :name="'new_translations['+index+'][value]'" x-model="row.value" placeholder="{{ __('Translation') }}"
                            dir="{{ $language->direction }}"
                            class="w-full rounded-lg border border-luxury-border bg-luxury-graphite px-3 py-2 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold">
                        <button type="button" @click="removeRow(index)" class="rounded-lg border border-red-500/30 px-3 py-2 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                            {{ __('Remove') }}
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" form="translationForm" variant="primary">{{ __('Save Translations') }}</x-admin.button>
        </div>
    </div>

    <script>
        function translationEditor(destroyUrl, firstGroup, groups) {
            return {
                search: '',
                activeTab: firstGroup ?? '',
                groups: groups ?? {},
                category: 'Website',
                rows: [],
                nextId: 1,
                init() {
                    // Start on whichever Category the pre-selected first tab
                    // actually belongs to, so the page doesn't open with its
                    // own active tab hidden behind a different category.
                    this.category = this.categoryOf(this.activeTab);
                },
                // Mirrors TranslationGroupResolver's own dynamic-panel split
                // (Admin:/Customer:/Driver: prefixes) — everything else
                // (Homepage & Site Pages, Blog, Header/Footer/Nav, Login,
                // Emails, ...) is lumped into "Website" for this filter.
                categoryOf(group) {
                    if (group.startsWith('Admin:')) return 'Admin Panel';
                    if (group.startsWith('Customer:')) return 'Customer Panel';
                    if (group.startsWith('Driver:')) return 'Driver Panel';

                    return 'Website';
                },
                onCategoryChange() {
                    this.search = '';

                    if (this.categoryOf(this.activeTab) === this.category) {
                        return;
                    }

                    this.activeTab = Object.keys(this.groups).find(group => this.categoryOf(group) === this.category) ?? this.activeTab;
                },
                addRow() {
                    this.rows.push({ id: this.nextId++, key: '', value: '' });
                },
                removeRow(index) {
                    this.rows.splice(index, 1);
                },
                matches(key) {
                    return this.search === '' || key.toLowerCase().includes(this.search.toLowerCase());
                },
                visibleCountInActiveTab() {
                    return (this.groups[this.activeTab] ?? []).filter(key => this.matches(key)).length;
                },
                deleteKey(key, buttonEl) {
                    if (! confirm('{{ __('Delete this string? This removes the translation for this language only — it will fall back to the English text.') }}')) {
                        return;
                    }

                    axios.delete(destroyUrl, { data: { key } })
                        .then(() => {
                            buttonEl.closest('[x-show]').remove();
                        })
                        .catch(() => {
                            alert('{{ __('Could not delete — please try again.') }}');
                        });
                },
            };
        }
    </script>
</x-admin.layouts.app>
