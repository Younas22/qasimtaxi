@php $redirect = $redirect ?? null; @endphp

<div class="space-y-5 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
    <div>
        <x-admin.input-label for="old_path" :value="__('Old Path')" />
        <div class="flex items-center gap-2">
            <span class="text-sm text-luxury-muted">{{ url('/') }}/</span>
            <div class="flex-1">
                <x-admin.text-input id="old_path" name="old_path" type="text" value="{{ old('old_path', $redirect?->old_path) }}" placeholder="old-page" required autofocus />
            </div>
        </div>
        <p class="mt-1.5 text-xs text-luxury-muted">{{ __('The URL that no longer exists — without the leading slash or domain.') }}</p>
        <x-admin.input-error :messages="$errors->get('old_path')" />
    </div>

    <div>
        <x-admin.input-label for="new_path" :value="__('New Path')" />
        <div class="flex items-center gap-2">
            <span class="text-sm text-luxury-muted">{{ url('/') }}/</span>
            <div class="flex-1">
                <x-admin.text-input id="new_path" name="new_path" type="text" value="{{ old('new_path', $redirect?->new_path) }}" placeholder="new-page" required />
            </div>
        </div>
        <p class="mt-1.5 text-xs text-luxury-muted">{{ __('Where visitors should end up instead.') }}</p>
        <x-admin.input-error :messages="$errors->get('new_path')" />
    </div>

    <div>
        <x-admin.input-label for="type" :value="__('Redirect Type')" />
        <select id="type" name="type" required
            class="w-full rounded-lg border border-luxury-border bg-luxury-charcoal px-4 py-3 text-sm text-luxury-white focus:border-luxury-gold focus:outline-none focus:ring-1 focus:ring-luxury-gold transition">
            @foreach (\App\Models\Redirect::TYPES as $value => $label)
                <option value="{{ $value }}" @selected((int) old('type', $redirect?->type ?? 301) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="mt-1.5 text-xs text-luxury-muted">{{ __('301 tells search engines the move is permanent and to transfer the old page\'s ranking — use this for almost every case.') }}</p>
        <x-admin.input-error :messages="$errors->get('type')" />
    </div>

    @if ($redirect)
        <x-admin.toggle name="is_active" :checked="old('is_active', $redirect->is_active)" label="{{ __('Redirect is active') }}" />
    @endif
</div>
