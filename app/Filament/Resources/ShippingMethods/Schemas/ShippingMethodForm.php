<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingMethods\Schemas;

use App\Support\Filament\TranslatableTabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShippingMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Shipping Method')
                    ->description('Shown to the customer at checkout. The selected method\'s charge is added to the order total.')
                    ->columnSpanFull()
                    ->schema([
                        TranslatableTabs::make('shipping', fn (string $suffix, bool $isEnglish): array => [
                            Group::make([
                                TextInput::make("name{$suffix}")
                                    ->label($isEnglish ? 'Method Name' : 'ডেলিভারির নাম (Method Name)')
                                    ->required($isEnglish)
                                    ->maxLength(100)
                                    ->placeholder($isEnglish ? 'e.g. Inside Dhaka, Outside Dhaka, Express (Dhaka)' : 'যেমন: ঢাকার ভিতরে, ঢাকার বাইরে'),

                                TextInput::make("delivery_estimate{$suffix}")
                                    ->label($isEnglish ? 'Delivery Estimate' : 'ডেলিভারির সময় (Delivery Estimate)')
                                    ->maxLength(100)
                                    ->placeholder($isEnglish ? 'e.g. 1-2 working days' : 'যেমন: ১-২ কর্মদিবস'),
                            ])->columns(2),

                            TextInput::make("description{$suffix}")
                                ->label($isEnglish ? 'Short Description' : 'সংক্ষিপ্ত বিবরণ (Short Description)')
                                ->maxLength(255)
                                ->placeholder($isEnglish ? 'e.g. Delivered by our courier partner within the city' : 'যেমন: শহরের ভিতরে আমাদের কুরিয়ার পার্টনার পৌঁছে দেবে'),
                        ]),

                        Group::make([
                            TextInput::make('charge')
                                ->label('Delivery Charge')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->default(0)
                                ->prefix('৳'),

                            TextInput::make('free_shipping_threshold')
                                ->label('Free Delivery Above')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('৳')
                                ->helperText('Orders with a subtotal at or above this amount ship free. Leave empty to always charge.'),
                        ])->columns(2),

                        Toggle::make('is_active')
                            ->label('Available at checkout')
                            ->default(true),
                    ]),
            ]);
    }
}
