<x-layouts::storefront :title="$title ?? null">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </div>
</x-layouts::storefront>
