<x-store.product-carousel
    :title="$section['title'] ?? 'Flash Sale'"
    :subtitle="$section['subtitle']"
    :products="$section['products']"
    :flash-sale="true"
    :view-all-url="$section['view_all_url'] ?? route('store.offers.index')"
    class="border-t border-gray-200/70 bg-surface"
/>
