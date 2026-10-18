<x-admin.layouts.app :title="'Add Promotion'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Add Promotion</h2>
        <p class="mt-1 text-sm text-luxury-muted">Create a new promo banner shown on the customer dashboard.</p>
    </div>

    <form method="POST" action="{{ route('admin.promotions.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.promotions._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Create Promotion</x-admin.button>
            <a href="{{ route('admin.promotions.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
