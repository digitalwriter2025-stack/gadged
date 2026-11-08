<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceHighlights\Schemas;

use App\Support\Icons\IconHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceHighlightForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Highlight Badge Details')
                    ->description('Feature trust badges such as Free Delivery, Warranty, Cash on Delivery, etc.')
                    ->schema([
                        Group::make([
                            Select::make('icon')
                                ->label('Select Icon')
                                ->options(IconHelper::getSelectOptions())
                                ->searchable()
                                ->required()
                                ->default('truck'),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0)
                                ->label('Display Order'),
                        ])->columns(2),

                        Group::make([
                            TextInput::make('title')
                                ->label('Badge Title')
                                ->required()
                                ->placeholder('e.g. Free Delivery, Official Warranty, 24/7 Support'),

                            TextInput::make('subtitle')
                                ->label('Subtitle / Description')
                                ->placeholder('e.g. Across Bangladesh, 100% genuine products'),
                        ])->columns(2),

                        TextInput::make('url')
                            ->label('Optional Target URL')
                            ->placeholder('e.g. /page/warranty-policy or /page/delivery-info'),

                        Toggle::make('is_active')
                            ->label('Visible on Storefront')
                            ->default(true),
                    ]),
            ]);
    }
}
