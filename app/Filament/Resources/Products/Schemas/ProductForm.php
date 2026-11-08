<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Product Information')
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Title & Identification')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Product Title')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                                                if ($operation === 'create' && filled($state)) {
                                                    $set('slug', Str::slug($state));
                                                }
                                            }),

                                        Group::make([
                                            TextInput::make('slug')
                                                ->label('URL Slug')
                                                ->required()
                                                ->unique(Product::class, 'slug', ignoreRecord: true),

                                            TextInput::make('sku')
                                                ->label('Product SKU / Code')
                                                ->required()
                                                ->unique(Product::class, 'sku', ignoreRecord: true)
                                                ->default(fn (): string => 'SKU-'.strtoupper(Str::random(8))),

                                            TextInput::make('model')
                                                ->label('Model Number'),
                                        ])->columns(3),

                                        Group::make([
                                            Select::make('category_id')
                                                ->label('Primary Category')
                                                ->options(fn (): array => Category::query()->pluck('name', 'id')->all())
                                                ->searchable()
                                                ->required(),

                                            Select::make('brand_id')
                                                ->label('Brand')
                                                ->options(fn (): array => Brand::query()->pluck('name', 'id')->all())
                                                ->searchable(),
                                        ])->columns(2),
                                    ]),
                            ]),

                        Tab::make('Pricing & Inventory')
                            ->icon('heroicon-o-currency-bangladeshi')
                            ->schema([
                                Section::make('Pricing')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('min_price')
                                                ->label('Selling Price (৳)')
                                                ->numeric()
                                                ->required()
                                                ->prefix('৳'),

                                            TextInput::make('regular_price')
                                                ->label('Regular / Compare-at Price (৳)')
                                                ->numeric()
                                                ->prefix('৳')
                                                ->helperText('Optional strike-through original price for showing discounts.'),

                                            TextInput::make('total_stock')
                                                ->label('Inventory Stock')
                                                ->numeric()
                                                ->default(10),

                                            Select::make('stock_status')
                                                ->label('Stock Status')
                                                ->options([
                                                    'in_stock' => 'In Stock',
                                                    'out_of_stock' => 'Out of Stock',
                                                    'pre_order' => 'Pre Order',
                                                    'upcoming' => 'Upcoming',
                                                ])
                                                ->default('in_stock')
                                                ->required(),
                                        ])->columns(4),
                                    ]),

                                Section::make('Publication & Store Visibility')
                                    ->schema([
                                        Group::make([
                                            Select::make('status')
                                                ->options([
                                                    'published' => 'Published & Live',
                                                    'draft' => 'Draft (Hidden)',
                                                ])
                                                ->default('published')
                                                ->required(),

                                            Toggle::make('is_featured')
                                                ->label('Featured Product (Homepage)')
                                                ->default(false),

                                            Toggle::make('is_latest')
                                                ->label('Mark as Latest Arrival')
                                                ->default(true),

                                            Toggle::make('is_best_seller')
                                                ->label('Mark as Best Seller')
                                                ->default(false),
                                        ])->columns(4),
                                    ]),
                            ]),

                        Tab::make('Images & Media')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Product Photography')
                                    ->schema([
                                        FileUpload::make('main_image')
                                            ->label('Cover / Main Image')
                                            ->disk('public')
                                            ->directory('products')
                                            ->image()
                                            ->imageEditor(),

                                        FileUpload::make('gallery_images')
                                            ->label('Product Gallery Images (Multiple)')
                                            ->disk('public')
                                            ->directory('products')
                                            ->image()
                                            ->multiple(),
                                    ]),
                            ]),

                        Tab::make('Descriptions & FAQs')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Descriptions')
                                    ->schema([
                                        TextInput::make('offer_text')
                                            ->label('Promotional Highlight Banner')
                                            ->placeholder('e.g. Special Cash Discount Available | Call for best price'),

                                        TextInput::make('warranty')
                                            ->label('Warranty Information')
                                            ->placeholder('e.g. 2 Years Official Brand Warranty (1 Year Parts)'),

                                        Textarea::make('short_description')
                                            ->label('Short Description / Key Highlights')
                                            ->rows(3),

                                        RichEditor::make('description')
                                            ->label('Detailed Full Product Description')
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('products'),
                                    ]),

                                Section::make('Frequently Asked Questions (FAQs)')
                                    ->schema([
                                        Repeater::make('faqs')
                                            ->label('Product FAQs')
                                            ->schema([
                                                TextInput::make('question')
                                                    ->label('Question')
                                                    ->required(),

                                                Textarea::make('answer')
                                                    ->label('Answer')
                                                    ->required(),
                                            ])
                                            ->columns(2)
                                            ->collapsible()
                                            ->defaultItems(0),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
