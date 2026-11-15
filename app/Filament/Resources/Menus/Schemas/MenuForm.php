<?php

declare(strict_types=1);

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Menu Location & Details')
                    ->schema([
                        Group::make([
                            TextInput::make('name')
                                ->label('Menu Title / Name')
                                ->required()
                                ->maxLength(100)
                                ->placeholder('e.g. Information, Policies, Customer Care'),

                            TextInput::make('name_bn')
                                ->label('মেনুর নাম (Bangla title, optional)')
                                ->maxLength(100)
                                ->placeholder('যেমন: তথ্য, নীতিমালা'),

                            Select::make('location')
                                ->label('Menu Placement Location')
                                ->options([
                                    'footer_information' => 'Footer Column 1 (Information)',
                                    'footer_policies' => 'Footer Column 2 (Policies & Terms)',
                                    'header_custom' => 'Custom Header Navigation',
                                ])
                                ->required(),
                        ])->columns(3),
                    ]),

                Section::make('Menu Navigation Links')
                    ->schema([
                        Repeater::make('rootItems')
                            ->relationship('rootItems')
                            ->label('Menu Links')
                            ->schema([
                                Group::make([
                                    TextInput::make('label')
                                        ->label('Link Label')
                                        ->required()
                                        ->maxLength(100)
                                        ->placeholder('e.g. About Us, Privacy Policy, Terms'),

                                    TextInput::make('label_bn')
                                        ->label('লিংকের নাম (Bangla label, optional)')
                                        ->maxLength(100)
                                        ->placeholder('যেমন: আমাদের সম্পর্কে, গোপনীয়তা নীতি'),
                                ])->columns(2),

                                Hidden::make('linkable_id'),

                                TextInput::make('url')
                                    ->label('Destination URL')
                                    ->placeholder('e.g. /page/about-us or https://...')
                                    ->helperText(fn (Get $get): ?string => filled($get('linkable_id')) ? 'Linked to a page; leave blank to keep that link.' : null)
                                    ->required(fn (Get $get): bool => blank($get('linkable_id'))),

                                Group::make([
                                    Toggle::make('open_in_new_tab')
                                        ->label('Open in new tab')
                                        ->default(false),

                                    Toggle::make('is_active')
                                        ->label('Active')
                                        ->default(true),

                                    TextInput::make('sort_order')
                                        ->numeric()
                                        ->default(0)
                                        ->label('Order'),
                                ])->columns(3),
                            ])
                            ->orderColumn('sort_order')
                            ->defaultItems(1)
                            ->collapsible(),
                    ]),
            ]);
    }
}
