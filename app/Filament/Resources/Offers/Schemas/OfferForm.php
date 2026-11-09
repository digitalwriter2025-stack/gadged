<?php

declare(strict_types=1);

namespace App\Filament\Resources\Offers\Schemas;

use App\Models\Offer;
use App\Support\Filament\TranslatableTabs;
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
                        TranslatableTabs::make('campaign', fn (string $suffix, bool $isEnglish): array => [
                            TextInput::make("title{$suffix}")
                                ->label($isEnglish ? 'Campaign Title' : 'ক্যাম্পেইনের নাম (Campaign Title)')
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

                            TextInput::make("summary{$suffix}")
                                ->label($isEnglish ? 'Short Promo Summary' : 'সংক্ষিপ্ত অফার (Short Promo Summary)')
                                ->maxLength(255)
                                ->placeholder($isEnglish ? 'e.g. Get up to 40% discount on selected gadgets' : 'যেমন: নির্বাচিত গ্যাজেটে ৪০% পর্যন্ত ছাড়'),

                            RichEditor::make("description{$suffix}")
                                ->label($isEnglish ? 'Full Campaign Terms & Details' : 'শর্তাবলি ও বিস্তারিত (Campaign Terms & Details)'),
                        ]),

                        Group::make([
                            TextInput::make('slug')
                                ->label('URL Slug')
                                ->required()
                                ->unique(Offer::class, 'slug', ignoreRecord: true),

                            Select::make('availability')
                                ->options([
                                    'Online' => 'Online Only',
                                    'All Outlets' => 'All Showrooms & Online',
                                    'Selected Branches' => 'Selected Showrooms',
                                ])
                                ->default('Online'),
                        ])->columns(2),

                        FileUpload::make('poster_upload')
                            ->label('Campaign Poster / Artwork (Square ~480x480)')
                            ->disk('public')
                            ->directory('offers')
                            ->image()
                            ->imageEditor()
                            ->dehydrated(false),
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
