<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments;

use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Pages\CreateCommercialDocument;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Pages\EditCommercialDocument;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Pages\ListCommercialDocuments;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Pages\ViewCommercialDocument;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\RelationManagers\LinesRelationManager;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\RelationManagers\PaiementsRelationManager;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\RelationManagers\RelancesRelationManager;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Schemas\CommercialDocumentForm;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Schemas\CommercialDocumentInfolist;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Tables\CommercialDocumentsTable;
use App\Models\Commerce\CommercialDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommercialDocumentResource extends Resource
{
    protected static ?string $model = CommercialDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?string $navigationLabel = 'Documents';
    protected static string|null|\UnitEnum $navigationGroup = 'Documents';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return CommercialDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CommercialDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommercialDocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LinesRelationManager::class,
            PaiementsRelationManager::class,
            RelancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommercialDocuments::route('/'),
            'create' => CreateCommercialDocument::route('/create'),
            'view' => ViewCommercialDocument::route('/{record}'),
            'edit' => EditCommercialDocument::route('/{record}/edit'),
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
