<?php

namespace App\Filament\Article\Resources\Article\Ouvrages;

use App\Filament\Article\Resources\Article\Ouvrages\Pages\CreateOuvrage;
use App\Filament\Article\Resources\Article\Ouvrages\Pages\EditOuvrage;
use App\Filament\Article\Resources\Article\Ouvrages\Pages\ListOuvrages;
use App\Filament\Article\Resources\Article\Ouvrages\Pages\ViewOuvrage;
use App\Filament\Article\Resources\Article\Ouvrages\RelationManagers\ComponentsRelationManager;
use App\Filament\Article\Resources\Article\Ouvrages\Schemas\OuvrageForm;
use App\Filament\Article\Resources\Article\Ouvrages\Schemas\OuvrageInfolist;
use App\Filament\Article\Resources\Article\Ouvrages\Tables\OuvragesTable;
use App\Models\Article\Ouvrage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class OuvrageResource extends Resource
{
    protected static ?string $model = Ouvrage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice;

    protected static ?string $navigationLabel = 'Ouvrages';

    protected static ?string $modelLabel = 'Ouvrage';

    protected static ?string $pluralModelLabel = 'Ouvrages';

    protected static string | UnitEnum | null $navigationGroup = 'Articles';

    public static function form(Schema $schema): Schema
    {
        return OuvrageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OuvrageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OuvragesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ComponentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOuvrages::route('/'),
            'create' => CreateOuvrage::route('/create'),
            'view' => ViewOuvrage::route('/{record}'),
            'edit' => EditOuvrage::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
