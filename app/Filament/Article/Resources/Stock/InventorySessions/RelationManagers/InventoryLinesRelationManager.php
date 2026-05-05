<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\RelationManagers;

use App\Enums\Article\InventorySessionStatus;
use App\Models\Stock\InventoryLine;
use App\Services\Stock\InventorySessionService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class InventoryLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    protected static ?string $title = 'Lignes d\'inventaire';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('article.name')
            ->columns([
                TextColumn::make('article.sku')
                    ->label('SKU')
                    ->searchable()
                    ->width('100px'),

                TextColumn::make('article.name')
                    ->label('Désignation')
                    ->searchable(),

                TextColumn::make('article.articleCategory.name')
                    ->label('Catégorie'),

                TextColumn::make('theoretical_quantity')
                    ->label('Qté théorique')
                    ->numeric(decimalPlaces: 3)
                    ->alignRight(),

                TextColumn::make('counted_quantity')
                    ->label('Qté comptée')
                    ->numeric(decimalPlaces: 3)
                    ->alignRight()
                    ->placeholder('—')
                    ->color(fn (InventoryLine $record) => match (true) {
                        $record->counted_quantity === null => 'gray',
                        $record->counted_quantity == $record->theoretical_quantity => 'success',
                        default => 'danger',
                    }),

                TextColumn::make('difference')
                    ->label('Écart')
                    ->numeric(decimalPlaces: 3)
                    ->alignRight()
                    ->placeholder('—')
                    ->color(fn (InventoryLine $record) => match (true) {
                        $record->counted_quantity === null => 'gray',
                        $record->difference > 0 => 'success',
                        $record->difference < 0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (InventoryLine $record) => $record->counted_quantity !== null
                        ? ($record->difference > 0 ? '+' : '')
                        .number_format($record->difference, 3, ',', ' ')
                        : null
                    ),
            ])
            ->filters([])
            ->recordActions([
                Action::make('saisir')
                    ->label('Saisir')
                    ->icon(Phosphor::PencilSimple)
                    ->color('primary')
                    ->visible(fn () => in_array(
                        $this->getOwnerRecord()->status,
                        [InventorySessionStatus::COUNTING]
                    ))
                    ->fillForm(fn (InventoryLine $record) => [
                        'counted_quantity' => $record->counted_quantity,
                    ])
                    ->schema([
                        TextInput::make('counted_quantity')
                            ->label('Quantité comptée')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001)
                            ->required()
                            ->helperText(fn (InventoryLine $record) => "Quantité théorique : {$record->theoretical_quantity}"
                            ),
                    ])
                    ->action(function (InventoryLine $record, array $data, InventorySessionService $service) {
                        try {
                            $service->saveLine($record, (float) $data['counted_quantity']);

                            Notification::make()
                                ->title('Ligne mise à jour')
                                ->success()
                                ->send();
                        } catch (ValidationException $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body(collect($e->errors())->flatten()->first())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([]);
    }

    public function isReadOnly(): bool
    {
        return ! in_array(
            $this->getOwnerRecord()->status,
            [InventorySessionStatus::COUNTING]
        );
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }
}
