<footer class="border-t border-luxury-border px-4 py-4 sm:px-6 lg:px-8">
    <div class="flex flex-col items-center justify-between gap-2 text-xs text-luxury-muted sm:flex-row">
        <p>&copy; {{ now()->year }} {{ setting('company_name', config('app.name', 'Limo Schedule')) }}. {{ __('All rights reserved.') }}</p>
        <p>{{ __('Crafted for the road ahead.') }}</p>
    </div>
</footer>
