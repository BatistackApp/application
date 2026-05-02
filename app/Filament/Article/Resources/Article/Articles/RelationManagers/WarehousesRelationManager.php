<?php

namespace App\Filament\Article\Resources\Article\Articles\RelationManagers;

use App\Jobs\Core\WarehouseLocatizationJob;
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
use Filament\Schemas\Components\Grid;
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
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Dépot')
                            ->options(Warehouse::get()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('bin_location')
                            ->label('Localisation dans l\'entrepot'),
                    ]),

                Grid::make(4)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('min_stock')
                            ->label('Stock minimum')
                            ->numeric(),

                        TextInput::make('max_stock')
                            ->label('Stock maximum')
                            ->numeric(),

                        TextInput::make('alert_stock')
                            ->label('Stock d\'alerte')
                            ->helperText('Déclenchera une alerte si < à ce champs')
                            ->numeric(),

                        TextInput::make('actual_stock')
                            ->label('Stock actuel')
                            ->numeric(),
                    ]),
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
                    ->modalHeading('Ajouter un nouveau stock')
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
                CreateAction::make()
                    ->modalHeading('Ajouter un nouveau stock')
                    ->icon('heroicon-s-plus')
                    ->tooltip('Ajouter un stock')
                    ->iconButton(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
