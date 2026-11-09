<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceHighlights\Schemas;

use App\Support\Filament\TranslatableTabs;
use App\Support\Icons\IconHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ServiceHighlightForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Highlight Badge Details')
                    ->description('Feature trust badges such as Free Delivery, Warranty, Cash on Delivery, etc.')
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Select::make('icon')
                            ->label('Select Icon')
                            ->options(IconHelper::getSelectOptions())
                            ->searchable()
                            ->required()
                            ->live()
                            ->default('truck'),

                        TranslatableTabs::make('highlight', fn (string $suffix, bool $isEnglish): array => [
                            Group::make([
                                TextInput::make("title{$suffix}")
                                    ->label($isEnglish ? 'Badge Title' : 'শিরোনাম (Badge Title)')
                                    ->required($isEnglish)
                                    ->maxLength(100)
                                    ->live(onBlur: true)
                                    ->placeholder($isEnglish ? 'e.g. Free Delivery, Official Warranty, 24/7 Support' : 'যেমন: ফ্রি ডেলিভারি, অফিসিয়াল ওয়ারেন্টি'),

                                TextInput::make("subtitle{$suffix}")
                                    ->label($isEnglish ? 'Subtitle / Description' : 'সাবটাইটেল (Subtitle)')
                                    ->maxLength(150)
                                    ->live(onBlur: true)
                                    ->placeholder($isEnglish ? 'e.g. Across Bangladesh, 100% genuine products' : 'যেমন: সারা বাংলাদেশে, ১০০% আসল পণ্য'),
                            ])->columns(2),
                        ]),

                        TextInput::make('url')
                            ->label('Optional Target URL')
                            ->placeholder('e.g. /page/warranty-policy or /page/delivery-info')
                            ->helperText('Customers who click the badge go here. Leave empty for a plain badge.'),
                    ]),

                Group::make([
                    Section::make('Storefront Preview')
                        ->schema([
                            Html::make(fn (Get $get): HtmlString => new HtmlString(
                                view('filament.service-highlights.preview', [
                                    'icon' => (string) $get('icon'),
                                    'title' => $get('title'),
                                    'subtitle' => $get('subtitle'),
                                    'titleBn' => $get('title_bn'),
                                    'subtitleBn' => $get('subtitle_bn'),
                                ])->render()
                            )),
                        ]),

                    Section::make('Visibility')
                        ->schema([
                            Toggle::make('is_active')
                                ->label('Visible on Storefront')
                                ->default(true),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0)
                                ->label('Display Order')
                                ->helperText('Lower numbers show first. You can also reorder badges from the list.'),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
