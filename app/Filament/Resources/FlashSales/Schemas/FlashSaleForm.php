<?php

declare(strict_types=1);

namespace App\Filament\Resources\FlashSales\Schemas;

use App\Enums\FlashSaleDiscountType;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Support\Filament\TranslatableTabs;
use App\Support\Money;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * Flash sale editor: name and deal products in the main column;
 * schedule and visibility in the sidebar.
 */
class FlashSaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    self::detailsSection(),
                    self::productsSection(),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    self::scheduleSection(),
                    self::visibilitySection(),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    private static function detailsSection(): Section
    {
        return Section::make('Sale details')
            ->schema([
                TranslatableTabs::make('flash-sale', fn (string $suffix, bool $isEnglish): array => [
                    TextInput::make("title{$suffix}")
                        ->label($isEnglish ? 'Sale name' : 'সেলের নাম (Sale name)')
                        ->placeholder($isEnglish ? 'e.g. Eid Flash Sale' : 'যেমন: ঈদ ফ্ল্যাশ সেল')
                        ->helperText(TranslatableTabs::fallbackHint($isEnglish))
                        ->required($isEnglish)
                        ->maxLength(150)
                        ->when($isEnglish, fn (TextInput $field): TextInput => $field
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            })),

                    Textarea::make("description{$suffix}")
                        ->label($isEnglish ? 'Short note' : 'সংক্ষিপ্ত বিবরণ (Short note)')
                        ->placeholder($isEnglish ? 'Optional' : 'ঐচ্ছিক')
                        ->maxLength(500)
                        ->rows(2),
                ]),

                TextInput::make('slug')
                    ->label('URL slug')
                    ->helperText('Filled in automatically from the name.')
                    ->required()
                    ->maxLength(255)
                    ->unique(FlashSale::class, 'slug', ignoreRecord: true),
            ]);
    }

    private static function productsSection(): Section
    {
        return Section::make('Deal products')
            ->description('Pick a product and its discount. The deal price appears on top of each row.')
            ->schema([
                Repeater::make('items')
                    ->hiddenLabel()
                    ->relationship()
                    ->orderColumn('sort_order')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(fn (): array => Product::query()->published()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->required()
                            ->live()
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->columnSpanFull(),

                        Select::make('discount_type')
                            ->label('Discount type')
                            ->options(FlashSaleDiscountType::class)
                            ->default(FlashSaleDiscountType::Percent)
                            ->selectablePlaceholder(false)
                            ->required()
                            ->live(),

                        TextInput::make('discount_value')
                            ->label(fn (Get $get): string => self::discountType($get('discount_type')) === FlashSaleDiscountType::FixedPrice ? 'Sale price' : 'Discount')
                            ->prefix(fn (Get $get): ?string => self::discountType($get('discount_type')) === FlashSaleDiscountType::Percent ? null : '৳')
                            ->suffix(fn (Get $get): ?string => self::discountType($get('discount_type')) === FlashSaleDiscountType::Percent ? '%' : null)
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn (Get $get): ?int => self::discountType($get('discount_type')) === FlashSaleDiscountType::Percent ? 100 : null)
                            ->required()
                            ->live(onBlur: true),

                        TextInput::make('quantity_limit')
                            ->label('Stock limit')
                            ->placeholder('Unlimited')
                            ->numeric()
                            ->integer()
                            ->minValue(1),
                    ])
                    ->columns(['default' => 1, 'sm' => 3])
                    ->itemLabel(fn (array $state): ?string => self::dealLabel($state))
                    ->collapsible()
                    ->minItems(1)
                    ->defaultItems(1)
                    ->addActionLabel('Add another product'),
            ]);
    }

    private static function scheduleSection(): Section
    {
        return Section::make('Schedule')
            ->description('The homepage countdown runs until the end time.')
            ->schema([
                DateTimePicker::make('starts_at')
                    ->label('Starts')
                    ->native(false)
                    ->seconds(false)
                    ->displayFormat('d M Y, h:i A')
                    ->required()
                    ->default(now()),

                DateTimePicker::make('ends_at')
                    ->label('Ends')
                    ->native(false)
                    ->seconds(false)
                    ->displayFormat('d M Y, h:i A')
                    ->after('starts_at')
                    ->required()
                    ->default(now()->addDays(3)),
            ]);
    }

    private static function visibilitySection(): Section
    {
        return Section::make('Visibility')
            ->schema([
                Toggle::make('is_active')
                    ->label('Show on storefront')
                    ->helperText('Turn off to pause the sale without deleting it.')
                    ->default(true),

                TextInput::make('sort_order')
                    ->label('Priority')
                    ->helperText('When two sales overlap, the lower number is shown on the homepage.')
                    ->numeric()
                    ->integer()
                    ->default(0),
            ]);
    }

    /**
     * "Product name · ৳3,600 → ৳3,240" once a product and discount are chosen.
     *
     * @param  array<string, mixed>  $state
     */
    private static function dealLabel(array $state): ?string
    {
        if (blank($state['product_id'] ?? null)) {
            return null;
        }

        $product = Product::query()->find($state['product_id'], ['id', 'name', 'regular_price']);

        if ($product === null || ! is_numeric($state['discount_value'] ?? null)) {
            return $product?->name;
        }

        $dealPrice = (new FlashSaleProduct([
            'discount_type' => self::discountType($state['discount_type'] ?? null),
            'discount_value' => $state['discount_value'],
        ]))->priceFrom($product->regular_price);

        return $product->name.' · '.Money::format($product->regular_price).' → '.Money::format($dealPrice);
    }

    private static function discountType(mixed $state): FlashSaleDiscountType
    {
        return $state instanceof FlashSaleDiscountType
            ? $state
            : (FlashSaleDiscountType::tryFrom((string) $state) ?? FlashSaleDiscountType::Percent);
    }
}
