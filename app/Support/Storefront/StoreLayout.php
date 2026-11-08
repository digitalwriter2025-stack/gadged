<?php

namespace App\Support\Storefront;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Product;
use App\Services\CatalogCache;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Storage;

/**
 * Header, footer and floating-button data for every storefront page, read from
 * admin settings and footer menus.
 *
 * @phpstan-type Contact array{hotline: ?string, hotline_tel: ?string, email: ?string, address: ?string, whatsapp: ?string, map_url: ?string, complain_url: ?string}
 */
class StoreLayout
{
    /**
     * Footer menu locations, in display order.
     */
    public const FOOTER_MENU_LOCATIONS = ['footer_information', 'footer_policies'];

    /**
     * Social settings keys with their brand colour, in display order.
     *
     * @var array<string, array{label: string, background: string}>
     */
    public const SOCIAL_NETWORKS = [
        'facebook' => ['label' => 'Facebook', 'background' => 'bg-[#1877f2]'],
        'messenger' => ['label' => 'Messenger', 'background' => 'bg-[#0a7cff]'],
        'youtube' => ['label' => 'YouTube', 'background' => 'bg-[#ff0000]'],
        'instagram' => ['label' => 'Instagram', 'background' => 'bg-[#e1306c]'],
        'linkedin' => ['label' => 'LinkedIn', 'background' => 'bg-[#0a66c2]'],
        'tiktok' => ['label' => 'TikTok', 'background' => 'bg-black'],
    ];

    public function __construct(
        private readonly SettingsService $settings,
        private readonly CatalogCache $cache,
    ) {}

    /**
     * @return array{site: array{name: string, logo: ?string}, contact: Contact, socialLinks: list<array{icon: string, label: string, background: string, url: string}>, footerMenus: list<array{title: string, links: list<array{label: string, url: string, new_tab: bool}>}>, affiliations: list<array{image: string, name: string, url: ?string}>, footer: array{copyright: string, developer_name: ?string, developer_url: ?string, payment_methods_image: ?string}}
     */
    public function data(): array
    {
        return [
            'site' => $this->site(),
            'contact' => $this->contact(),
            'socialLinks' => $this->socialLinks(),
            'footerMenus' => $this->footerMenus(),
            'affiliations' => $this->affiliations(),
            'footer' => $this->footer(),
            'announcement' => $this->announcement(),
            'headerSettings' => $this->headerSettings(),
            'floatingSettings' => $this->floatingSettings(),
            'seo' => $this->seo(),
            'analytics' => $this->analytics(),
        ];
    }

    /**
     * @return array{name: string, logo: ?string}
     */
    public function site(): array
    {
        return [
            'name' => (string) $this->settings->get('general.site_name', config('app.name')),
            'logo' => $this->imageUrl($this->settings->get('general.logo')),
        ];
    }

    /**
     * @return Contact
     */
    public function contact(): array
    {
        $contact = $this->settings->group('contact');
        $hotline = $contact['hotline'] ?? $contact['phone'] ?? null;

        return [
            'hotline' => $hotline,
            'hotline_tel' => $hotline === null ? null : preg_replace('/[^\d+]/', '', $hotline),
            'email' => $contact['email'] ?? null,
            'address' => $contact['address'] ?? null,
            'whatsapp' => isset($contact['whatsapp']) ? preg_replace('/\D/', '', $contact['whatsapp']) : null,
            'map_url' => $contact['map_url'] ?? null,
            'complain_url' => $contact['complain_url'] ?? null,
        ];
    }

    /**
     * Only networks with a saved URL are shown; WhatsApp comes from the contact number.
     *
     * @return list<array{icon: string, label: string, background: string, url: string}>
     */
    public function socialLinks(): array
    {
        $links = [];

        foreach (self::SOCIAL_NETWORKS as $network => $meta) {
            $url = $this->settings->get("social.{$network}");

            if (filled($url)) {
                $links[] = ['icon' => $network, 'url' => $url, ...$meta];
            }
        }

        $whatsapp = $this->contact()['whatsapp'];

        if (filled($whatsapp)) {
            $links[] = ['icon' => 'whatsapp', 'label' => 'WhatsApp', 'background' => 'bg-whatsapp', 'url' => "https://wa.me/{$whatsapp}"];
        }

        return $links;
    }

    /**
     * @return list<array{title: string, links: list<array{label: string, url: string, new_tab: bool}>}>
     */
    public function footerMenus(): array
    {
        return $this->cache->rememberForever('menus.footer', fn (): array => Menu::query()
            ->whereIn('location', self::FOOTER_MENU_LOCATIONS)
            ->with(['rootItems' => fn ($items) => $items->where('is_active', true)->with('linkable')])
            ->get()
            ->sortBy(fn (Menu $menu): int|false => array_search($menu->location, self::FOOTER_MENU_LOCATIONS, true))
            ->map(fn (Menu $menu): array => [
                'title' => $menu->name,
                'links' => $menu->rootItems
                    ->map(fn (MenuItem $item): ?array => $this->menuLink($item))
                    ->filter()
                    ->values()
                    ->all(),
            ])
            ->reject(fn (array $menu): bool => $menu['links'] === [])
            ->values()
            ->all());
    }

