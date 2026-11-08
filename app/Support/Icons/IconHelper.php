<?php

declare(strict_types=1);

namespace App\Support\Icons;

class IconHelper
{
    /**
     * Common stroke icons (lucide-style 24x24, stroke-based).
     *
     * @var array<string, string>
     */
    public const STROKE_ICONS = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'gift' => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7M7.5 8a2.5 2.5 0 0 1 0-5C11 3 12 8 12 8s1-5 4.5-5a2.5 2.5 0 0 1 0 5"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-up' => '<path d="m18 15-6-6-6 6"/>',
        'tag' => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8Z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'filter' => '<path d="M3 5h18M6 12h12M10 19h4"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-left' => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
        'arrow-up' => '<path d="M12 19V5M6 11l6-6 6 6"/>',
        'arrow-down' => '<path d="M12 5v14M18 13l-6 6-6-6"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'map-pin' => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'chat' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
        'home' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'cart' => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.6 12.4a1 1 0 0 0 1 .8h9.7a1 1 0 0 0 1-.8L21 7H6"/>',
        'cpu' => '<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v4M15 2v4M9 18v4M15 18v4M2 9h4M2 15h4M18 9h4M18 15h4"/>',
        'heart' => '<path d="M12 20s-7.5-4.6-9.3-9.2A5 5 0 0 1 12 6a5 5 0 0 1 9.3 4.8C19.5 15.4 12 20 12 20Z"/>',
        'compare' => '<path d="M7 4 3 8l4 4M3 8h13M17 20l4-4-4-4M21 16H8"/>',
        'minus' => '<path d="M5 12h14"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'check' => '<path d="m5 12 5 5 9-10"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        'store' => '<path d="M3 9l1.5-5h15L21 9M3 9v11h18V9M3 9h18M9 20v-6h6v6"/>',
        'bank' => '<path d="m3 9 9-5 9 5M5 10v7M9.5 10v7M14.5 10v7M19 10v7M3 20h18"/>',
        'fan' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="1.5"/><path d="M12 10.5c0-3 1-5 3-5.5M13.5 12c3 0 5 1 5.5 3M12 13.5c0 3-1 5-3 5.5M10.5 12c-3 0-5-1-5.5-3"/>',
        'board' => '<rect x="3" y="3" width="18" height="18" rx="2"/><rect x="7" y="7" width="6" height="6" rx="1"/><path d="M16 7v4M16 14h-9M16 17H7"/>',
        'memory' => '<path d="M2 8h20v8H2zM6 8v8M10 8v8M14 8v8M18 8v8M5 16v3M19 16v3"/>',
        'drive' => '<rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="11" r="4"/><path d="m12 11 3 7M8 19h.01"/>',
        'gpu' => '<path d="M2 6h20v11H2zM5 17v3M2 6V4"/><circle cx="15" cy="11.5" r="3"/><path d="M5 9h4M5 12h4"/>',
        'plug' => '<path d="M9 2v5M15 2v5M6 7h12v4a6 6 0 0 1-12 0V7ZM12 17v5"/>',
        'tower' => '<rect x="6" y="2" width="12" height="20" rx="2"/><path d="M10 6h4M10 9h4"/><circle cx="12" cy="17" r="1"/>',
        'monitor' => '<rect x="2" y="3" width="20" height="13" rx="2"/><path d="M8 21h8M12 16v5"/>',
        'keyboard' => '<rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M7 14h10"/>',
        'mouse' => '<rect x="6" y="3" width="12" height="18" rx="6"/><path d="M12 7v4"/>',
        'speaker' => '<rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="14" r="4"/><path d="M12 6h.01"/>',
        'headphone' => '<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1v-6h3v4ZM3 19a2 2 0 0 0 2 2h1v-6H3v4Z"/>',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
        'printer' => '<path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2M6 6l1 15h10l1-15M10 11v6M14 11v6"/>',
        'package' => '<path d="m16.5 9.4 4.5-2.8L12 2 3 6.6l4.5 2.8M3 6.6v10.8L12 22V9.4M21 6.6v10.8L12 22"/><path d="m7.5 11.2 4.5 2.8 4.5-2.8"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'refresh-cw' => '<path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>',
        'percent' => '<line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
        'award' => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
    ];

    /**
     * Solid / branded icons (fill-based).
     *
     * @var array<string, string>
     */
    public const SOLID_ICONS = [
        'bolt' => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>',
        'star' => '<path d="m12 2.5 2.9 6 6.6.8-4.9 4.6 1.3 6.6L12 17.2l-5.9 3.3 1.3-6.6-4.9-4.6 6.6-.8 2.9-6Z"/>',
        'user' => '<circle cx="12" cy="8" r="4.5"/><path d="M3.5 21a8.5 8.5 0 0 1 17 0Z"/>',
        'phone' => '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1Z"/>',
        'cash' => '<path fill-rule="evenodd" d="M2 6h20v12H2V6Zm10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM4 8v2a2 2 0 0 0 2-2H4Zm16 0h-2a2 2 0 0 0 2 2V8ZM4 16h2a2 2 0 0 0-2-2v2Zm16 0v-2a2 2 0 0 0-2 2h2Z"/>',
        'support' => '<circle cx="12" cy="7" r="4"/><path d="M4 21a8 8 0 0 1 16 0H4Z"/><path d="M4 8a8 8 0 0 1 16 0v2h-1.5V8a6.5 6.5 0 0 0-13 0v2H4V8Z"/>',
        'card' => '<path fill-rule="evenodd" d="M2 6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v1H2V6Zm0 4h20v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-8Zm3 5v1.5h5V15H5Z"/>',
        'truck' => '<path d="M1 5h13v11H1V5Zm14 3h4l4 4v4h-8V8Z"/><circle cx="5.5" cy="17.5" r="2.2"/><circle cx="18.5" cy="17.5" r="2.2"/>',
        'whatsapp' => '<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.3-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-3.9-4.7-4.1-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.3.5-.4.4c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.1.1.7-.1 1.3Z"/>',
        'facebook' => '<path d="M14 8.5V6.8c0-.8.2-1.3 1.4-1.3H17V2.3A22 22 0 0 0 14.6 2C12.2 2 10.6 3.5 10.6 6.2v2.3H8v3.3h2.6V22H14V11.8h2.7l.4-3.3H14Z"/>',
        'messenger' => '<path d="M12 2C6.4 2 2 6.1 2 11.3c0 2.8 1.3 5.2 3.3 6.9V22l3.1-1.7c1.1.3 2.3.5 3.6.5 5.6 0 10-4.1 10-9.4S17.6 2 12 2Zm1 12.6-2.5-2.7-5 2.7 5.5-5.8 2.6 2.7 4.9-2.7-5.5 5.8Z"/>',
        'youtube' => '<path d="M22 8.2a3 3 0 0 0-2.1-2.1C18 5.6 12 5.6 12 5.6s-6 0-7.9.5A3 3 0 0 0 2 8.2 31 31 0 0 0 1.6 12c0 1.3.1 2.6.4 3.8a3 3 0 0 0 2.1 2.1c1.9.5 7.9.5 7.9.5s6 0 7.9-.5a3 3 0 0 0 2.1-2.1c.3-1.2.4-2.5.4-3.8s-.1-2.6-.4-3.8ZM10 15V9l5.2 3L10 15Z"/>',
        'instagram' => '<path fill-rule="evenodd" d="M7.5 2h9A5.5 5.5 0 0 1 22 7.5v9a5.5 5.5 0 0 1-5.5 5.5h-9A5.5 5.5 0 0 1 2 16.5v-9A5.5 5.5 0 0 1 7.5 2Zm0 2A3.5 3.5 0 0 0 4 7.5v9A3.5 3.5 0 0 0 7.5 20h9a3.5 3.5 0 0 0 3.5-3.5v-9A3.5 3.5 0 0 0 16.5 4h-9ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm5.3-3.3a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"/>',
        'linkedin' => '<path d="M6.9 21H3.2V9h3.7v12ZM5 7.4a2.1 2.1 0 1 1 0-4.3 2.1 2.1 0 0 1 0 4.3ZM21 21h-3.7v-5.8c0-1.4 0-3.2-2-3.2s-2.2 1.5-2.2 3.1V21H9.4V9h3.5v1.6c.5-.9 1.7-1.9 3.5-1.9 3.8 0 4.5 2.5 4.5 5.7V21Z"/>',
        'tiktok' => '<path d="M16.6 2h-3.4v13.4a2.9 2.9 0 1 1-2.9-2.9c.3 0 .6 0 .9.1V9.1a6.3 6.3 0 1 0 5.4 6.3V8.6a8 8 0 0 0 4.4 1.3V6.5a4.4 4.4 0 0 1-4.4-4.5Z"/>',
    ];

    /**
     * Get array of options suitable for a Filament Select component.
     *
     * @return array<string, string>
     */
    public static function getSelectOptions(): array
    {
        $options = [];

        $labels = [
            'truck' => 'Fast Delivery / Shipping (truck)',
            'shield' => 'Official Warranty / Security (shield)',
            'headphone' => 'Customer Support / Audio (headphone)',
            'support' => 'Support Agent (support)',
            'cash' => 'Cash on Delivery / Money (cash)',
            'card' => 'Online Card Payment (card)',
            'bolt' => 'Flash Deals / Fast Service (bolt)',
            'gift' => 'Gift / Offer (gift)',
            'star' => 'Top Quality / Rating (star)',
            'package' => 'Secure Packaging (package)',
            'clock' => '24/7 Service / Fast Time (clock)',
            'refresh-cw' => 'Easy Return / Replacement (refresh-cw)',
            'percent' => 'Special Discount / Deal (percent)',
            'award' => 'Authorized Brand Dealer (award)',
            'phone' => 'Direct Phone Hotline (phone)',
            'whatsapp' => 'WhatsApp Chat (whatsapp)',
            'store' => 'Physical Showroom / Store (store)',
            'bank' => 'Bank Transfer (bank)',
            'cpu' => 'Processor / CPU (cpu)',
            'gpu' => 'Graphics Card / GPU (gpu)',
            'board' => 'Motherboard (board)',
            'memory' => 'RAM / Memory (memory)',
            'drive' => 'SSD / Hard Drive (drive)',
            'fan' => 'Cooling Fan (fan)',
            'monitor' => 'Monitor / Display (monitor)',
            'keyboard' => 'Keyboard (keyboard)',
            'mouse' => 'Mouse (mouse)',
            'speaker' => 'Sound / Speaker (speaker)',
            'printer' => 'Printer / Scanner (printer)',
            'plug' => 'Power Supply / Accessories (plug)',
            'tower' => 'PC Casing / Desktop (tower)',
        ];

        foreach ($labels as $key => $label) {
            $options[$key] = $label;
        }

        // Add remaining icons
        foreach (array_keys(self::STROKE_ICONS) as $key) {
            if (! isset($options[$key])) {
                $options[$key] = ucfirst(str_replace('-', ' ', $key));
            }
        }

        foreach (array_keys(self::SOLID_ICONS) as $key) {
            if (! isset($options[$key])) {
                $options[$key] = ucfirst(str_replace('-', ' ', $key));
            }
        }

        asort($options);

        return $options;
    }

    /**
     * Render icon HTML with attributes.
     */
    public static function render(string $name, string $class = 'size-5'): string
    {
        if (isset(self::STROKE_ICONS[$name])) {
            return sprintf(
                '<svg class="%s shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
                e($class),
                self::STROKE_ICONS[$name]
            );
        }

        if (isset(self::SOLID_ICONS[$name])) {
            return sprintf(
                '<svg class="%s shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">%s</svg>',
                e($class),
                self::SOLID_ICONS[$name]
            );
        }

        // Fallback default stroke icon
        return sprintf(
            '<svg class="%s shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>',
            e($class)
        );
    }
}
