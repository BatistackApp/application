<?php

namespace App\Filament\Article\Resources\Article\Articles\RelationManagers;

use App\Enums\Article\TrackingType;
use App\Enums\TypeAccount;
use App\Models\Core\Warehouse;
use App\Models\User;
use App\Services\Core\DeviceDetector;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Marcelorodrigo\FilamentBarcodeScannerField\Forms\Components\BarcodeInput;

class SerialNumbersRelationManager extends RelationManager
{
    protected static string $relationship = 'serialNumbers';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('warehouse_id')
                    ->label('Dépots')
                    ->options(Warehouse::pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),

                BarcodeInput::make('serial_number')
                    ->label('Numéro de série')
                    ->required()
                    ->visible(fn() => DeviceDetector::isMobile()),

                TextInput::make('serial_number')
                    ->label('Numéro de série')
                    ->required()
                    ->visible(fn() => !DeviceDetector::isMobile()),

                DatePicker::make('purchase_date')
                    ->label('Date d\'achat'),

                DatePicker::make('warranty_expiry')
                    ->label('Expiration de la garantie'),

                Select::make('assigned_user_id')
                    ->label('Assignée à')
                    ->options(User::where('type_account', TypeAccount::EMPLOYEE)->pluck('name', 'id')->all()),

                SpatieMediaLibraryFileUpload::make('photo_plate_path')
                    ->collection('articles')
                    ->label('Photo'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('serial_number')
            ->columns([
                TextColumn::make('serial_number')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->tracking_type === TrackingType::SERIAL_NUMBER;
    }
}
