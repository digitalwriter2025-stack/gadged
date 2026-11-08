{{-- Included by filter-sidebar: $price, $filters, $summaryClass --}}
<details class="group/filter" open>
    <summary class="{{ $summaryClass }}">
        Price Range
        <x-store.icon name="chevron-right" class="size-4 text-gray-600 transition duration-200 group-open/filter:rotate-90" />
    </summary>

    <div class="space-y-2 pb-4">
        <div class="flex items-center gap-2">
            <label class="flex h-10 min-w-0 flex-1 items-center gap-1.5 rounded-md border border-gray-300 px-3 text-sm transition focus-within:border-builder focus-within:ring-2 focus-within:ring-builder/15">
                <span class="text-gray-500">৳</span>
                <span class="sr-only">Minimum price</span>
                <input type="number" name="min" inputmode="numeric" min="0" max="{{ $price['max'] }}" value="{{ $filters['min'] ?? $price['min'] }}" data-default="{{ $price['min'] }}" data-filter-price
                    class="w-full min-w-0 [appearance:textfield] bg-transparent text-gray-900 focus:outline-none [&::-webkit-inner-spin-button]:appearance-none">
            </label>
            <span class="text-xs text-gray-500">to</span>
        </div>
        <label class="flex h-10 items-center gap-1.5 rounded-md border border-gray-300 px-3 text-sm transition focus-within:border-builder focus-within:ring-2 focus-within:ring-builder/15">
            <span class="text-gray-500">৳</span>
            <span class="sr-only">Maximum price</span>
            <input type="number" name="max" inputmode="numeric" min="0" max="{{ $price['max'] }}" value="{{ $filters['max'] ?? $price['max'] }}" data-default="{{ $price['max'] }}" data-filter-price
                class="w-full min-w-0 [appearance:textfield] bg-transparent text-gray-900 focus:outline-none [&::-webkit-inner-spin-button]:appearance-none">
        </label>
    </div>
</details>
