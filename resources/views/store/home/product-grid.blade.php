<x-store.product-grid
    :title="$section['title'] ?? 'All Products'"
    :subtitle="$section['subtitle']"
    :products="$section['products']"
    :initial-count="$section['initial_count']"
    :step="$section['initial_count']"
    class="border-t border-gray-200/70 bg-surface"
/>
