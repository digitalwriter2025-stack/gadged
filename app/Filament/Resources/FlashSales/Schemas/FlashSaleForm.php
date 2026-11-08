<?php

declare(strict_types=1);

namespace App\Filament\Resources\FlashSales\Schemas;

use App\Models\FlashSale;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class FlashSaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Flash Sale Campaign')
                    ->schema([
                        Group::make([
                            TextInput::make('title')
                                ->label('Flash Sale Name')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                                    if ($operation === 'create' && filled($state)) {
                                        $set('slug', Str::slug($state));
                                    }
                                }),

                            TextInput::make('slug')
                                ->label('URL Slug')
                                ->required()
                                ->unique(FlashSale::class, 'slug', ignoreRecord: true),
                        ])->columns(2),

                        Group::make([
                            DateTimePicker::make('starts_at')
                                ->label('Starts At (Countdown Start)')
                                ->required()
                                ->default(now()),

                            DateTimePicker::make('ends_at')
                                ->label('Ends At (Countdown End)')
                                ->required()
                                ->default(now()->addDays(3)),

                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                        ])->columns(4),

                        Textarea::make('description')
                            ->label('Campaign Description')
                            ->rows(2),
                    ]),

                Section::make('Discounted Flash Deals Products')
                    ->description('Select the products offered during this flash sale with discount rates.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->label('Flash Sale Products')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(fn (): array => Product::query()->published()->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->required(),

                                Select::make('discount_type')
                                    ->label('Discount Type')
                                    ->options([
                                        'percent' => 'Percentage (%)',
                                        'fixed' => 'Fixed Amount (৳)',
                                    ])
                                    ->default('percent')
                                    ->required(),

                                TextInput::make('discount_value')
                                    ->label('Discount Value')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('quantity_limit')
                                    ->label('Stock / Quantity Limit')
                                    ->numeric()
                                    ->placeholder('Unlimited if empty'),
                            ])
                            ->columns(4)
                            ->collapsible()
                            ->defaultItems(1),
                    ]),
            ]);
    }
}
