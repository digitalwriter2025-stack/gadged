<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brands\Schemas;

use App\Models\Brand;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Brand Information')
                    ->schema([
                        Group::make([
                            TextInput::make('name')
                                ->label('Brand Name')
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
                                ->unique(Brand::class, 'slug', ignoreRecord: true),
                        ])->columns(2),

                        Group::make([
                            TextInput::make('website')
                                ->label('Official Website URL')
                                ->url()
                                ->placeholder('https://www.apple.com'),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0)
                                ->label('Display Order'),
                        ])->columns(2),

                        FileUpload::make('logo_upload')
                            ->label('Brand Logo Artwork')
                            ->disk('public')
                            ->directory('brands')
                            ->image(),

                        Group::make([
                            Toggle::make('is_featured')
                                ->label('Featured Brand (Shown in Home Carousel)')
                                ->default(false),

                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                        ])->columns(2),
                    ]),
            ]);
    }
}
