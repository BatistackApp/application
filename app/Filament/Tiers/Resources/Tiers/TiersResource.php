<?php

namespace App\Filament\Tiers\Resources\Tiers;

use App\Filament\Tiers\Resources\Tiers\Pages\CreateTiers;
use App\Filament\Tiers\Resources\Tiers\Pages\EditTiers;
use App\Filament\Tiers\Resources\Tiers\Pages\ListTiers;
use App\Filament\Tiers\Resources\Tiers\Pages\ViewTiers;
use App\Filament\Tiers\Resources\Tiers\RelationManagers\AddressesRelationManager;
use App\Filament\Tiers\Resources\Tiers\RelationManagers\ContactsRelationManager;
use App\Filament\Tiers\Resources\Tiers\Schemas\TiersForm;
use App\Filament\Tiers\Resources\Tiers\Schemas\TiersInfolist;
use App\Filament\Tiers\Resources\Tiers\Tables\TiersTable;
use App\Models\Tiers\Tiers;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TiersResource extends Resource
{
    protected static ?string $model = Tiers::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;
    protected static ?string $navigationLabel = 'Tiers';
    protected static ?string $modelLabel = 'Tier';
    protected static ?string $pluralModelLabel = 'Tiers';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return TiersForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TiersInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TiersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AddressesRelationManager::class,
            ContactsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTiers::route('/'),
            'create' => CreateTiers::route('/create'),
            'view' => ViewTiers::route('/{record}'),
            'edit' => EditTiers::route('/{record}/edit'),
        ];
    }
}
