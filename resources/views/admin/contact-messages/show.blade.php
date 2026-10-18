<x-admin.layouts.app :title="__('Contact Message')">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-luxury-white">{{ $message->subject ?: __('Contact Message') }}</h2>
            <p class="mt-1 text-sm text-luxury-muted">{{ __('Received :date', ['date' => $message->created_at->format('M d, Y h:i A')]) }}</p>
        </div>
        <a href="{{ route('admin.contact-messages.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">&larr; {{ __('Back to messages') }}</a>
    </div>

    <div class="space-y-6 rounded-2xl border border-luxury-border bg-luxury-charcoal p-6">
        <div class="grid grid-cols-1 gap-4 border-b border-luxury-border pb-6 sm:grid-cols-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Name') }}</p>
                <p class="mt-1 text-sm font-medium text-luxury-white">{{ $message->name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Email') }}</p>
                <a href="mailto:{{ $message->email }}" class="mt-1 block text-sm font-medium text-luxury-gold hover:underline">{{ $message->email }}</a>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Phone') }}</p>
                @if ($message->phone)
                    <a href="tel:{{ $message->phone }}" class="mt-1 block text-sm font-medium text-luxury-gold hover:underline">{{ $message->phone }}</a>
                @else
                    <p class="mt-1 text-sm text-luxury-muted">—</p>
                @endif
            </div>
        </div>

        <div>
            <p class="text-xs uppercase tracking-wide text-luxury-muted">{{ __('Message') }}</p>
            <p class="mt-2 whitespace-pre-line text-sm text-luxury-white">{{ $message->message }}</p>
        </div>

        <div class="flex items-center gap-3 border-t border-luxury-border pt-6">
            <a href="mailto:{{ $message->email }}?subject={{ urlencode(__('Re: :subject', ['subject' => $message->subject ?: __('Your inquiry')])) }}">
                <x-admin.button type="button" variant="primary">{{ __('Reply by Email') }}</x-admin.button>
            </a>
            @permission('messages.delete')
                <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" onsubmit="return confirm(@js(__('Delete this message?')));">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-500/30 px-4 py-2.5 text-sm font-medium text-red-400 transition hover:bg-red-500/10">
                        {{ __('Delete') }}
                    </button>
                </form>
            @endpermission
        </div>
    </div>
</x-admin.layouts.app>
