<?php

declare(strict_types=1);

namespace App\Filament\Resources\Offers\Schemas;

use App\Models\Offer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Campaign Basics')
                    ->schema([
                        Group::make([
                            TextInput::make('title')
                                ->label('Campaign Title')
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
                                ->unique(Offer::class, 'slug', ignoreRecord: true),
                        ])->columns(2),

                        Group::make([
                            TextInput::make('summary')
                                ->label('Short Promo Summary')
                                ->placeholder('e.g. Get up to 40% discount on selected gadgets'),

                            Select::make('availability')
                                ->options([
                                    'Online' => 'Online Only',
                                    'All Outlets' => 'All Showrooms & Online',
                                    'Selected Branches' => 'Selected Showrooms',
                                ])
                                ->default('Online'),
                        ])->columns(2),

                        FileUpload::make('poster_upload')
                            ->label('Offer Poster / Artwork (Square ~480x480)')
                            ->disk('public')
                            ->directory('offers')
                            ->image()
                            ->imageEditor(),

                        RichEditor::make('description')
                            ->label('Full Campaign Terms & Details'),
                    ]),

                Section::make('Associated Products & Placement')
                    ->schema([
                        Select::make('products')
                            ->relationship('products', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->label('Included Products'),

                        Group::make([
                            Toggle::make('is_mobile_deal')
                                ->label('Show in "Mobile Deals" Page (/mobile-deals)')
                                ->helperText('When enabled, this offer also appears on the dedicated mobile deals page.')
                                ->default(false),

                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                        ])->columns(3),

                        Group::make([
                            DateTimePicker::make('starts_at')
                                ->label('Campaign Starts At')
                                ->required()
                                ->default(now()),

                            DateTimePicker::make('ends_at')
                                ->label('Campaign Ends At')
                                ->required()
                                ->default(now()->addDays(7)),
                        ])->columns(2),
                    ]),
            ]);
    }
}