    /**
     * @return list<array{image: string, name: string, url: ?string}>
     */
    public function affiliations(): array
    {
        return collect($this->settings->get('footer.affiliations', []))
            ->map(fn (array $item): ?array => ($image = $this->imageUrl($item['image'] ?? null)) === null ? null : [
                'image' => $image,
                'name' => (string) ($item['name'] ?? ''),
                'url' => $item['url'] ?? null,
            ])
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{copyright: string, developer_name: ?string, developer_url: ?string, payment_methods_image: ?string}
     */
    public function footer(): array
    {
        $footer = $this->settings->group('footer');

        return [
            'copyright' => filled($footer['copyright_text'] ?? null)
                ? str_replace('{year}', (string) now()->year, $footer['copyright_text'])
                : 'Copyright © '.now()->year.', '.$this->site()['name'].'. All Rights Reserved.',
            'developer_name' => $footer['developer_name'] ?? null,
            'developer_url' => $footer['developer_url'] ?? null,
            'payment_methods_image' => $this->imageUrl($footer['payment_methods_image'] ?? null),
        ];
    }

    /**
     * Public URL for an image path saved by an admin upload field (public disk).
     */
    public function imageUrl(mixed $path): ?string
    {
        return is_string($path) && $path !== '' ? Storage::disk('public')->url($path) : null;
    }

    /**
     * Resolved link for a menu item; items pointing at a hidden page or category are dropped.
     *
     * @return array{label: string, url: string, new_tab: bool}|null
     */
    private function menuLink(MenuItem $item): ?array
    {
        $url = match (true) {
            $item->linkable instanceof Page => $item->linkable->is_active ? route('store.pages.show', $item->linkable) : null,
            $item->linkable instanceof Category => $item->linkable->is_active ? route('store.categories.show', $item->linkable->slug) : null,
            $item->linkable instanceof Product => route('store.products.show', $item->linkable->slug),
            default => $item->url,
        };

        return blank($url) ? null : ['label' => $item->label, 'url' => $url, 'new_tab' => $item->open_in_new_tab];
    }

    /**
     * @return array{enabled: bool, text: ?string, link: ?string, bg_color: string, text_color: string}
     */
    public function announcement(): array
    {
        $a = $this->settings->group('announcement');

        return [
            'enabled' => (bool) ($a['enabled'] ?? false),
            'text' => $a['text'] ?? null,
            'link' => $a['link'] ?? null,
            'bg_color' => $a['bg_color'] ?? '#ea580c',
            'text_color' => $a['text_color'] ?? '#ffffff',
        ];
    }

    /**
     * @return array{show_pc_builder: bool}
     */
    public function headerSettings(): array
    {
        $h = $this->settings->group('header');

        return [
            'show_pc_builder' => (bool) ($h['show_pc_builder'] ?? true),
        ];
    }

    /**
     * @return array{show_hotline: bool, show_whatsapp: bool, show_scroll_top: bool}
     */
    public function floatingSettings(): array
    {
        $f = $this->settings->group('floating');

        return [
            'show_hotline' => (bool) ($f['show_hotline'] ?? true),
            'show_whatsapp' => (bool) ($f['show_whatsapp'] ?? true),
            'show_scroll_top' => (bool) ($f['show_scroll_top'] ?? true),
        ];
    }

    /**
     * @return array{meta_title: ?string, meta_description: ?string, og_image: ?string, robots_index: bool, favicon: ?string}
     */
    public function seo(): array
    {
        $s = $this->settings->group('seo');

        return [
            'meta_title' => $s['meta_title'] ?? null,
            'meta_description' => $s['meta_description'] ?? null,
            'og_image' => $this->imageUrl($s['og_image'] ?? null),
            'robots_index' => (bool) ($s['robots_index'] ?? true),
            'favicon' => $this->imageUrl($this->settings->get('general.favicon')),
        ];
    }

    /**
     * @return array{google_tag_id: ?string, custom_head_scripts: ?string, custom_body_scripts: ?string}
     */
    public function analytics(): array
    {
        $a = $this->settings->group('analytics');

        return [
            'google_tag_id' => $a['google_tag_id'] ?? null,
            'custom_head_scripts' => $a['custom_head_scripts'] ?? null,
            'custom_body_scripts' => $a['custom_body_scripts'] ?? null,
        ];
    }
}
