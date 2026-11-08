<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Schemas;

use App\Enums\BannerPlacement;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Banner Placement & Information')
                    ->schema([
                        Group::make([
                            Select::make('placement')
                                ->label('Display Location')
                                ->options(BannerPlacement::class)
                                ->required()
                                ->default(BannerPlacement::HeroMain->value),

                            TextInput::make('sort_order')
                                ->label('Sort Order')
                                ->numeric()
                                ->default(0),
                        ])->columns(2),

                        Group::make([
                            TextInput::make('title')
                                ->label('Banner Title / Heading')
                                ->placeholder('e.g. Smartphone Mega Sale'),

                            TextInput::make('subtitle')
                                ->label('Subtitle')
                                ->placeholder('Save up to 25% on selected models'),
                        ])->columns(2),

                        Group::make([
                            TextInput::make('url')
                                ->label('Target Link URL')
                                ->placeholder('https://... or /category/smartphone')
                                ->default('#'),

                            TextInput::make('cta_label')
                                ->label('CTA Button Text')
                                ->placeholder('Shop Now'),

                            Toggle::make('open_in_new_tab')
                                ->label('Open link in new tab')
                                ->default(false),
                        ])->columns(3),

                        TextInput::make('alt_text')
                            ->label('Accessibility Alt Text')
                            ->placeholder('Describe the banner for screen readers and SEO'),
                    ]),

                Section::make('Banner Images')
                    ->description('Upload high-resolution graphics for desktop and mobile screens.')
                    ->schema([
                        Group::make([
                            FileUpload::make('desktop_image')
                                ->label('Desktop Image')
                                ->helperText('Hero slider: ~1672x941 px | Side banner: ~418x216 px')
                                ->disk('public')
                                ->directory('banners')
                                ->image()
                                ->imageEditor(),

                            FileUpload::make('mobile_image')
                                ->label('Mobile Image (Optional)')
                                ->helperText('Optional mobile-optimized crop for smaller viewports.')
                                ->disk('public')
                                ->directory('banners')
                                ->image()
                                ->imageEditor(),
                        ])->columns(2),
                    ]),

                Section::make('Schedule & Visibility')
                    ->schema([
                        Group::make([
                            Toggle::make('is_active')
                                ->label('Banner Active')
                                ->default(true),

                            DateTimePicker::make('starts_at')
                                ->label('Auto-Publish At (Optional)'),

                            DateTimePicker::make('ends_at')
                                ->label('Auto-Expire At (Optional)'),
                        ])->columns(3),
                    ]),
            ]);
    }
}
