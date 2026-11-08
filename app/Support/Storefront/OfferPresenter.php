<?php

namespace App\Support\Storefront;

use App\Models\Offer;

/**
 * Shapes offers for the Offers / Mobile Deal listing cards and the offer detail page.
 */
class OfferPresenter
{
    /**
     * Gradient pairs for offers that have no poster uploaded yet.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const FALLBACK_GRADIENTS = [
        ['#1e3a8a', '#7c3aed'], ['#b91c1c', '#f97316'], ['#0f766e', '#22d3ee'],
        ['#312e81', '#db2777'], ['#14532d', '#84cc16'], ['#0c4a6e', '#2563eb'],
    ];

    /**
     * Product photos used as the card image for offers without a poster, keyed by offer slug.
     *
     * @var array<string, string>
     */
    private const FALLBACK_ILLUSTRATIONS = [
        'mobile-deal' => 'images/products/samsung-mobile-galaxy-s26-ultra.webp',
        'triple-desktop-offer' => 'images/products/amd-gaming-pc-amd-ryzen-7-7700.jpg',
        'smartwatch-earbuds-deal' => 'images/products/apple-smart-watch-series-11.webp',
        'monitor-mega-deal' => 'images/products/lg-monitor-34u640b-b.jpg',
        'air-conditioner-deal' => 'images/products/haier-air-conditioner-hsu-19gravity-inv-pro-x6.jpg',
        'tv-deal' => 'images/products/samsung-television-55u8510.webp',
        'camera-deal' => 'images/products/canon-camera-eos.jpg',
    ];

    /**
     * @return array{id: int, title: string, summary: ?string, url: string, image: ?string, image_large: ?string, image_fit: string, gradient: string, availability: string, period: string, ends_at_iso: string}
     */
    public function present(Offer $offer): array
    {
        $poster = $offer->getFirstMedia('poster');
        $illustration = isset(self::FALLBACK_ILLUSTRATIONS[$offer->slug]) ? asset(self::FALLBACK_ILLUSTRATIONS[$offer->slug]) : null;
        [$from, $to] = self::FALLBACK_GRADIENTS[$offer->id % count(self::FALLBACK_GRADIENTS)];

        return [
            'id' => $offer->id,
            'title' => $offer->title,
            'summary' => $offer->summary,
            'url' => route('store.offers.show', $offer->slug),
            'image' => $poster?->getAvailableUrl(['card']) ?? $illustration,
            'image_large' => $poster?->getAvailableUrl(['large', 'card']) ?? $illustration,
            'image_fit' => $poster !== null ? 'object-cover' : 'object-contain p-4',
            'gradient' => "linear-gradient(135deg, {$from}, {$to})",
            'availability' => $offer->availability,
            'period' => $offer->starts_at->format('d M Y').' - '.$offer->ends_at->format('d M Y'),
            'ends_at_iso' => $offer->ends_at->toIso8601String(),
        ];
    }
}
