<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomeSections\Tables;

use App\Enums\HomeSectionType;
use App\Enums\ProductCollectionSource;
use App\Models\Category;
use App\Models\HomeSection;
use App\Services\CatalogCache;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class HomeSectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->withCount('items')
                ->addSelect([
                    'blocks_above' => self::countNeighbours('<'),
                    'blocks_below' => self::countNeighbours('>'),
                ]))
            ->columns([
                TextColumn::make('position')
                    ->label('#')
                    ->state(fn (HomeSection $record): int => (int) $record->blocks_above + 1)
                    ->formatStateUsing(fn (int $state): HtmlString => new HtmlString('<span class="gx-hs-position">'.$state.'</span>'))
                    ->width('64px'),

                TextColumn::make('title')
                    ->label('Homepage block')
                    ->state(fn (HomeSection $record): string => $record->title ?: $record->type->getLabel())
                    ->searchable()
                    ->formatStateUsing(fn (string $state, HomeSection $record): HtmlString => new HtmlString(
                        view('filament.home-sections.block', [
                            'type' => $record->type,
                            'title' => $state,
                            'summary' => self::contentSummary($record),
                            'isHidden' => ! $record->is_active,
                        ])->render()
                    )),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->icon(false)
                    ->tooltip(fn (HomeSection $record): string => $record->type->getDescription()),

                ToggleColumn::make('is_active')
                    ->label('Show on site')
                    ->onColor('success')
                    ->tooltip(fn (HomeSection $record): string => $record->is_active
                        ? 'Showing on the homepage. Click to hide it.'
                        : 'Not on the homepage. Click to show it.'),
            ])
            ->recordClasses(fn (HomeSection $record): ?string => $record->is_active ? null : 'gx-hs-row-hidden')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->reorderRecordsTriggerAction(fn (Action $action, bool $isReordering): Action => $action
                ->button()
                ->label($isReordering ? 'Done' : 'Drag to reorder')
                ->color($isReordering ? 'primary' : 'gray'))
            // Dragging saves with one query and skips model events, so the storefront cache is cleared here.
            ->afterReordering(fn (CatalogCache $cache) => $cache->flush())
            ->paginated(false)
            ->emptyStateIcon(Heroicon::OutlinedViewColumns)
            ->emptyStateHeading('No homepage blocks yet')
            ->emptyStateDescription('Add blocks such as a hero slider, a flash sale or a row of products to build the homepage.')
            ->recordActions([
                ActionGroup::make([
                    Action::make('moveUp')
                        ->label('Move up')
                        ->hiddenLabel()
                        ->tooltip(fn (HomeSection $record): string => (int) $record->blocks_above === 0 ? 'Already at the top' : 'Move up')
                        ->icon(Heroicon::ArrowUp)
                        ->color('gray')
                        ->extraAttributes(['class' => 'gx-hs-move'])
                        ->disabled(fn (HomeSection $record): bool => (int) $record->blocks_above === 0)
                        ->action(fn (HomeSection $record) => $record->moveUp()),

                    Action::make('moveDown')
                        ->label('Move down')
                        ->hiddenLabel()
                        ->tooltip(fn (HomeSection $record): string => (int) $record->blocks_below === 0 ? 'Already at the bottom' : 'Move down')
                        ->icon(Heroicon::ArrowDown)
                        ->color('gray')
                        ->extraAttributes(['class' => 'gx-hs-move'])
                        ->disabled(fn (HomeSection $record): bool => (int) $record->blocks_below === 0)
                        ->action(fn (HomeSection $record) => $record->moveDown()),
                ])->buttonGroup(),

                EditAction::make(),

                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Counts the blocks above ("<") or below (">") a row in homepage order, so the
     * list shows true positions even when sort_order numbers have gaps or repeats.
     *
     * @return Builder<HomeSection>
     */
    private static function countNeighbours(string $operator): Builder
    {
        return HomeSection::query()
            ->from('home_sections', 'neighbour')
            ->selectRaw('count(*)')
            ->whereRaw("(neighbour.sort_order, neighbour.id) {$operator} (home_sections.sort_order, home_sections.id)");
    }

    /**
     * One plain sentence saying where the block's content comes from.
     */
    private static function contentSummary(HomeSection $record): string
    {
        return match ($record->type) {
            HomeSectionType::Hero => 'Main slider and side banners – change them in Banners & Sliders',
            HomeSectionType::PromoBanners => 'Banner images – change them in Banners & Sliders',
            HomeSectionType::ServiceHighlights => 'Trust badges strip – change them in Service Highlights',
            HomeSectionType::FlashSale => 'Products from the running Flash Sale',
            HomeSectionType::FeaturedCategories => 'Category icons',
            HomeSectionType::FeaturedBrands => 'Brand logos',
            HomeSectionType::Showcase => self::productSummary($record).', '.($record->setting('theme') === 'midnight' ? 'black panel' : 'red panel'),
            HomeSectionType::ProductCollection, HomeSectionType::ProductGrid => self::productSummary($record),
        };
    }

    private static function productSummary(HomeSection $record): string
    {
        $source = ProductCollectionSource::tryFrom((string) $record->setting('source')) ?? ProductCollectionSource::Latest;

        if ($source === ProductCollectionSource::Manual) {
            return $record->items_count.' hand-picked '.Str::plural('product', $record->items_count);
        }

        $from = $source === ProductCollectionSource::Category
            ? 'Products from '.(Category::query()->whereKey($record->setting('category_id'))->value('name') ?? 'a deleted category')
            : $source->getLabel();

        return $from.', up to '.$record->setting('limit', 12);
    }
}
