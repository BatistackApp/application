<?php

namespace App\Filament\Article\Resources\Article\Articles\RelationManagers;

use App\Models\Core\Warehouse;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class WarehousesRelationManager extends RelationManager
{
    protected static string $relationship = 'warehouses';
    protected static ?string $title = 'Stocks & Dépot';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('warehouse_id')
                    ->label('Dépot')
                    ->options(Warehouse::get()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->createOptionModalHeading('Nouveau Dépot')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Désignation')
                            ->required(),

                        TextInput::make('location')
                            ->label('Adresse')
                            ->helperText('Le système géolocalisera automatiquement le dépot'),
                    ])
                    ->createOptionUsing(function (array $data) {
                        Warehouse::create($data);

                        Notification::make()
                            ->success()
                            ->title('Dépot Ajouter')
                            ->send();
                    })
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('article_id')
            ->emptyStateHeading('Article non pris en charge pour les stocks')
            ->emptyStateIcon(Phosphor::Empty)
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Inserer un nouveau stock')
                    ->icon('heroicon-s-plus'),
            ])
            ->columns([
                TextColumn::make('warehouse.name')
                    ->description(fn(Model $record) => $record->bin_location)
                    ->label('Dépot'),

                TextColumn::make('min_stock')
                    ->label('Stock minimum'),

                TextColumn::make('max_stock')
                    ->label('Stock maximum'),

                TextColumn::make('alert_stock')
                    ->label('Alerte de stock'),

                TextColumn::make('actual_stock')
                    ->label('Stock Actuel'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AttachAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
