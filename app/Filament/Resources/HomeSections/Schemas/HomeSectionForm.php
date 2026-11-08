<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomeSections\Schemas;

use App\Enums\HomeSectionType;
use App\Enums\ProductCollectionSource;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class HomeSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Section Overview')
                    ->description('Set section type, title, subtitle and visibility.')
                    ->schema([
                        Group::make([
                            Select::make('type')
                                ->label('Section Type')
                                ->options(HomeSectionType::class)
                                ->required()
                                ->live(),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0)
                                ->label('Display Order')
                                ->helperText('Sections are displayed in ascending order.'),
                        ])->columns(2),

                        Group::make([
                            TextInput::make('title')
                                ->label('Section Heading')
                                ->placeholder('e.g. Featured Categories, Flash Sale, Mobile Phones'),

                            TextInput::make('subtitle')
                                ->label('Section Subtitle')
                                ->placeholder('e.g. Explore our wide range of authentic tech products'),
                        ])->columns(2),

                        Toggle::make('is_active')
                            ->label('Visible on Storefront')
                            ->default(true),
                    ]),

                Section::make('Section Content Configuration')
                    ->description('Specific settings based on the chosen section type.')
                    ->schema([
                        // Product Collection settings
                        Group::make([
                            Select::make('settings.source')
                                ->label('Product Source')
                                ->options(ProductCollectionSource::class)
                                ->default('latest')
                                ->live()
                                ->visible(fn (Get $get): bool => in_array($get('type'), [
                                    HomeSectionType::ProductCollection->value,
                                    HomeSectionType::Showcase->value,
                                    HomeSectionType::ProductGrid->value,
                                ], true)),

                            Select::make('settings.category_id')
                                ->label('Target Category')
                                ->options(fn (): array => Category::query()->active()->pluck('name', 'id')->all())
                                ->searchable()
                                ->visible(fn (Get $get): bool => $get('settings.source') === ProductCollectionSource::Category->value),

                            TextInput::make('settings.limit')
                                ->numeric()
                                ->default(12)
                                ->label('Products / Items Limit')
                                ->visible(fn (Get $get): bool => in_array($get('type'), [
                                    HomeSectionType::ProductCollection->value,
                                    HomeSectionType::FeaturedCategories->value,
                                    HomeSectionType::FeaturedBrands->value,
                                    HomeSectionType::FlashSale->value,
                                    HomeSectionType::Showcase->value,
                                    HomeSectionType::ProductGrid->value,
                                ], true)),

                            TextInput::make('settings.view_all_url')
                                ->label('Custom "View All" URL')
                                ->placeholder('e.g. /category/smartphone or /offers')
                                ->visible(fn (Get $get): bool => in_array($get('type'), [
                                    HomeSectionType::ProductCollection->value,
                                    HomeSectionType::FlashSale->value,
                                    HomeSectionType::Showcase->value,
                                ], true)),
                        ])->columns(2),

                        // Showcase specific settings
                        Group::make([
                            Select::make('settings.theme')
                                ->label('Showcase Gradient Theme')
                                ->options([
                                    'aurora' => 'Aurora (Teal / Blue Glow)',
                                    'midnight' => 'Midnight (Deep Dark / Purple Accent)',
                                ])
                                ->default('aurora')
                                ->visible(fn (Get $get): bool => $get('type') === HomeSectionType::Showcase->value),

                            TextInput::make('settings.cta_label')
                                ->label('CTA Button Label')
                                ->default('Shop now')
                                ->visible(fn (Get $get): bool => $get('type') === HomeSectionType::Showcase->value),

                            FileUpload::make('images')
                                ->label('Showcase Collage Images (Max 2)')
                                ->disk('public')
                                ->directory('showcase')
                                ->multiple()
                                ->maxFiles(2)
                                ->image()
                                ->visible(fn (Get $get): bool => $get('type') === HomeSectionType::Showcase->value),
                        ])->columns(2),

                        // Product Grid initial display count
                        TextInput::make('settings.initial_count')
                            ->numeric()
                            ->default(5)
                            ->label('Initial Card Count (Product Grid)')
                            ->visible(fn (Get $get): bool => $get('type') === HomeSectionType::ProductGrid->value),

                        // Hand-picked items repeater for manual source
                        Repeater::make('items')
                            ->relationship('items')
                            ->label('Hand-Picked Products')
                            ->schema([
                                Select::make('itemable_id')
                                    ->label('Select Product')
                                    ->options(fn (): array => Product::query()->published()->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->required(),

                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Sort Order'),
                            ])
                            ->columns(2)
                            ->visible(fn (Get $get): bool => $get('settings.source') === ProductCollectionSource::Manual->value),
                    ]),
            ]);
    }
}
