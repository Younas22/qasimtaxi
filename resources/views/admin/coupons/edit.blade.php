<x-admin.layouts.app :title="__('Edit Coupon')">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">{{ __('Edit Coupon') }}</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ $coupon->code }} &middot; {{ __('Used :count time(s).', ['count' => $coupon->used_count]) }}</p>
    </div>

    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.coupons._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">{{ __('Update Coupon') }}</x-admin.button>
            <a href="{{ route('admin.coupons.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin.layouts.app>
