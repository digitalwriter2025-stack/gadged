<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomeSections\Schemas;

use App\Enums\HomeSectionType;
use App\Enums\ProductCollectionSource;
use App\Filament\Resources\Banners\BannerResource;
use App\Filament\Resources\ServiceHighlights\ServiceHighlightResource;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Product;
use App\Support\Filament\ProductPicker;
use App\Support\Filament\TranslatableTabs;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;

/**
 * Homepage block editor. The main column walks through the block step by step
 * (type, heading, products, look) and only shows what the chosen type uses;
 * the sidebar holds visibility and position.
 */
class HomeSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    self::typeSection(),
                    self::managedElsewhereCallout(),
                    self::headingSection(),
                    self::productsSection(),
                    self::displaySection(),
                    self::lookSection(),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    self::visibilitySection(),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    private static function typeSection(): Section
    {
        return Section::make('Block type')
            ->description(fn (Get $get): string => self::sectionType($get)?->getLabel() ?? 'What should this block show on the homepage?')
            ->icon('heroicon-o-squares-2x2')
            ->collapsible()
            ->collapsed(fn (string $operation): bool => $operation === 'edit')
            ->schema([
                Radio::make('type')
                    ->hiddenLabel()
                    ->options(HomeSectionType::class)
                    ->required()
                    ->live()
                    ->columns(['default' => 1, 'md' => 2])
                    ->gridDirection(GridDirection::Row)
                    ->extraAttributes(['class' => 'gx-choice-cards']),
            ]);
    }

    /**
     * Hero, promo banner and service highlight blocks only switch their strip on; the pictures live elsewhere.
     */
    private static function managedElsewhereCallout(): Callout
    {
        return Callout::make(fn (Get $get): string => self::isType($get, HomeSectionType::ServiceHighlights)
            ? 'The badges in this block are edited in Service Highlights'
            : 'The pictures in this block are edited in Banners & Sliders')
            ->description('Here you only choose whether the block is shown and where it sits on the homepage.')
            ->info()
            ->footerActions([
                Action::make('openContent')
                    ->label(fn (Get $get): string => self::isType($get, HomeSectionType::ServiceHighlights) ? 'Open Service Highlights' : 'Open Banners & Sliders')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Get $get): string => self::isType($get, HomeSectionType::ServiceHighlights)
                        ? ServiceHighlightResource::getUrl()
                        : BannerResource::getUrl()),
            ])
            ->visible(fn (Get $get): bool => self::sectionType($get)?->hasHeading() === false);
    }

    private static function headingSection(): Section
    {
        return Section::make('Heading')
            ->description('The text customers see on top of this block.')
            ->icon('heroicon-o-pencil-square')
            ->visible(fn (Get $get): bool => self::sectionType($get)?->hasHeading() === true)
            ->schema([
                TranslatableTabs::make('section', fn (string $suffix, bool $isEnglish): array => [
                    TextInput::make("title{$suffix}")
                        ->label($isEnglish ? 'Heading' : 'শিরোনাম')
                        ->placeholder($isEnglish ? 'e.g. New and Noteworthy' : 'যেমন: নতুন ও আলোচিত')
                        ->helperText(TranslatableTabs::fallbackHint($isEnglish))
                        ->maxLength(150),

                    TextInput::make("subtitle{$suffix}")
                        ->label($isEnglish ? 'Small text under the heading' : 'শিরোনামের নিচের ছোট লেখা')
                        ->placeholder($isEnglish ? 'e.g. Explore the latest finds from innovative tech' : 'যেমন: নতুন সব গ্যাজেট দেখুন')
                        ->helperText(TranslatableTabs::fallbackHint($isEnglish) ?? 'Optional.')
                        ->maxLength(255),

                    TextInput::make("settings.cta_label{$suffix}")
                        ->label($isEnglish ? 'Button text' : 'বাটনের লেখা')
                        ->default($isEnglish ? 'Shop now' : null)
                        ->placeholder($isEnglish ? 'Shop now' : 'এখনই কিনুন')
                        ->helperText(TranslatableTabs::fallbackHint($isEnglish))
                        ->visible(fn (Get $get): bool => self::isType($get, HomeSectionType::Showcase)),
                ]),
            ]);
    }

    private static function productsSection(): Section
    {
        return Section::make('Products')
            ->description('Choose which products appear in this block.')
            ->icon('heroicon-o-shopping-bag')
            ->visible(fn (Get $get): bool => self::sectionType($get)?->listsProducts() === true)
            ->schema([
                Radio::make('settings.source')
                    ->label('Where do the products come from?')
                    ->options(ProductCollectionSource::class)
                    ->default(ProductCollectionSource::Latest->value)
                    ->live()
                    ->columns(['default' => 1, 'md' => 2])
                    ->gridDirection(GridDirection::Row)
                    ->extraAttributes(['class' => 'gx-choice-cards']),

                Select::make('settings.category_id')
                    ->label('Category')
                    ->options(fn (): array => Category::query()->active()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required()
                    ->visible(fn (Get $get): bool => self::isSource($get, ProductCollectionSource::Category)),

                Repeater::make('items')
                    ->relationship('items')
                    ->label('Your products')
                    ->helperText('Drag the rows to change the order customers see.')
                    ->table([
                        TableColumn::make('Product'),
                    ])
                    ->schema([
                        ProductPicker::make('itemable_id')
                            ->hiddenLabel()
                            ->placeholder('Search a product…')
                            ->required()
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                    ])
                    ->orderColumn('sort_order')
                    ->defaultItems(0)
                    ->addActionLabel('Add a product')
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => [
                        ...$data,
                        'itemable_type' => (new Product)->getMorphClass(),
                    ])
                    ->visible(fn (Get $get): bool => self::isSource($get, ProductCollectionSource::Manual)),

                Grid::make(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('settings.limit')
                            ->label('How many products to show')
                            ->numeric()
                            ->minValue(1)
                            ->default(12),

                        TextInput::make('settings.initial_count')
                            ->label('Cards shown before "Load more"')
                            ->numeric()
                            ->minValue(1)
                            ->default(5)
                            ->visible(fn (Get $get): bool => self::isType($get, HomeSectionType::ProductGrid)),

                        self::linkField()
                            ->visible(fn (Get $get): bool => self::isType($get, HomeSectionType::ProductCollection, HomeSectionType::Showcase)),
                    ]),
            ]);
    }

    /**
     * Categories, brands and the flash sale fill themselves; only the count (and link) can change.
     */
    private static function displaySection(): Section
    {
        return Section::make('Display')
            ->description('This block fills itself automatically.')
            ->icon('heroicon-o-adjustments-horizontal')
            ->visible(fn (Get $get): bool => self::isType($get, HomeSectionType::FeaturedCategories, HomeSectionType::FeaturedBrands, HomeSectionType::FlashSale))
            ->columns(['default' => 1, 'md' => 2])
            ->schema([
                TextInput::make('settings.limit')
                    ->label('How many to show')
                    ->numeric()
                    ->minValue(1)
                    ->default(12),

                self::linkField()
                    ->visible(fn (Get $get): bool => self::isType($get, HomeSectionType::FlashSale)),
            ]);
    }

    private static function lookSection(): Section
    {
        return Section::make('Look')
            ->description('Colour of the panel and the optional pictures under its heading.')
            ->icon('heroicon-o-swatch')
            ->visible(fn (Get $get): bool => self::isType($get, HomeSectionType::Showcase))
            ->schema([
                Radio::make('settings.theme')
                    ->label('Panel colour')
                    ->options([
                        'aurora' => 'Red',
                        'midnight' => 'Black',
                    ])
                    ->descriptions([
                        'aurora' => 'Deep red gradient, prices in the heading turn yellow.',
                        'midnight' => 'Black with an orange glow, prices in the heading turn red.',
                    ])
                    ->default('aurora')
                    ->columns(['default' => 1, 'md' => 2])
                    ->gridDirection(GridDirection::Row)
                    ->extraAttributes(['class' => 'gx-choice-cards gx-theme-cards']),

                FileUpload::make('images_upload')
                    ->label('Pictures (up to 2)')
                    ->helperText('Shown tilted under the heading. Square product photos on a plain background look best. Leave empty for a text-only panel.')
                    ->disk('public')
                    ->directory('showcase')
                    ->multiple()
                    ->maxFiles(2)
                    ->image()
                    ->panelLayout('grid')
                    ->dehydrated(false),
            ]);
    }

    private static function visibilitySection(): Section
    {
        return Section::make('Visibility')
            ->icon('heroicon-o-eye')
            ->schema([
                Toggle::make('is_active')
                    ->label('Show on the homepage')
                    ->helperText('Turn off to hide the block without deleting it.')
                    ->default(true),

                TextInput::make('sort_order')
                    ->label('Position')
                    ->helperText('Smaller numbers appear higher on the page. You can also use the ↑ ↓ buttons on the Home Sections list.')
                    ->numeric()
                    ->minValue(0)
                    ->default(fn (): int => (int) HomeSection::query()->max('sort_order') + 1),
            ]);
    }

    private static function linkField(): TextInput
    {
        return TextInput::make('settings.view_all_url')
            ->label(fn (Get $get): string => self::isType($get, HomeSectionType::Showcase) ? 'Button link' : '"View all" link')
            ->placeholder('e.g. /category/powerbanks')
            ->helperText('Leave empty to use the default page.');
    }

    /**
     * Enum-backed fields hold the enum instance once filled from a record but a
     * plain string while the admin is picking, so both shapes are accepted.
     */
    private static function sectionType(Get $get): ?HomeSectionType
    {
        $type = $get('type');

        return $type instanceof HomeSectionType ? $type : HomeSectionType::tryFrom((string) $type);
    }

    private static function isType(Get $get, HomeSectionType ...$types): bool
    {
        return in_array(self::sectionType($get), $types, true);
    }

    private static function isSource(Get $get, ProductCollectionSource $source): bool
    {
        $state = $get('settings.source');

        return ($state instanceof ProductCollectionSource ? $state : ProductCollectionSource::tryFrom((string) $state)) === $source;
    }
}
