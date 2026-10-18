<x-admin.layouts.app :title="$page->name">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ $page->name }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">/{{ $page->slug === 'home' ? '' : $page->slug }}</p>
        </div>
        <a href="{{ $page->slug === 'home' ? route('pages.home') : route('pages.show', $page->slug) }}" target="_blank" rel="noopener" class="text-sm text-luxury-gold hover:text-luxury-gold-light">
            {{ __('View Live') }} &rarr;
        </a>
    </div>

    <div class="space-y-6">
        {{-- Page Settings --}}
        <form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data" class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            @csrf
            @method('PUT')

            <h3 class="text-sm font-semibold text-luxury-white">{{ __('Page Settings') }}</h3>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <x-admin.input-label for="name" value="{{ __('Page Name') }}" />
                    <x-admin.text-input id="name" name="name" type="text" value="{{ old('name', $page->name) }}" required />
                    <x-admin.input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-admin.input-label for="meta_title" value="{{ __('Meta Title') }}" />
                    <x-admin.text-input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $page->meta_title) }}" />
                    <x-admin.input-error :messages="$errors->get('meta_title')" />
                </div>

                <div class="sm:col-span-2">
                    <x-admin.input-label for="meta_description" value="{{ __('Meta Description') }}" />
                    <textarea id="meta_description" name="meta_description" rows="2"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">{{ old('meta_description', $page->meta_description) }}</textarea>
                    <x-admin.input-error :messages="$errors->get('meta_description')" />
                </div>

                <div>
                    <x-admin.input-label for="canonical_override" value="{{ __('Canonical URL (optional)') }}" />
                    <x-admin.text-input id="canonical_override" name="canonical_override" type="url" value="{{ old('canonical_override', $page->canonical_override) }}" placeholder="{{ url()->current() }}" />
                    <p class="mt-1 text-xs text-luxury-muted">{{ __('Leave blank to use this page\'s own URL — only set this if this content is a duplicate of another page.') }}</p>
                    <x-admin.input-error :messages="$errors->get('canonical_override')" />
                </div>

                <div x-data="{ preview: '{{ $page->og_image_url }}' }">
                    <x-admin.input-label value="{{ __('Social Share Image (optional)') }}" />
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-luxury-border bg-luxury-graphite">
                            <template x-if="preview"><img :src="preview" alt="" class="h-full w-full object-cover"></template>
                            <template x-if="!preview"><span class="text-[10px] text-luxury-muted">{{ __('No image') }}</span></template>
                        </div>
                        <label class="flex-1 cursor-pointer rounded-lg border border-dashed border-luxury-border px-4 py-3 text-center text-xs text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                            <span>{{ __('Click to upload image') }}</span>
                            <input type="file" name="og_image" accept="image/*" class="hidden" @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : preview">
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-luxury-muted">{{ __('Falls back to the site-wide default when left blank.') }}</p>
                    <x-admin.input-error :messages="$errors->get('og_image')" />
                </div>

                <div class="sm:col-span-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <x-admin.toggle name="robots_index" :checked="old('robots_index', $page->robots_index)"
                        label="{{ __('Allow search engines to index this page') }}" />
                    <x-admin.toggle name="robots_follow" :checked="old('robots_follow', $page->robots_follow)"
                        label="{{ __('Allow search engines to follow links on this page') }}" />
                </div>

                <div class="sm:col-span-2">
                    <x-admin.input-label for="custom_schema" value="{{ __('Schema Markup (optional)') }}" />
                    <textarea id="custom_schema" name="custom_schema" rows="6" spellcheck="false"
                        class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 font-mono text-xs text-luxury-white placeholder:text-luxury-muted focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition"
                        placeholder="&lt;script type=&quot;application/ld+json&quot;&gt;{ ... }&lt;/script&gt;">{{ old('custom_schema', $page->custom_schema) }}</textarea>
                    <p class="mt-1 text-xs text-luxury-muted">{{ __("Paste the full <script> tag with your JSON-LD/structured data — it's added to this page's <head> exactly as pasted.") }}</p>
                    <x-admin.input-error :messages="$errors->get('custom_schema')" />
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-luxury-muted">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $page->is_active))
                    class="h-4 w-4 rounded border-luxury-border bg-luxury-charcoal text-luxury-gold focus:ring-1 focus:ring-luxury-gold">
                {{ __('Page is published') }}
            </label>

            <div class="flex justify-end">
                <x-admin.button type="submit" variant="primary">{{ __('Save Page Settings') }}</x-admin.button>
            </div>
        </form>

        {{-- Sections --}}
        <div class="rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-luxury-white">{{ __('Sections') }}</h3>
                @permission('content.create')
                    <a href="{{ route('admin.pages.sections.create', $page) }}">
                        <x-admin.button type="button" variant="primary">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            {{ __('Add Section') }}
                        </x-admin.button>
                    </a>
                @endpermission
            </div>

            <div class="space-y-3" id="sections-list" x-data="pageSectionsSortable({{ \Illuminate\Support\Js::from(route('admin.pages.sections.reorder', $page)) }})">
                @forelse ($page->sections as $section)
                    <div class="flex flex-col gap-3 rounded-xl border border-luxury-border/60 bg-luxury-graphite/40 p-4 sm:flex-row sm:items-center sm:justify-between" data-section-id="{{ $section->id }}">
                        <div class="flex items-center gap-3">
                            @permission('content.edit')
                                <button type="button" class="drag-handle hidden shrink-0 cursor-grab text-luxury-muted hover:text-luxury-gold active:cursor-grabbing sm:block" title="{{ __('Drag to reorder') }}">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 6a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zm0 6a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zm0 6a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zm6-12a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zm0 6a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zm0 6a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z" /></svg>
                                </button>
                                <div class="flex flex-col">
                                    <form method="POST" action="{{ route('admin.pages.sections.move-up', [$page, $section]) }}">
                                        @csrf
                                        <button type="submit" @if ($loop->first) disabled @endif class="flex h-5 w-5 items-center justify-center text-luxury-muted hover:text-luxury-gold disabled:opacity-20">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.pages.sections.move-down', [$page, $section]) }}">
                                        @csrf
                                        <button type="submit" @if ($loop->last) disabled @endif class="flex h-5 w-5 items-center justify-center text-luxury-muted hover:text-luxury-gold disabled:opacity-20">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                    </form>
                                </div>
                            @endpermission

                            {{-- Background thumbnail — hero/CTA are the only types with a
                                 background image, so this doubles as a visual cue for where
                                 "Edit" actually changes the background photo. --}}
                            @if (in_array($section->type, ['hero', 'cta'], true))
                                <div class="h-12 w-20 shrink-0 overflow-hidden rounded-lg border border-luxury-border bg-luxury-slate">
                                    @if ($section->image_url)
                                        <img src="{{ $section->image_url }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-[10px] text-luxury-muted">
                                            {{ __('No image') }}
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-luxury-white">{{ $section->heading ?: $section->type_label }}</p>
                                    <span class="rounded-full bg-luxury-slate px-2 py-0.5 text-[10px] uppercase tracking-wide text-luxury-muted">{{ $section->type_label }}</span>
                                </div>
                                <p class="mt-0.5 text-xs text-luxury-muted">{{ $section->subheading ?: ($section->body ? \Illuminate\Support\Str::limit(strip_tags($section->body), 80) : '') }}</p>
                                @if (in_array($section->type, ['hero', 'cta'], true))
                                    <p class="mt-0.5 text-[11px] text-luxury-muted">
                                        {{ $section->image_url ? __('Background set — click Edit to change it.') : __('No background image set — click Edit to add one.') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-admin.status-badge :active="$section->is_active" />
                            @permission('content.edit')
                                <form method="POST" action="{{ route('admin.pages.sections.toggle', [$page, $section]) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                        {{ $section->is_active ? __('Disable') : __('Enable') }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.pages.sections.edit', [$page, $section]) }}" class="rounded-lg border border-luxury-border px-3 py-1.5 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
                                    {{ __('Edit') }}
                                </a>
                            @endpermission
                            @permission('content.delete')
                                <form method="POST" action="{{ route('admin.pages.sections.destroy', [$page, $section]) }}" onsubmit="return confirm('{{ __('Remove this section?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            @endpermission
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-luxury-muted">{{ __('No sections yet — add one to start building this page.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
        <script>
            function pageSectionsSortable(reorderUrl) {
                return {
                    init() {
                        Sortable.create(this.$el, {
                            handle: '.drag-handle',
                            animation: 150,
                            onEnd: () => {
                                const sectionIds = Array.from(this.$el.children)
                                    .map((row) => row.dataset.sectionId)
                                    .filter(Boolean);

                                axios.post(reorderUrl, { section_ids: sectionIds }).catch(() => {
                                    window.dispatchEvent(new CustomEvent('notify', {
                                        detail: { type: 'error', message: @js(__('Could not save the new order — please try again.')) },
                                    }));
                                });
                            },
                        });
                    },
                };
            }
        </script>
    @endpush
</x-admin.layouts.app>
