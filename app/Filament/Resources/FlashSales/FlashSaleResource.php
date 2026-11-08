<?php

declare(strict_types=1);

namespace App\Filament\Resources\FlashSales;

use App\Filament\Resources\FlashSales\Pages\CreateFlashSale;
use App\Filament\Resources\FlashSales\Pages\EditFlashSale;
use App\Filament\Resources\FlashSales\Pages\ListFlashSales;
use App\Filament\Resources\FlashSales\Schemas\FlashSaleForm;
use App\Filament\Resources\FlashSales\Tables\FlashSalesTable;
use App\Models\FlashSale;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FlashSaleResource extends Resource
{
    protected static ?string $model = FlashSale::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?string $navigationLabel = 'Flash Sales';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    public static function form(Schema $schema): Schema
    {
        return FlashSaleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlashSalesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFlashSales::route('/'),
            'create' => CreateFlashSale::route('/create'),
            'edit' => EditFlashSale::route('/{record}/edit'),
        ];
    }
}
