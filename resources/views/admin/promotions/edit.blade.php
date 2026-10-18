<x-admin.layouts.app :title="'Edit Promotion'">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-luxury-white">Edit Promotion</h2>
        <p class="mt-1 text-sm text-luxury-muted">{{ $promotion->title }}</p>
    </div>

    <form method="POST" action="{{ route('admin.promotions.update', $promotion) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.promotions._form')

        <div class="flex items-center gap-3">
            <x-admin.button type="submit" variant="primary">Update Promotion</x-admin.button>
            <a href="{{ route('admin.promotions.index') }}" class="text-sm text-luxury-muted hover:text-luxury-white">Cancel</a>
        </div>
    </form>
</x-admin.layouts.app>
