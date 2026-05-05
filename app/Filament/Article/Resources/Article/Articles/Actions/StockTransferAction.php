<?php

namespace App\Filament\Article\Resources\Article\Articles\Actions;

use App\Enums\Article\StockMouvementType;
use App\Models\Article\Article;
use App\Models\Core\Warehouse;
use App\Services\Stock\StockMouvementService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Validation\ValidationException;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class StockTransferAction
{
    public static function make(): Action
    {
        return Action::make('stock_transfer')
            ->label('Transfert')
            ->icon(Phosphor::ArrowsLeftRight)
            ->color('info')
            ->modalHeading(fn (Article $record) => "Transfert de stock — {$record->name}")
            ->modalDescription('Déplace du stock d\'un dépôt vers un autre.')
            ->schema(fn (Article $record) => [
                Grid::make(2)
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Dépôt source')
                            ->options(function () use ($record) {
                                return $record->warehouses()
                                    ->wherePivot('actual_stock', '>', 0)
                                    ->get()
                                    ->mapWithKeys(fn ($w) => [
                                        $w->id => "{$w->name} (stock : {$w->pivot->actual_stock})",
                                    ]);
                            })
                            ->searchable()
                            ->live()
                            ->required(),

                        Select::make('target_warehouse_id')
                            ->label('Dépôt destination')
                            ->options(fn (Get $get) => Warehouse::where('is_active', true)
                                ->where('id', '!=', $get('warehouse_id'))
                                ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->required(),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Quantité')
                            ->numeric()
                            ->minValue(0.001)
                            ->step(0.001)
                            ->suffix($record->unit->getAbrv())
                            ->required(),

                        TextInput::make('reference')
                            ->label('Référence'),
                    ]),

                Textarea::make('note')
                    ->label('Note')
                    ->rows(2),
            ])
            ->action(function (Article $record, array $data, StockMouvementService $service) {
                try {
                    $service->create(
                        type: StockMouvementType::TRANSFER,
                        article: $record,
                        warehouse: Warehouse::findOrFail($data['warehouse_id']),
                        quantity: (float) $data['quantity'],
                        user: auth()->user(),
                        options: [
                            'target_warehouse_id' => $data['target_warehouse_id'],
                            'reference' => $data['reference'] ?? null,
                            'note' => $data['note'] ?? null,
                        ],
                    );

                    Notification::make()
                        ->title('Transfert effectué')
                        ->body("{$data['quantity']} {$record->unit->getAbrv()} transférés avec succès.")
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Erreur')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }
}
