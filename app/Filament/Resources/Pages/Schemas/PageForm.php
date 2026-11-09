<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use App\Support\Filament\TranslatableTabs;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                        TranslatableTabs::make('page', fn (string $suffix, bool $isEnglish): array => [
                            TextInput::make("title{$suffix}")
                                ->label($isEnglish ? 'Page Title' : 'পেজের শিরোনাম (Page Title)')
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

                            RichEditor::make("content{$suffix}")
                                ->label($isEnglish ? 'Page Content (HTML / Markdown)' : 'পেজের লেখা (Page Content)')
                                ->required($isEnglish)
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('pages'),
                        ]),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->required()
                            ->unique(Page::class, 'slug', ignoreRecord: true)
                            ->helperText('Accessible via /page/{slug}. Filled in from the English title.'),

                        Toggle::make('is_active')
                            ->label('Published & Visible')
                            ->default(true),
                    ]),

                Section::make('Page SEO & Meta Tags')
                    ->collapsed()
                    ->schema([
                        TranslatableTabs::make('page-seo', fn (string $suffix, bool $isEnglish): array => [
                            TextInput::make("seo_title{$suffix}")
                                ->label($isEnglish ? 'Custom SEO Title' : 'SEO শিরোনাম (SEO Title)')
                                ->maxLength(255)
                                ->placeholder('Leave blank to use page title'),

                            Textarea::make("seo_description{$suffix}")
                                ->label($isEnglish ? 'Meta Description' : 'মেটা বিবরণ (Meta Description)')
                                ->maxLength(500)
                                ->rows(2)
                                ->placeholder('Summary for search engine snippets'),
                        ]),
                    ]),
            ]);
    }
}
