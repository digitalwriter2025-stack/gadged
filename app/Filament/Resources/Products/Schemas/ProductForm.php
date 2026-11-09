<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\StockStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Filament\TranslatableTabs;
use App\Support\Money;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * Product editor: details, images, price & stock in the main column;
 * status, organization and offer in the sidebar.
 */
class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    self::detailsSection(),
                    self::imagesSection(),
                    self::priceAndStockSection(),
                    self::comboSection(),
                    self::faqSection(),
                ])
                    ->columnSpan(['lg' => 2])
                    ->extraAttributes(['class' => 'gx-product-form']),

                Group::make([
                    self::statusSection(),
                    self::organizationSection(),
                    self::offerSection(),
                ])
                    ->columnSpan(['lg' => 1])
                    ->extraAttributes(['class' => 'gx-product-form']),
            ]);
    }

    private static function detailsSection(): Section
    {
        return Section::make('Product details')
            ->schema([
                TranslatableTabs::make('details', fn (string $suffix, bool $isEnglish): array => [
                    TextInput::make("name{$suffix}")
                        ->label($isEnglish ? 'Product title' : 'পণ্যের নাম (Product title)')
                        ->placeholder($isEnglish ? 'e.g. Walnut 50W Bluetooth Bookshelf Speaker' : 'যেমন: ওয়ালনাট ৫০W ব্লুটুথ বুকশেলফ স্পিকার')
                        ->helperText(TranslatableTabs::fallbackHint($isEnglish))
                        ->required($isEnglish)
                        ->maxLength(255)
                        ->when($isEnglish, fn (TextInput $field): TextInput => $field
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            })),

                    Textarea::make("short_description{$suffix}")
                        ->label($isEnglish ? 'Short description' : 'সংক্ষিপ্ত বিবরণ (Short description)')
                        ->placeholder($isEnglish ? 'A one or two line summary of the key highlights' : 'মূল বৈশিষ্ট্যগুলো এক-দুই লাইনে')
                        ->rows(3),

                    RichEditor::make("description{$suffix}")
                        ->label($isEnglish ? 'Full description' : 'বিস্তারিত বিবরণ (Full description)')
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('products'),
                ]),

                TextInput::make('slug')
                    ->label('URL slug')
                    ->prefix('/product/')
                    ->required()
                    ->maxLength(255)
                    ->unique(Product::class, 'slug', ignoreRecord: true),
            ]);
    }

    private static function imagesSection(): Section
    {
        return Section::make('Images')
            ->schema([
                Grid::make(['default' => 1, 'md' => 3])->schema([
                    FileUpload::make('main_image')
                        ->label('Cover image')
                        ->disk('public')
                        ->directory('products')
                        ->image()
                        ->imageEditor()
                        ->dehydrated(false)
                        ->extraAttributes(['class' => 'gx-upload'], merge: true),

                    FileUpload::make('gallery_images')
                        ->label('Gallery images')
                        ->disk('public')
                        ->directory('products')
                        ->image()
                        ->multiple()
                        ->panelLayout('grid')
                        ->dehydrated(false)
                        ->extraAttributes(['class' => 'gx-upload'], merge: true)
                        ->columnSpan(['md' => 2]),
                ]),
            ]);
    }

    private static function priceAndStockSection(): Section
    {
        $hasVariants = fn (?Product $record): bool => $record?->type === ProductType::Variable;

        return Section::make('Price & stock')
            ->schema([
                Grid::make(['default' => 1, 'sm' => 3])->schema([
                    TextInput::make('selling_price')
                        ->label('Selling price')
                        ->prefix('৳')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->live(onBlur: true)
                        ->disabled($hasVariants)
                        ->dehydrated(false),

                    TextInput::make('compare_at_price')
                        ->label('Regular price')
                        ->placeholder('Only if discounted')
                        ->prefix('৳')
                        ->numeric()
                        ->gt('selling_price')
                        ->live(onBlur: true)
                        ->hint(fn (Get $get): ?string => self::discountHint($get('selling_price'), $get('compare_at_price')))
                        ->hintColor('success')
                        ->disabled($hasVariants)
                        ->dehydrated(false),

                    TextInput::make('stock_quantity')
                        ->label('Stock quantity')
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->default(10)
                        ->required()
                        ->disabled($hasVariants)
                        ->dehydrated(false),

                    TextInput::make('sku')
                        ->label('SKU')
                        ->required()
                        ->maxLength(255)
                        ->unique(Product::class, 'sku', ignoreRecord: true)
                        ->default(fn (): string => 'SKU-'.strtoupper(Str::random(8))),

                    Select::make('stock_status')
                        ->label('Stock status')
                        ->options(StockStatus::class)
                        ->default(StockStatus::InStock)
                        ->selectablePlaceholder(false)
                        ->required(),
                ]),
            ])
            ->belowContent(fn (?Product $record): ?string => $hasVariants($record)
                ? 'This product has variants, so price and stock are set on each variant.'
                : null);
    }

    private static function comboSection(): Section
    {
        $isCombo = fn (Get $get): bool => (bool) $get('is_combo');

        return Section::make('Combo')
            ->description('Sell two or more products together at one price.')
            ->icon('heroicon-o-gift')
            ->afterHeader([
                Text::make('Combo')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible($isCombo),

                Toggle::make('is_combo')
                    ->hiddenLabel()
                    ->live()
                    ->default(false),
            ])
            ->extraAttributes(['class' => 'gx-combo'])
            ->schema([
                Text::make(new HtmlString(
                    '<span class="gx-combo-empty-title">This product is not a combo</span>'
                    .'<span class="gx-combo-empty-text">Turn on the switch to pick the products in the bundle. The combo is then listed on the Combo Deals page, and its product page shows what is in the box.</span>'
                ))
                    ->extraAttributes(['class' => 'gx-combo-empty'])
                    ->hidden($isCombo),

                Repeater::make('comboItems')
                    ->label("What's in the combo")
                    ->relationship()
                    ->orderColumn('sort_order')
                    ->table([
                        TableColumn::make('Product')->markAsRequired(),
                        TableColumn::make('Qty')->markAsRequired()->width('7rem'),
                    ])
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(fn (): array => self::comboProductOptions())
                            ->placeholder('Search a product…')
                            ->searchable()
                            ->required()
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->live(),

                        TextInput::make('quantity')
                            ->label('Qty')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(99)
                            ->default(1)
                            ->required()
                            ->live(onBlur: true),
                    ])
                    ->minItems(2)
                    ->defaultItems(2)
                    ->addActionLabel('Add another product')
                    ->visible($isCombo),

                Text::make(fn (Get $get): HtmlString => self::comboValueSummary($get('comboItems'), $get('selling_price')))
                    ->visible(fn (Get $get): bool => $isCombo($get) && self::comboSeparateTotal($get('comboItems')) !== null),

                Actions::make([
                    Action::make('useComboSeparateTotal')
                        ->label(fn (Get $get): string => 'Show '.Money::format(self::comboSeparateTotal($get('comboItems'))).' as the regular (crossed-out) price')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('gray')
                        ->size('sm')
                        ->action(fn (Get $get, Set $set) => $set('compare_at_price', self::comboSeparateTotal($get('comboItems')))),
                ])
                    ->visible(function (Get $get) use ($isCombo): bool {
                        $separateTotal = self::comboSeparateTotal($get('comboItems'));

                        return $isCombo($get)
                            && $separateTotal !== null
                            && ! (is_numeric($get('compare_at_price')) && Money::compare(Money::of($get('compare_at_price')), $separateTotal) === 0);
                    }),
            ]);
    }

    /**
     * Products that can go inside a combo, labelled with their selling price.
     *
     * @return array<int, string>
     */
    private static function comboProductOptions(): array
    {
        return Product::query()
            ->where('is_combo', false)
            ->orderBy('name')
            ->get(['id', 'name', 'min_price'])
            ->mapWithKeys(fn (Product $product): array => [
                $product->id => "{$product->name} — ".Money::format($product->min_price),
            ])
            ->all();
    }

    /**
     * What the picked items cost bought one by one, or null until a product is picked.
     *
     * @param  array<array-key, array{product_id?: int|string|null, quantity?: int|string|null}>|null  $items
     */
    private static function comboSeparateTotal(?array $items): ?string
    {
        $quantities = collect($items ?? [])
            ->filter(fn (array $item): bool => filled($item['product_id'] ?? null))
            ->mapWithKeys(fn (array $item): array => [(int) $item['product_id'] => max(1, (int) ($item['quantity'] ?? 1))]);

        if ($quantities->isEmpty()) {
            return null;
        }

        $prices = Product::query()->whereKey($quantities->keys())->pluck('min_price', 'id');

        return Money::add(...$quantities
            ->map(fn (int $quantity, int $productId): string => Money::mul($prices[$productId] ?? '0', $quantity))
            ->values()
            ->all());
    }

    /**
     * "Bought separately → combo price → customer saves" strip under the combo items.
     *
     * @param  array<array-key, array{product_id?: int|string|null, quantity?: int|string|null}>|null  $items
     */
    private static function comboValueSummary(?array $items, mixed $sellingPrice): HtmlString
    {
        $separateTotal = self::comboSeparateTotal($items) ?? '0';
        $tile = fn (string $label, string $value, string $modifier = ''): string => '<div class="gx-combo-stat '.$modifier.'">'
            .'<span class="gx-combo-stat-label">'.e($label).'</span>'
            .'<span class="gx-combo-stat-value">'.e($value).'</span>'
            .'</div>';

        if (! is_numeric($sellingPrice) || Money::compare(Money::of($sellingPrice), '0') <= 0) {
            return new HtmlString('<div class="gx-combo-summary">'
                .$tile('Bought separately', Money::format($separateTotal))
                .$tile('Combo price', 'Not set', 'gx-combo-stat-muted')
                .'<p class="gx-combo-note">Enter the combo price in "Selling price" above to see how much the customer saves.</p>'
                .'</div>');
        }

        $comboPrice = Money::of($sellingPrice);
        $saving = Money::sub($separateTotal, $comboPrice);
        $savesMoney = Money::compare($saving, '0') === 1;

        return new HtmlString('<div class="gx-combo-summary">'
            .$tile('Bought separately', Money::format($separateTotal))
            .$tile('Combo price', Money::format($comboPrice))
            .($savesMoney
                ? $tile('Customer saves', Money::format($saving).' ('.Money::percentage($saving, $separateTotal).'%)', 'gx-combo-stat-good')
                : $tile('Customer saves', 'Nothing', 'gx-combo-stat-bad'))
            .($savesMoney ? '' : '<p class="gx-combo-note gx-combo-note-bad">The combo costs the same or more than buying the items one by one. Lower the selling price to make it a deal.</p>')
            .'</div>');
    }

    private static function faqSection(): Section
    {
        return Section::make('FAQs')
            ->collapsed(fn (?Product $record): bool => blank($record?->faqs))
            ->schema([
                TranslatableTabs::make('faqs', fn (string $suffix, bool $isEnglish): array => [
                    Repeater::make("faqs{$suffix}")
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('question')
                                ->label($isEnglish ? 'Question' : 'প্রশ্ন (Question)')
                                ->required(),

                            Textarea::make('answer')
                                ->label($isEnglish ? 'Answer' : 'উত্তর (Answer)')
                                ->rows(2)
                                ->required(),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                        ->collapsible()
                        ->defaultItems(0)
                        ->addActionLabel($isEnglish ? 'Add question' : 'বাংলা প্রশ্ন যোগ করুন')
                        ->helperText($isEnglish ? null : 'Leave empty to show the English FAQs.'),
                ]),
            ]);
    }

    private static function statusSection(): Section
    {
        return Section::make('Status')
            ->schema([
                Select::make('status')
                    ->hiddenLabel()
                    ->options(ProductStatus::class)
                    ->default(ProductStatus::Active)
                    ->selectablePlaceholder(false)
                    ->required(),

                Toggle::make('is_featured')
                    ->label('Featured')
                    ->default(false),

                Toggle::make('is_latest')
                    ->label('New arrival')
                    ->default(true),

                Toggle::make('is_best_seller')
                    ->label('Best seller')
                    ->default(false),

                Toggle::make('is_trending')
                    ->label('Trending')
                    ->default(false),
            ]);
    }

    private static function organizationSection(): Section
    {
        return Section::make('Organization')
            ->schema([
                Select::make('category_id')
                    ->label('Category')
                    ->options(fn (): array => Category::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required(),

                Select::make('brand_id')
                    ->label('Brand')
                    ->options(fn (): array => Brand::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),

                TextInput::make('model')
                    ->label('Model number')
                    ->placeholder('e.g. BS-50W')
                    ->maxLength(255),
            ]);
    }

    private static function offerSection(): Section
    {
        return Section::make('Offer & warranty')
            ->schema([
                TranslatableTabs::make('offer', fn (string $suffix, bool $isEnglish): array => [
                    TextInput::make("offer_text{$suffix}")
                        ->label($isEnglish ? 'Promotional highlight' : 'অফার (Promotional highlight)')
                        ->placeholder($isEnglish ? 'e.g. Special cash discount available' : 'যেমন: বিশেষ ক্যাশ ডিসকাউন্ট')
                        ->maxLength(255),

                    TextInput::make("warranty{$suffix}")
                        ->label($isEnglish ? 'Warranty' : 'ওয়ারেন্টি (Warranty)')
                        ->placeholder($isEnglish ? 'e.g. 1 year official brand warranty' : 'যেমন: ১ বছরের অফিসিয়াল ওয়ারেন্টি')
                        ->maxLength(100),
                ]),
            ]);
    }

    /**
     * "Save ৳500 (25% off)" once a valid regular price is entered.
     */
    private static function discountHint(mixed $sellingPrice, mixed $regularPrice): ?string
    {
        if (! is_numeric($sellingPrice) || ! is_numeric($regularPrice)) {
            return null;
        }

        $sellingPrice = Money::of($sellingPrice);
        $regularPrice = Money::of($regularPrice);

        if (Money::compare($regularPrice, $sellingPrice) <= 0) {
            return null;
        }

        $saving = Money::sub($regularPrice, $sellingPrice);

        return 'Save '.Money::format($saving).' ('.Money::percentage($saving, $regularPrice).'% off)';
    }
}
