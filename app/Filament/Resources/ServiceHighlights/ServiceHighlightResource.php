<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServiceHighlights;

use App\Filament\Resources\ServiceHighlights\Pages\CreateServiceHighlight;
use App\Filament\Resources\ServiceHighlights\Pages\EditServiceHighlight;
use App\Filament\Resources\ServiceHighlights\Pages\ListServiceHighlights;
use App\Filament\Resources\ServiceHighlights\Schemas\ServiceHighlightForm;
use App\Filament\Resources\ServiceHighlights\Tables\ServiceHighlightsTable;
use App\Models\ServiceHighlight;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ServiceHighlightResource extends Resource
{
    protected static ?string $model = ServiceHighlight::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Service Highlights';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ServiceHighlightForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceHighlightsTable::configure($table);
    }

    /**
     * @return list<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'title_bn'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceHighlights::route('/'),
            'create' => CreateServiceHighlight::route('/create'),
            'edit' => EditServiceHighlight::route('/{record}/edit'),
        ];
    }
}
