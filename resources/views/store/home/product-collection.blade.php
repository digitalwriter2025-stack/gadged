<x-store.product-carousel
    :title="$section['title'] ?? ''"
    :subtitle="$section['subtitle']"
    :products="$section['products']"
    :view-all-url="$section['view_all_url'] ?? route('store.search')"
    class="border-t border-gray-200/70 bg-surface"
/>
