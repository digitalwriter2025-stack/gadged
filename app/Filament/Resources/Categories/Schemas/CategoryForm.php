<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use App\Support\Filament\TranslatableTabs;
use App\Support\Icons\IconHelper;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Hierarchy & Basics')
                    ->schema([
                        Group::make([
                            Select::make('parent_id')
                                ->label('Parent Category')
                                ->options(fn (): array => Category::query()->whereNull('parent_id')->pluck('name', 'id')->all())
                                ->searchable()
                                ->placeholder('None (Root Category)'),

                            TextInput::make('sort_order')
                                ->label('Sort Order')
                                ->numeric()
                                ->default(0),
                        ])->columns(2),

                        TranslatableTabs::make('category', fn (string $suffix, bool $isEnglish): array => [
                            TextInput::make("name{$suffix}")
                                ->label($isEnglish ? 'Category Name' : 'ক্যাটাগরির নাম (Category Name)')
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

                            Textarea::make("description{$suffix}")
                                ->label($isEnglish ? 'Description' : 'বিবরণ (Description)')
                                ->rows(2),
                        ]),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->required()
                            ->unique(Category::class, 'slug', ignoreRecord: true),

                        Group::make([
                            Select::make('icon')
                                ->label('Category Icon')
                                ->options(IconHelper::getSelectOptions())
                                ->searchable(),

                            FileUpload::make('image_upload')
                                ->label('Category Thumbnail / Icon Image')
                                ->disk('public')
                                ->directory('categories')
                                ->image()
                                ->dehydrated(false),
                        ])->columns(2),
                    ]),

                Section::make('Display & Navigation Flags')
                    ->description('Controls where this category appears across the storefront.')
                    ->schema([
                        Group::make([
                            Toggle::make('show_in_menu')
                                ->label('Show in Header Navigation Mega-Menu')
                                ->default(true)
                                ->helperText('When enabled, this category appears in the main top navigation.'),

                            Toggle::make('is_featured')
                                ->label('Feature on Homepage Grid')
                                ->default(false)
                                ->helperText('Displays inside the Featured Categories circle grid on home.'),

                            Toggle::make('is_active')
                                ->label('Active & Published')
                                ->default(true),
                        ])->columns(3),
                    ]),
            ]);
    }
}
