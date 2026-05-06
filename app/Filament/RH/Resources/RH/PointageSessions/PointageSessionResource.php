<?php

namespace App\Filament\RH\Resources\RH\PointageSessions;

use App\Filament\RH\Resources\RH\PointageSessions\Pages\CreatePointageSession;
use App\Filament\RH\Resources\RH\PointageSessions\Pages\EditPointageSession;
use App\Filament\RH\Resources\RH\PointageSessions\Pages\ListPointageSessions;
use App\Filament\RH\Resources\RH\PointageSessions\Pages\ViewPointageSession;
use App\Filament\RH\Resources\RH\PointageSessions\Schemas\PointageSessionForm;
use App\Filament\RH\Resources\RH\PointageSessions\Schemas\PointageSessionInfolist;
use App\Filament\RH\Resources\RH\PointageSessions\Tables\PointageSessionsTable;
use App\Models\RH\PointageSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PointageSessionResource extends Resource
{
    protected static ?string $model = PointageSession::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::ClipboardText;

    protected static ?string $navigationLabel = 'Pointages';

    protected static ?string $modelLabel = 'Session de pointage';

    protected static ?string $pluralModelLabel = 'Sessions de pointage';

    public static function form(Schema $schema): Schema
    {
        return PointageSessionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PointageSessionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PointageSessionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPointageSessions::route('/'),
            'create' => CreatePointageSession::route('/create'),
            'view' => ViewPointageSession::route('/{record}'),
            'edit' => EditPointageSession::route('/{record}/edit'),
        ];
    }
}
