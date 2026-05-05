<?php

namespace App\Filament\Chantier\Resources\Chantiers;

use App\Filament\Chantier\Resources\Chantiers\Pages\CreateChantier;
use App\Filament\Chantier\Resources\Chantiers\Pages\EditChantier;
use App\Filament\Chantier\Resources\Chantiers\Pages\GanttChantier;
use App\Filament\Chantier\Resources\Chantiers\Pages\ListChantiers;
use App\Filament\Chantier\Resources\Chantiers\Pages\ViewChantier;
use App\Filament\Chantier\Resources\Chantiers\RelationManagers\BudgetLinesRelationManager;
use App\Filament\Chantier\Resources\Chantiers\RelationManagers\CoutsRelationManager;
use App\Filament\Chantier\Resources\Chantiers\RelationManagers\DocumentsRelationManager;
use App\Filament\Chantier\Resources\Chantiers\RelationManagers\TasksRelationManager;
use App\Filament\Chantier\Resources\Chantiers\Schemas\ChantierForm;
use App\Filament\Chantier\Resources\Chantiers\Schemas\ChantierInfolist;
use App\Filament\Chantier\Resources\Chantiers\Tables\ChantiersTable;
use App\Models\Chantier\Chantier;
use BackedEnum;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ChantierResource extends Resource
{
    protected static ?string $model = Chantier::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::HardHat;

    protected static ?string $navigationLabel = 'Chantiers';

    protected static ?string $modelLabel = 'Chantier';

    protected static ?string $pluralModelLabel = 'Chantiers';

    public static function form(Schema $schema): Schema
    {
        return ChantierForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ChantierInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChantiersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BudgetLinesRelationManager::class,
            TasksRelationManager::class,
            CoutsRelationManager::class,
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChantiers::route('/'),
            'create' => CreateChantier::route('/create'),
            'view' => ViewChantier::route('/{record}'),
            'edit' => EditChantier::route('/{record}/edit'),
            'gantt' => GanttChantier::route('/{record}/gantt'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewChantier::class,
            EditChantier::class,
            GanttChantier::class,
        ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
