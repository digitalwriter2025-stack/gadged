<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\CatalogCache;
use App\Services\MetaPixel;
use App\Services\SettingsService;
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

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    /**
     * The saved Conversions API token is never sent to the browser; this only says one exists.
     */
    public bool $hasSavedMetaAccessToken = false;

    public function mount(SettingsService $settingsService, MetaPixel $metaPixel): void
    {
        $allSettings = $settingsService->all();
        $allSettings['meta_pixel']['pixel_id'] = $metaPixel->pixelId();
        $allSettings['meta_pixel']['access_token'] = null;

        $this->hasSavedMetaAccessToken = $metaPixel->hasAccessToken();
        $this->form->fill($allSettings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Section::make('Brand Identity')
                                    ->description('Configure your store identity, names and branding logos.')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('general.site_name')
                                                ->label('Website Name')
                                                ->required()
                                                ->placeholder('Tech Craze BD'),

                                            TextInput::make('general.tagline')
                                                ->label('Website Tagline')
                                                ->placeholder('Leading Tech & Gadget Retailer in Bangladesh'),
                                        ])->columns(2),

                                        Group::make([
                                            FileUpload::make('general.logo')
                                                ->label('Primary Site Logo (Header)')
                                                ->disk('public')
                                                ->directory('settings')
                                                ->image()
                                                ->imageEditor(),

                                            FileUpload::make('general.logo_dark')
                                                ->label('Dark Mode Logo')
                                                ->disk('public')
                                                ->directory('settings')
                                                ->image()
                                                ->imageEditor(),

                                            FileUpload::make('general.favicon')
                                                ->label('Favicon (.ico / .png)')
                                                ->disk('public')
                                                ->directory('settings')
                                                ->image(),
                                        ])->columns(3),
                                    ]),
                            ]),

                        Tab::make('Announcement Bar')
                            ->icon('heroicon-o-megaphone')
                            ->schema([
                                Section::make('Header Announcement / Notice')
                                    ->description('Show an eye-catching promotional top bar at the very top of your storefront.')
                                    ->schema([
                                        Toggle::make('announcement.enabled')
                                            ->label('Enable Top Announcement Bar')
                                            ->default(false),

                                        TextInput::make('announcement.text')
                                            ->label('Notice Message')
                                            ->placeholder('⚡ Free shipping on all orders over ৳5000! Call 01711727860 for instant orders.'),

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

                                        Textarea::make('contact.address')
                                            ->label('Physical Office / Store Address')
                                            ->rows(3)
                                            ->placeholder("Head Office: House 12, Road 5, Dhanmondi, Dhaka 1205\nOutlet: Multiplan Center, Elephant Road"),

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
                                    ->description('Customize visibility of interactive floating tools.')
                                    ->schema([
                                        Group::make([
                                            Toggle::make('header.show_pc_builder')
                                                ->label('Show "PC Builder" Button in Header')
                                                ->default(true),

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
                            ]),

                        Tab::make('Footer')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Footer Branding & Affiliations')
                                    ->description('Control copyright, developer attribution, payment methods, and trade association logos.')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('footer.copyright_text')
                                                ->label('Copyright Text')
                                                ->helperText('Use {year} to dynamically output current year.')
                                                ->placeholder('Copyright © {year}, Tech Craze BD. All Rights Reserved.'),

                                            TextInput::make('footer.developer_name')
                                                ->label('Developer Attribution Name')
                                                ->placeholder('Digital Webars'),

                                            TextInput::make('footer.developer_url')
                                                ->label('Developer Website Link')
                                                ->placeholder('https://www.digitalwebars.com/'),
                                        ])->columns(3),

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
                                    ->schema([
                                        TextInput::make('seo.meta_title')
                                            ->label('Default Meta Title (Homepage)')
                                            ->placeholder('Tech Craze BD — Best Tech Store in Bangladesh'),

                                        Textarea::make('seo.meta_description')
                                            ->label('Default Meta Description')
                                            ->rows(3)
                                            ->placeholder('Shop genuine smartphones, laptops, smartwatches, monitors, and computer accessories with official warranty and fastest delivery.'),

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

                                Section::make('Tracking & Custom Scripts')
                                    ->schema([
                                        TextInput::make('analytics.google_tag_id')
                                            ->label('Google Analytics / Tag Manager ID')
                                            ->placeholder('G-XXXXXXXXXX or GTM-XXXXXXX'),

                                        Textarea::make('analytics.custom_head_scripts')
                                            ->label('Custom Header Scripts (<head> tags)')
                                            ->rows(3)
                                            ->placeholder('<meta name="..." content="..."> or custom styles/scripts'),

                                        Textarea::make('analytics.custom_body_scripts')
                                            ->label('Custom Footer Scripts (before </body>)')
                                            ->rows(3)
                                            ->placeholder('<script>/* your custom tracking or chat script */</script>'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function save(SettingsService $settingsService, CatalogCache $cache): void
    {
        $state = $this->form->getState();

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

    private function metaPixelSection(): Section
    {
        // Bordered box around each on/off option, readable in light and dark mode.
        $optionBox = ['style' => 'border: 1px solid color-mix(in srgb, currentColor 15%, transparent); border-radius: 0.75rem; padding: 0.875rem 1rem;'];

        return Section::make('Meta (Facebook)')
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
