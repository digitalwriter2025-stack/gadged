<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page Information')
                    ->schema([
                        Group::make([
                            TextInput::make('title')
                                ->label('Page Title')
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
                                ->unique(Page::class, 'slug', ignoreRecord: true)
                                ->helperText('Accessible via /page/{slug}'),
                        ])->columns(2),

                        RichEditor::make('content')
                            ->label('Page Content (HTML / Markdown)')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('pages'),

                        Toggle::make('is_active')
                            ->label('Published & Visible')
                            ->default(true),
                    ]),

                Section::make('Page SEO & Meta Tags')
                    ->collapsed()
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('Custom SEO Title')
                            ->placeholder('Leave blank to use page title'),

                        Textarea::make('seo_description')
                            ->label('Meta Description')
                            ->rows(2)
                            ->placeholder('Summary for search engine snippets'),
                    ]),
            ]);
    }
}
