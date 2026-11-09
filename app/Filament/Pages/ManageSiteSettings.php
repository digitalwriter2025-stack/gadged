<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Services\CatalogCache;
use App\Services\GoogleTag;
use App\Services\MetaPixel;
use App\Services\SettingsService;
use App\Support\Filament\TranslatableTabs;
use App\Support\Localization;
use App\Support\Storefront\StoreLayout;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use RuntimeException;

class ManageSiteSettings extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Website & Store Settings';

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.manage-site-settings';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can(Permission::SettingsManage->value);
    }

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    /**
     * The saved Conversions API token is never sent to the browser; this only says one exists.
     */
    public bool $hasSavedMetaAccessToken = false;

    public function getSubheading(): string
    {
        return 'Brand, colors, contact details, storefront layout and tracking: everything your customers see, in one place.';
    }

    public function mount(SettingsService $settingsService, MetaPixel $metaPixel, GoogleTag $googleTag): void
    {
        $allSettings = $settingsService->all();
        $allSettings['meta_pixel']['pixel_id'] = $metaPixel->pixelId();
        $allSettings['meta_pixel']['access_token'] = null;
        // Also picks up an ID saved in the old single google_tag_id field.
        $allSettings['analytics']['ga4_measurement_id'] = $googleTag->measurementId();
        $allSettings['analytics']['google_ads_id'] = $googleTag->adsId();
        $allSettings['analytics']['gtm_id'] = $googleTag->gtmId();

        $this->hasSavedMetaAccessToken = $metaPixel->hasAccessToken();
        $this->form->fill($allSettings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Settings')
                    ->vertical()
                    ->contained(false)
                    ->persistTabInQueryString()
                    ->extraAttributes(['class' => 'gx-settings-tabs'])
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Section::make('Store Identity')
                                    ->description('The name and tagline shown in the browser tab, emails and search results.')
                                    ->icon('heroicon-o-building-storefront')
                                    ->schema([
                                        Grid::make(['default' => 1, 'md' => 2])->schema([
                                            TextInput::make('general.site_name')
                                                ->label('Website Name')
                                                ->required()
                                                ->maxLength(100)
                                                ->prefixIcon('heroicon-o-tag')
                                                ->placeholder('Tech Craze BD'),

                                            TextInput::make('general.tagline')
                                                ->label('Website Tagline')
                                                ->maxLength(160)
                                                ->prefixIcon('heroicon-o-chat-bubble-bottom-center-text')
                                                ->placeholder('Leading Tech & Gadget Retailer in Bangladesh'),
                                        ]),
                                    ]),

                                Section::make('Logos & Favicon')
                                    ->description('Transparent PNG, SVG or WebP files look best. Changes go live on the storefront as soon as you save.')
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        Grid::make(['default' => 1, 'md' => 2, 'xl' => 3])->schema([
                                            $this->brandImageUpload('general.logo', 'Primary Logo (Header)')
                                                ->imageEditor()
                                                ->helperText('Shown in the header on light backgrounds. Wide logo, about 400 × 100 px.'),

                                            $this->brandImageUpload('general.logo_dark', 'Dark Mode Logo')
                                                ->imageEditor()
                                                ->helperText('Light-coloured version for dark backgrounds. Leave empty to reuse the primary logo.')
                                                ->extraAttributes(['class' => 'gx-upload-dark'], merge: true),

                                            $this->brandImageUpload('general.favicon', 'Favicon')
                                                ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml', 'image/webp'])
                                                ->maxSize(512)
                                                ->helperText('Square icon for the browser tab. PNG or ICO, 512 × 512 px.'),
                                        ]),
                                    ]),
                            ]),

                        $this->themeColorsTab(),

                        Tab::make('Language')
                            ->icon('heroicon-o-language')
                            ->schema([
                                Section::make('Website Language')
                                    ->description('The storefront is available in English and বাংলা. Bangla text is entered next to the English text in every form; anything left empty in Bangla is shown in English.')
                                    ->icon('heroicon-o-language')
                                    ->schema([
                                        Grid::make(['default' => 1, 'md' => 2])->schema([
                                            Select::make('localization.default_locale')
                                                ->label('Default Language')
                                                ->helperText('What a first-time visitor sees. Visitors who pick a language keep it on their next visit.')
                                                ->options(collect(Localization::locales())->map(fn (array $language): string => $language['name'])->all())
                                                ->selectablePlaceholder(false)
                                                ->required(),

                                            Toggle::make('localization.show_switcher')
                                                ->label('Show the English / বাংলা switch in the header and mobile menu')
                                                ->inline(false),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Announcement Bar')
                            ->icon('heroicon-o-megaphone')
                            ->schema([
                                Section::make('Header Announcement / Notice')
                                    ->icon('heroicon-o-megaphone')
                                    ->description('Show an eye-catching promotional top bar at the very top of your storefront.')
                                    ->schema([
                                        Toggle::make('announcement.enabled')
                                            ->label('Enable Top Announcement Bar')
                                            ->default(false),

                                        TextInput::make('announcement.text')
                                            ->label('Notice Message')
                                            ->placeholder('⚡ Free shipping on all orders over ৳5000! Call 01711727860 for instant orders.'),

                                        TextInput::make('announcement.text_bn')
                                            ->label('নোটিশ (Bangla message, optional)')
                                            ->helperText('Shown to visitors browsing in বাংলা. Leave empty to show the English message.')
                                            ->placeholder('⚡ ৳৫০০০-এর বেশি অর্ডারে ফ্রি ডেলিভারি!'),

                                        TextInput::make('announcement.link')
                                            ->label('Action URL (Optional)')
                                            ->placeholder('/offers or https://...'),

                                        Group::make([
                                            ColorPicker::make('announcement.bg_color')
                                                ->label('Background Color')
                                                ->default('#ea580c'),

                                            ColorPicker::make('announcement.text_color')
                                                ->label('Text Color')
                                                ->default('#ffffff'),
                                        ])->columns(2),
                                    ]),
                            ]),

                        Tab::make('Contact & Location')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Section::make('Customer Contact Information')
                                    ->icon('heroicon-o-phone')
                                    ->description('These contact details are shown on the header, footer, contact page, and floating quick buttons.')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('contact.hotline')
                                                ->label('Hotline Number')
                                                ->placeholder('+8801711727860'),

                                            TextInput::make('contact.phone')
                                                ->label('Alternative Phone')
                                                ->placeholder('+8801700000000'),

                                            TextInput::make('contact.whatsapp')
                                                ->label('WhatsApp Number (with country code)')
                                                ->placeholder('8801711727860')
                                                ->helperText('Used for direct WhatsApp floating chat button.'),

                                            TextInput::make('contact.email')
                                                ->label('Contact / Support Email')
                                                ->email()
                                                ->placeholder('techcrazebd@gmail.com'),
                                        ])->columns(2),

                                        Grid::make(['default' => 1, 'md' => 2])->schema([
                                            Textarea::make('contact.address')
                                                ->label('Physical Office / Store Address')
                                                ->rows(3)
                                                ->placeholder("Head Office: House 12, Road 5, Dhanmondi, Dhaka 1205\nOutlet: Multiplan Center, Elephant Road"),

                                            Textarea::make('contact.address_bn')
                                                ->label('ঠিকানা (Bangla address, optional)')
                                                ->rows(3)
                                                ->placeholder('প্রধান কার্যালয়: বাড়ি ১২, রোড ৫, ধানমন্ডি, ঢাকা ১২০৫'),
                                        ]),

                                        Group::make([
                                            TextInput::make('contact.map_url')
                                                ->label('Google Maps URL')
                                                ->placeholder('https://maps.google.com/?q=...'),

                                            TextInput::make('contact.complain_url')
                                                ->label('Complain Box URL or Mailto')
                                                ->placeholder('mailto:techcrazebd@gmail.com'),

                                            TextInput::make('contact.office_hours')
                                                ->label('Working Days & Hours')
                                                ->placeholder('Sat - Thu: 10:00 AM - 8:00 PM'),
                                        ])->columns(3),
                                    ]),
                            ]),

                        Tab::make('Social Networks')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Section::make('Official Social Media Profiles')
                                    ->icon('heroicon-o-share')
                                    ->description('Links are displayed in the footer and mobile menu.')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('social.facebook')
                                                ->label('Facebook Page URL')
                                                ->prefix('https://facebook.com/')
                                                ->placeholder('yourpage'),

                                            TextInput::make('social.messenger')
                                                ->label('Messenger Link')
                                                ->prefix('https://m.me/')
                                                ->placeholder('yourpage'),

                                            TextInput::make('social.youtube')
                                                ->label('YouTube Channel URL')
                                                ->prefix('https://youtube.com/@')
                                                ->placeholder('yourchannel'),

                                            TextInput::make('social.instagram')
                                                ->label('Instagram URL')
                                                ->prefix('https://instagram.com/')
                                                ->placeholder('yourprofile'),

                                            TextInput::make('social.linkedin')
                                                ->label('LinkedIn URL')
                                                ->prefix('https://linkedin.com/company/')
                                                ->placeholder('yourcompany'),

                                            TextInput::make('social.tiktok')
                                                ->label('TikTok URL')
                                                ->prefix('https://tiktok.com/@')
                                                ->placeholder('yourhandle'),
                                        ])->columns(2),
                                    ]),
                            ]),

                        Tab::make('Layout & Buttons')
                            ->icon('heroicon-o-squares-plus')
                            ->schema([
                                Section::make('Header & Floating Controls')
                                    ->icon('heroicon-o-cursor-arrow-rays')
                                    ->description('Customize visibility of interactive floating tools.')
                                    ->schema([
                                        Group::make([
                                            Toggle::make('header.show_pc_builder')
                                                ->label('Enable PC Builder (header button and page)')
                                                ->default(false),

                                            Toggle::make('floating.show_hotline')
                                                ->label('Show Floating Hotline Call Button')
                                                ->default(true),

                                            Toggle::make('floating.show_whatsapp')
                                                ->label('Show Floating WhatsApp Chat Button')
                                                ->default(true),

                                            Toggle::make('floating.show_scroll_top')
                                                ->label('Show "Back to Top" Floating Button')
                                                ->default(true),
                                        ])->columns(2),
                                    ]),

                                $this->headerQuickLinkSection('track', 'Order Track Link', 'heroicon-o-truck'),

                                $this->headerQuickLinkSection('combo', 'Combo Link', 'heroicon-o-cube'),
                            ]),

                        Tab::make('Footer')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Footer Branding & Affiliations')
                                    ->icon('heroicon-o-document-text')
                                    ->description('Control copyright, developer attribution, payment methods, and trade association logos.')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('footer.copyright_text')
                                                ->label('Copyright Text')
                                                ->helperText('Use {year} to dynamically output current year.')
                                                ->placeholder('Copyright © {year}, Tech Craze BD. All Rights Reserved.'),

                                            TextInput::make('footer.copyright_text_bn')
                                                ->label('কপিরাইট (Bangla, optional)')
                                                ->helperText('{year} works here too.')
                                                ->placeholder('কপিরাইট © {year}, Tech Craze BD। সর্বস্বত্ব সংরক্ষিত।'),

                                            TextInput::make('footer.developer_name')
                                                ->label('Developer Attribution Name')
                                                ->placeholder('Digital Webars'),

                                            TextInput::make('footer.developer_url')
                                                ->label('Developer Website Link')
                                                ->placeholder('https://www.digitalwebars.com/'),
                                        ])->columns(2),

                                        FileUpload::make('footer.payment_methods_image')
                                            ->label('Accepted Payment Methods Banner')
                                            ->disk('public')
                                            ->directory('settings')
                                            ->image(),

                                        Repeater::make('footer.affiliations')
                                            ->label('Affiliations & Badges (e.g. BASIS, BCS, e-CAB)')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Organization Name')
                                                    ->required(),

                                                FileUpload::make('image')
                                                    ->label('Badge Logo')
                                                    ->disk('public')
                                                    ->directory('settings/affiliations')
                                                    ->image()
                                                    ->required(),

                                                TextInput::make('url')
                                                    ->label('Website URL (Optional)')
                                                    ->url(),
                                            ])
                                            ->columns(3)
                                            ->collapsible()
                                            ->defaultItems(0),
                                    ]),
                            ]),

                        Tab::make('SEO & Analytics')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make('Search Engine Optimization (SEO)')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->schema([
                                        TranslatableTabs::make('seo', fn (string $suffix, bool $isEnglish): array => [
                                            TextInput::make("seo.meta_title{$suffix}")
                                                ->label($isEnglish ? 'Default Meta Title (Homepage)' : 'মেটা টাইটেল (Bangla Meta Title)')
                                                ->placeholder($isEnglish ? 'Tech Craze BD — Best Tech Store in Bangladesh' : 'Tech Craze BD — বাংলাদেশের সেরা টেক স্টোর'),

                                            Textarea::make("seo.meta_description{$suffix}")
                                                ->label($isEnglish ? 'Default Meta Description' : 'মেটা বিবরণ (Bangla Meta Description)')
                                                ->rows(3)
                                                ->placeholder($isEnglish ? 'Shop genuine smartphones, laptops, smartwatches, monitors, and computer accessories with official warranty and fastest delivery.' : 'অফিসিয়াল ওয়ারেন্টি ও দ্রুত ডেলিভারিসহ আসল স্মার্টফোন, স্মার্টওয়াচ ও এক্সেসরিজ কিনুন।'),
                                        ]),

                                        FileUpload::make('seo.og_image')
                                            ->label('Default Social Share Image (OG Image 1200x630)')
                                            ->disk('public')
                                            ->directory('settings')
                                            ->image(),

                                        Toggle::make('seo.robots_index')
                                            ->label('Allow Search Engine Indexing (robots index, follow)')
                                            ->default(true),
                                    ]),

                                $this->metaPixelSection(),

                                $this->googleSection(),

                                Section::make('Other tags')
                                    ->icon('heroicon-o-code-bracket')
                                    ->schema([
                                        Textarea::make('analytics.custom_head_scripts')
                                            ->label('Extra code for the page head')
                                            ->rows(3)
                                            ->helperText('TikTok, Snap, Hotjar or any other tag. Printed as-is - only paste code you trust.'),

                                        Textarea::make('analytics.custom_body_scripts')
                                            ->label('Extra code after the body tag')
                                            ->rows(3)
                                            ->helperText('For the noscript half of a tag, or a chat widget.'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function save(SettingsService $settingsService, CatalogCache $cache): void
    {
        $state = $this->form->getState();

        // Replaced by the separate GA4, Ads and Tag Manager fields; a stale value would bring a cleared ID back.
        $state['analytics']['google_tag_id'] = null;

        foreach ($state as $group => $values) {
            if (is_array($values)) {
                $settingsService->setMany($group, $values);
            }
        }

        $settingsService->flush();
        $cache->flush();

        if (filled($state['meta_pixel']['access_token'] ?? null)) {
            $this->hasSavedMetaAccessToken = true;
            $this->data['meta_pixel']['access_token'] = null;
        }

        Notification::make()
            ->title('Settings Saved Successfully!')
            ->body('Store settings have been updated and cache reloaded.')
            ->success()
            ->send();
    }

    /**
     * Sends one PageView with the saved Meta settings and reports what the Graph API answered.
     */
    public function sendMetaTestEvent(MetaPixel $metaPixel): void
    {
        try {
            $metaPixel->sendTestEvent();
        } catch (RequestException $exception) {
            Notification::make()
                ->title('Meta rejected the test event')
                ->body($exception->response->json('error.error_user_msg') ?? $exception->response->json('error.message') ?? $exception->getMessage())
                ->danger()
                ->persistent()
                ->send();

            return;
        } catch (ConnectionException|RuntimeException $exception) {
            Notification::make()
                ->title('Test event not sent')
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        Notification::make()
            ->title('Meta received the test event')
            ->body($metaPixel->testEventCode() !== null
                ? 'Open Events Manager -> your pixel -> Test events to see it.'
                : 'No test event code is saved, so it was counted as a live PageView.')
            ->success()
            ->send();
    }

    /**
     * Puts the stock storefront colours back in the form; nothing changes on the storefront until Save.
     */
    public function resetThemeColors(): void
    {
        $this->data['theme'] = config('shop.settings.theme');

        Notification::make()
            ->title('Default colors restored')
            ->body('Click Save to put them live on the storefront.')
            ->success()
            ->send();
    }

    /**
     * Logo-style upload on the public disk with a roomy, contained preview.
     */
    private function brandImageUpload(string $statePath, string $label): FileUpload
    {
        return FileUpload::make($statePath)
            ->label($label)
            ->disk('public')
            ->directory('settings')
            ->image()
            ->maxSize(2048)
            ->imagePreviewHeight('140')
            ->openable()
            ->extraAttributes(['class' => 'gx-upload'], merge: true);
    }

    /**
     * Show/hide, wording and target of one header quick link; empty fields keep the built-in text and page.
     */
    private function headerQuickLinkSection(string $key, string $title, string $icon): Section
    {
        $defaults = StoreLayout::HEADER_QUICK_LINKS[$key];

        return Section::make($title)
            ->description('The header link next to the cart, also shown in the mobile menu. Leave a field empty to keep the default.')
            ->icon($icon)
            ->schema([
                Toggle::make("header.{$key}_enabled")
                    ->label('Show this link in the header')
                    ->default(true),

                TranslatableTabs::make("header-{$key}", fn (string $suffix, bool $isEnglish): array => [
                    Grid::make(['default' => 1, 'md' => 2])->schema([
                        TextInput::make("header.{$key}_label{$suffix}")
                            ->label($isEnglish ? 'Title' : 'শিরোনাম (Bangla Title)')
                            ->maxLength(30)
                            ->placeholder($isEnglish ? $defaults['label'] : __($defaults['label'], locale: 'bn'))
                            ->helperText(TranslatableTabs::fallbackHint($isEnglish)),

                        TextInput::make("header.{$key}_caption{$suffix}")
                            ->label($isEnglish ? 'Subtitle' : 'সাবটাইটেল (Bangla Subtitle)')
                            ->maxLength(40)
                            ->placeholder($isEnglish ? $defaults['caption'] : __($defaults['caption'], locale: 'bn'))
                            ->helperText(TranslatableTabs::fallbackHint($isEnglish)),
                    ]),
                ]),

                TextInput::make("header.{$key}_url")
                    ->label('Link URL (Optional)')
                    ->maxLength(255)
                    ->regex('#^(/|https?://)#i')
                    ->validationMessages(['regex' => 'Start with / for a page on this site, or with https://.'])
                    ->placeholder(route($defaults['route'], absolute: false))
                    ->helperText('A page on this site such as /offers, or a full https:// link.'),
            ]);
    }

    private function themeColorsTab(): Tab
    {
        return Tab::make('Theme Colors')
            ->icon('heroicon-o-swatch')
            ->schema([
                Section::make('Buttons')
                    ->description('Add to Cart, View Details, Place Order, Apply and every other main button on the storefront.')
                    ->icon('heroicon-o-cursor-arrow-rays')
                    ->afterHeader([
                        Action::make('resetThemeColors')
                            ->label('Reset to default colors')
                            ->icon('heroicon-o-arrow-path')
                            ->color('gray')
                            ->action(fn () => $this->resetThemeColors()),
                    ])
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])->schema([
                            $this->themeColorPicker('button', 'Button Color', 'Background of the main buttons.'),
                            $this->themeColorPicker('button_hover', 'Button Hover Color', 'When the mouse is over a button.'),
                            $this->themeColorPicker('button_text', 'Button Text Color', 'Text and icons on the buttons.'),
                        ]),
                    ]),

                Section::make('Brand & Text')
                    ->description('Highlights, links, prices and badges across the website.')
                    ->icon('heroicon-o-paint-brush')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])->schema([
                            $this->themeColorPicker('accent', 'Primary Brand Color', 'Links, icons, section heading bars, active menu items and badges.'),
                            $this->themeColorPicker('accent_dark', 'Primary Hover Color', 'Links and highlights when hovered.'),
                            $this->themeColorPicker('builder', 'Secondary Color', 'Dark links, form focus outlines, filter counts and the PC Builder total.'),
                            $this->themeColorPicker('price', 'Price Color', 'Selling prices and flash-sale timers.'),
                            $this->themeColorPicker('savings', 'Savings / Discount Color', 'Discount badges and "You save" amounts.'),
                        ]),
                    ]),

                Section::make('Header, Background & Footer')
                    ->icon('heroicon-o-window')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])->schema([
                            $this->themeColorPicker('topbar', 'Header Bar Color', 'The main header bar, also used as the mobile browser bar color.'),
                            $this->themeColorPicker('surface', 'Page Background', 'Light background behind sections and cards.'),
                            $this->themeColorPicker('footer', 'Footer Background', 'Background of the footer.'),
                            $this->themeColorPicker('footer_link', 'Footer Link Color', 'Headings and links in the footer.'),
                            $this->themeColorPicker('whatsapp', 'WhatsApp Button Color', 'WhatsApp chat buttons.'),
                        ]),
                    ]),
            ]);
    }

    /**
     * Hex colour field for one "theme" setting; an empty field falls back to the stylesheet colour.
     */
    private function themeColorPicker(string $key, string $label, string $helperText): ColorPicker
    {
        return ColorPicker::make("theme.{$key}")
            ->label($label)
            ->hex()
            ->hexColor()
            ->placeholder(config("shop.settings.theme.{$key}"))
            ->helperText($helperText);
    }

    private function googleSection(): Section
    {
        return Section::make('Google')
            ->icon('heroicon-o-globe-alt')
            ->description('GA4, Google Ads and Tag Manager. Product views, add to cart, add to wishlist, checkout, purchase and search are sent automatically, once each.')
            ->schema([
                TextInput::make('analytics.ga4_measurement_id')
                    ->label('Google Analytics ID (GA4)')
                    ->placeholder('G-XXXXXXXXXX')
                    ->regex('/^\s*G-[A-Z0-9]{4,15}\s*$/i')
                    ->validationMessages(['regex' => 'A GA4 Measurement ID looks like G-XXXXXXXXXX.'])
                    ->dehydrateStateUsing(fn (?string $state): ?string => GoogleTag::normalizeId($state))
                    ->helperText('Sends page views and the shop events straight to GA4. Leave blank if GA4 is set up inside Tag Manager, or every event is counted twice.'),

                TextInput::make('analytics.google_ads_id')
                    ->label('Google Ads Conversion ID')
                    ->placeholder('AW-123456789')
                    ->regex('/^\s*AW-\d{6,15}\s*$/i')
                    ->validationMessages(['regex' => 'A Google Ads Conversion ID looks like AW-123456789.'])
                    ->required(fn (Get $get): bool => filled($get('analytics.google_ads_conversion_label')))
                    ->dehydrateStateUsing(fn (?string $state): ?string => GoogleTag::normalizeId($state))
                    ->helperText('Google Ads → Goals → Conversions → your Purchase action → Tag setup. Leave blank if the conversion tag is in Tag Manager.'),

                TextInput::make('analytics.google_ads_conversion_label')
                    ->label('Google Ads Conversion Label')
                    ->placeholder('AbC-D_efG-h12_34-567')
                    ->regex('/^\s*[A-Za-z0-9_-]{4,64}\s*$/')
                    ->validationMessages(['regex' => 'Paste only the label: the part after the slash, without "AW-".'])
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? trim($state) : null)
                    ->helperText('The part after the slash in send_to "AW-123456789/AbC-D_efG...". Reported once per order, with the order value and number. Do not also import the GA4 purchase into Google Ads, or each order is counted twice.'),

                TextInput::make('analytics.gtm_id')
                    ->label('Google Tag Manager ID')
                    ->placeholder('GTM-XXXXXXX')
                    ->regex('/^\s*GTM-[A-Z0-9]{4,12}\s*$/i')
                    ->validationMessages(['regex' => 'A Tag Manager container ID looks like GTM-XXXXXXX.'])
                    ->dehydrateStateUsing(fn (?string $state): ?string => GoogleTag::normalizeId($state))
                    ->helperText('Prints both GTM snippets - head and body - on every storefront page, and pushes view_item, add_to_cart, add_to_wishlist, begin_checkout, purchase and search to the dataLayer in the GA4 ecommerce format.'),
            ]);
    }

    private function metaPixelSection(): Section
    {
        // Bordered box around each on/off option, readable in light and dark mode.
        $optionBox = ['style' => 'border: 1px solid color-mix(in srgb, currentColor 15%, transparent); border-radius: 0.75rem; padding: 0.875rem 1rem;'];

        return Section::make('Meta (Facebook)')
            ->icon('heroicon-o-chart-bar')
            ->description('Meta Pixel and Conversions API. Product views, add to cart, checkout, purchase and search are sent automatically.')
            ->afterHeader([
                Action::make('sendMetaTestEvent')
                    ->label('Send test event')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('gray')
                    ->tooltip('Save first: the test uses the saved pixel ID, token and test event code.')
                    ->action(fn (MetaPixel $metaPixel) => $this->sendMetaTestEvent($metaPixel)),
            ])
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('meta_pixel.pixel_id')
                        ->label('Meta Pixel ID (dataset ID)')
                        ->placeholder('1234567890123456')
                        ->regex('/^\d{5,20}$/')
                        ->validationMessages(['regex' => 'The Pixel ID is digits only, as shown in Events Manager.'])
                        ->required(fn (Get $get): bool => (bool) $get('meta_pixel.server_enabled'))
                        ->helperText('Events Manager -> Data sources -> your pixel. Digits only.'),

                    Checkbox::make('meta_pixel.browser_enabled')
                        ->label('Fire the browser pixel')
                        ->helperText('Turn this off only if Google Tag Manager already loads the pixel for you.')
                        ->default(true)
                        ->extraFieldWrapperAttributes($optionBox),

                    TextInput::make('meta_pixel.access_token')
                        ->label('Conversions API access token')
                        ->password()
                        ->autocomplete('new-password')
                        ->placeholder(fn (): string => $this->hasSavedMetaAccessToken ? '•••••••• (a key is saved)' : 'EAAG...')
                        ->helperText('Events Manager -> Settings -> Conversions API -> Generate access token. Stored encrypted; leave blank to keep the saved one.')
                        ->hintAction(
                            Action::make('forgetMetaAccessToken')
                                ->label('Remove saved token')
                                ->color('danger')
                                ->visible(fn (): bool => $this->hasSavedMetaAccessToken)
                                ->requiresConfirmation()
                                ->action(function (SettingsService $settingsService): void {
                                    $settingsService->set('meta_pixel.access_token', null);
                                    $settingsService->flush();
                                    $this->hasSavedMetaAccessToken = false;

                                    Notification::make()->title('Conversions API token removed')->success()->send();
                                }),
                        )
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->dehydrateStateUsing(fn (string $state): string => MetaPixel::encryptAccessToken($state)),

                    Checkbox::make('meta_pixel.server_enabled')
                        ->label('Send events from the server (Conversions API)')
                        ->helperText('Recovers the conversions ad blockers and iOS drop. Needs a pixel ID and a token.')
                        ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            if ($value && ! $this->hasSavedMetaAccessToken && blank($this->data['meta_pixel']['access_token'] ?? null)) {
                                $fail('Add a Conversions API access token to send events from the server.');
                            }
                        })
                        ->extraFieldWrapperAttributes($optionBox),

                    TextInput::make('meta_pixel.test_event_code')
                        ->label('Test event code')
                        ->placeholder('TEST12345')
                        ->alphaNum()
                        ->maxLength(50)
                        ->helperText('Only while you are checking the setup - clear it before running live campaigns.'),

                    Checkbox::make('meta_pixel.advanced_matching')
                        ->label('Advanced matching')
                        ->helperText('Sends a hashed email and phone so more of the traffic is matched to real accounts.')
                        ->extraFieldWrapperAttributes($optionBox),

                    Select::make('meta_pixel.content_id')
                        ->label('Catalogue product ID')
                        ->options(MetaPixel::CONTENT_ID_OPTIONS)
                        ->default('id')
                        ->required()
                        ->selectablePlaceholder(false)
                        ->helperText('Must match the id column of the feed uploaded to Commerce Manager, or dynamic ads show the wrong item.'),

                    TextInput::make('meta_pixel.api_version')
                        ->label('Graph API version')
                        ->placeholder(MetaPixel::DEFAULT_API_VERSION)
                        ->default(MetaPixel::DEFAULT_API_VERSION)
                        ->required()
                        ->regex('/^v\d+\.\d+$/')
                        ->validationMessages(['regex' => 'Use the form v21.0.'])
                        ->helperText('Leave this alone unless Meta deprecates the version.'),
                ]),

                TextInput::make('meta_pixel.domain_verification')
                    ->label('Domain verification code')
                    ->maxLength(500)
                    ->dehydrateStateUsing(fn (?string $state): ?string => MetaPixel::domainVerificationCode($state))
                    ->helperText('Business Settings -> Brand safety -> Domains. Paste only the content value, not the whole meta tag.'),
            ]);
    }
}
