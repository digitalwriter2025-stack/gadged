<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use App\Support\Filament\TranslatableTabs;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * Page editor: title and content in the main column;
 * publishing, footer placement and URL in the sidebar.
 */
class PageForm
{
    /**
     * Footer choice for a page that is not linked from the footer.
     */
    public const NOT_IN_FOOTER = 'none';

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    self::contentSection(),
                    self::seoSection(),
                ])
                    ->columnSpan(['lg' => 2])
                    ->extraAttributes(['class' => 'gx-page-form']),

                Group::make([
                    self::publishingSection(),
                    self::footerSection(),
                    self::urlSection(),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    private static function contentSection(): Section
    {
        return Section::make('Page content')
            ->description('What customers read on this page.')
            ->icon('heroicon-o-document-text')
            ->schema([
                TranslatableTabs::make('page', fn (string $suffix, bool $isEnglish): array => [
                    TextInput::make("title{$suffix}")
                        ->label($isEnglish ? 'Page title' : 'পেজের শিরোনাম (Page title)')
                        ->placeholder($isEnglish ? 'e.g. Warranty Policy' : 'যেমন: ওয়ারেন্টি নীতি')
                        ->helperText(TranslatableTabs::fallbackHint($isEnglish))
                        ->required($isEnglish)
                        ->maxLength(150)
                        ->extraInputAttributes(['class' => 'gx-page-title'])
                        ->when($isEnglish, fn (TextInput $field): TextInput => $field
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            })),

                    RichEditor::make("content{$suffix}")
                        ->label($isEnglish ? 'Content' : 'পেজের লেখা (Content)')
                        ->required($isEnglish)
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('pages')
                        ->minHeight('26rem')
                        ->extraAttributes(['class' => 'gx-page-editor']),
                ]),
            ]);
    }

    private static function seoSection(): Section
    {
        return Section::make('Search engine listing')
            ->description('Optional. How this page appears on Google and when shared.')
            ->icon('heroicon-o-magnifying-glass')
            ->collapsible()
            ->collapsed()
            ->schema([
                TranslatableTabs::make('page-seo', fn (string $suffix, bool $isEnglish): array => [
                    TextInput::make("seo_title{$suffix}")
                        ->label($isEnglish ? 'SEO title' : 'SEO শিরোনাম (SEO title)')
                        ->maxLength(255)
                        ->placeholder('Leave blank to use the page title'),

                    Textarea::make("seo_description{$suffix}")
                        ->label($isEnglish ? 'Meta description' : 'মেটা বিবরণ (Meta description)')
                        ->maxLength(500)
                        ->rows(3)
                        ->placeholder('One or two sentences for search engine snippets'),
                ]),
            ]);
    }

    private static function publishingSection(): Section
    {
        return Section::make('Visibility')
            ->icon('heroicon-o-eye')
            ->schema([
                Toggle::make('is_active')
                    ->label('Published')
                    ->helperText('Turn off to hide the page and its footer link without deleting it.')
                    ->default(true),
            ]);
    }

    private static function footerSection(): Section
    {
        return Section::make('Footer link')
            ->description('Pick the footer column that links to this page.')
            ->icon('heroicon-o-bars-3-bottom-left')
            ->schema([
                Radio::make('footer_location')
                    ->hiddenLabel()
                    ->options([
                        'footer_information' => 'Information',
                        'footer_policies' => 'Policies',
                        self::NOT_IN_FOOTER => 'Not in footer',
                    ])
                    ->descriptions([
                        'footer_information' => 'About us, service centers, EMI...',
                        'footer_policies' => 'Privacy, warranty, delivery, returns...',
                        self::NOT_IN_FOOTER => 'Reach it by its link only.',
                    ])
                    ->default(self::NOT_IN_FOOTER)
                    ->extraAttributes(['class' => 'gx-choice-cards'])
                    ->helperText('Reorder or rename footer links under Content → Navigation Menus.')
                    ->dehydrated(false),
            ]);
    }

    private static function urlSection(): Section
    {
        return Section::make('Page link')
            ->icon('heroicon-o-link')
            ->schema([
                TextInput::make('slug')
                    ->label('URL slug')
                    ->prefix('/page/')
                    ->required()
                    ->unique(Page::class, 'slug', ignoreRecord: true)
                    ->helperText('Filled in from the English title. Changing it breaks links shared earlier.'),
            ]);
    }
}
