<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Schemas;

use App\Enums\BannerPlacement;
use App\Support\Filament\TranslatableTabs;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;

/**
 * Banner editor: placement, artwork and text in the main column;
 * visibility, schedule and link in the sidebar.
 */
class BannerForm
{
    /**
     * Upload field => media collection; the "_bn" pair holds the optional Bangla artwork.
     *
     * @var array<string, string>
     */
    public const IMAGE_FIELDS = [
        'desktop_image' => 'desktop',
        'mobile_image' => 'mobile',
        'desktop_image_bn' => 'desktop_bn',
        'mobile_image_bn' => 'mobile_bn',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    self::placementSection(),
                    self::artworkSection(),
                    self::textSection(),
                ])
                    ->columnSpan(['lg' => 2])
                    ->extraAttributes(['class' => 'gx-banner-form']),

                Group::make([
                    self::visibilitySection(),
                    self::linkSection(),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    private static function placementSection(): Section
    {
        return Section::make('Placement')
            ->description(fn (Get $get, string $operation): string => $operation === 'edit'
                ? (string) self::placement($get)?->getLabel()
                : 'Where should this banner appear on the storefront?')
            ->icon('heroicon-o-squares-2x2')
            ->collapsible()
            ->collapsed(fn (string $operation): bool => $operation === 'edit')
            ->schema([
                Radio::make('placement')
                    ->hiddenLabel()
                    ->options(BannerPlacement::class)
                    ->default(BannerPlacement::HeroMain->value)
                    ->required()
                    ->live()
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                    ->gridDirection(GridDirection::Row)
                    ->extraAttributes(['class' => 'gx-choice-cards']),
            ]);
    }

    private static function artworkSection(): Section
    {
        return Section::make('Artwork')
            ->description(fn (Get $get): string => self::artworkSizeNote(self::placement($get)))
            ->icon('heroicon-o-photo')
            ->schema([
                TranslatableTabs::make('artwork', fn (string $suffix, bool $isEnglish): array => [
                    Grid::make(['default' => 1, 'md' => 3])->schema([
                        self::imageUpload("desktop_image{$suffix}")
                            ->label($isEnglish ? 'Desktop image' : 'বাংলা ডেস্কটপ ছবি (Desktop image)')
                            ->helperText($isEnglish ? 'Required for the banner to show.' : 'Leave empty to show the English image.')
                            ->columnSpan(['md' => 2]),

                        self::imageUpload("mobile_image{$suffix}")
                            ->label($isEnglish ? 'Mobile image' : 'বাংলা মোবাইল ছবি (Mobile image)')
                            ->helperText(fn (Get $get): string => self::placement($get)?->usesMobileImage() === false
                                ? 'Not used here: this slot shows the desktop image on phones too.'
                                : 'Optional. A taller crop for phones; without one, phones get the desktop image.'),
                    ]),
                ]),
            ]);
    }

    private static function textSection(): Section
    {
        return Section::make('Banner text')
            ->description('Customers read the words in the artwork. This text names the banner in the list and describes it to screen readers and search engines.')
            ->icon('heroicon-o-chat-bubble-bottom-center-text')
            ->schema([
                TranslatableTabs::make('banner', fn (string $suffix, bool $isEnglish): array => [
                    Grid::make(['default' => 1, 'sm' => 2])->schema([
                        TextInput::make("title{$suffix}")
                            ->label($isEnglish ? 'Title' : 'শিরোনাম (Title)')
                            ->placeholder($isEnglish ? 'e.g. Smartphone Mega Sale' : 'যেমন: স্মার্টফোন মেগা সেল')
                            ->helperText(TranslatableTabs::fallbackHint($isEnglish))
                            ->maxLength(150),

                        TextInput::make("alt_text{$suffix}")
                            ->label($isEnglish ? 'Alt text' : 'ছবির বিবরণ (Alt text)')
                            ->placeholder($isEnglish ? 'e.g. Up to 25% off Samsung and Xiaomi phones' : 'যেমন: স্যামসাং ও শাওমি ফোনে ২৫% পর্যন্ত ছাড়')
                            ->helperText($isEnglish ? 'Describe what the image says. Uses the title when empty.' : TranslatableTabs::fallbackHint($isEnglish))
                            ->maxLength(255),

                        TextInput::make("subtitle{$suffix}")
                            ->label($isEnglish ? 'Subtitle' : 'সাবটাইটেল (Subtitle)')
                            ->placeholder($isEnglish ? 'Save up to 25% on selected models' : 'নির্বাচিত মডেলে ২৫% পর্যন্ত সাশ্রয়')
                            ->maxLength(255),

                        TextInput::make("cta_label{$suffix}")
                            ->label($isEnglish ? 'Button text' : 'বাটনের লেখা (Button text)')
                            ->placeholder($isEnglish ? 'Shop Now' : 'এখনই কিনুন')
                            ->maxLength(50),
                    ]),
                ]),
            ]);
    }

    private static function visibilitySection(): Section
    {
        return Section::make('Visibility')
            ->description('Leave the dates empty to show the banner until you turn it off.')
            ->icon('heroicon-o-eye')
            ->schema([
                Toggle::make('is_active')
                    ->label('Show on storefront')
                    ->helperText('Turn off to hide it without deleting.')
                    ->default(true),

                DateTimePicker::make('starts_at')
                    ->label('Show from')
                    ->placeholder('Right away')
                    ->native(false)
                    ->seconds(false)
                    ->displayFormat('d M Y, h:i A')
                    ->prefixIcon('heroicon-m-calendar'),

                DateTimePicker::make('ends_at')
                    ->label('Hide after')
                    ->placeholder('Never')
                    ->native(false)
                    ->seconds(false)
                    ->displayFormat('d M Y, h:i A')
                    ->prefixIcon('heroicon-m-calendar'),

                TextInput::make('sort_order')
                    ->label('Display order')
                    ->helperText('Lower numbers show first in the same placement.')
                    ->prefixIcon('heroicon-m-arrows-up-down')
                    ->numeric()
                    ->integer()
                    ->default(0),
            ]);
    }

    private static function linkSection(): Section
    {
        return Section::make('Link')
            ->description('Where customers go when they click the banner.')
            ->icon('heroicon-o-link')
            ->schema([
                TextInput::make('url')
                    ->hiddenLabel()
                    ->placeholder('/category/smartphone or https://...')
                    ->prefixIcon('heroicon-m-link')
                    ->default('#'),

                Toggle::make('open_in_new_tab')
                    ->label('Open in a new tab')
                    ->default(false),
            ]);
    }

    private static function imageUpload(string $name): FileUpload
    {
        return FileUpload::make($name)
            ->disk('public')
            ->directory('banners')
            ->image()
            ->imageEditor()
            ->dehydrated(false)
            ->extraAttributes(['class' => 'gx-upload'], merge: true);
    }

    /**
     * "Hero slider artwork: 1672 × 941 px. …" for the chosen placement.
     */
    private static function artworkSizeNote(?BannerPlacement $placement): string
    {
        $size = $placement?->recommendedImageSize();

        $note = $size === null ? 'Upload a wide, high-resolution image.' : "{$placement->getLabel()} artwork: {$size}.";

        return "{$note} Add a Bangla version in the বাংলা tab if the image has words in it.";
    }

    private static function placement(Get $get): ?BannerPlacement
    {
        $placement = $get('placement');

        return $placement instanceof BannerPlacement ? $placement : BannerPlacement::tryFrom((string) $placement);
    }
}
