<?php

namespace App\Filament\Article\Resources\Article\Articles\Actions;

use App\Enums\Article\SerialNumberStatus;
use App\Enums\Article\StockMouvementType;
use App\Enums\Article\TrackingType;
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

class StockExitAction
{
    public static function make(): Action
    {
        return Action::make('stock_exit')
            ->label('Sortie')
            ->icon(Phosphor::ArrowCircleUp)
            ->color('danger')
            ->modalHeading(fn (Article $record) => "Sortie de stock — {$record->name}")
            ->modalDescription('Enregistre une consommation ou vente depuis un dépôt.')
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

                        TextInput::make('quantity')
                            ->label('Quantité')
                            ->numeric()
                            ->minValue(0.001)
                            ->step(0.001)
                            ->suffix($record->unit->getAbrv())
                            ->required(),
                    ]),

                Select::make('serial_number_id')
                    ->label('Numéro de série')
                    ->visible(fn () => $record->tracking_type === TrackingType::SERIAL_NUMBER)
                    ->options(fn (Get $get) => $record->serialNumbers()
                        ->where('status', SerialNumberStatus::IN_STOCK)
                        ->where('warehouse_id', $get('warehouse_id'))
                        ->pluck('serial_number', 'id')
                    )
                    ->searchable(),

                Grid::make(2)
                    ->schema([
                        TextInput::make('reference')
                            ->label('Référence')
                            ->placeholder('N° chantier, commande...'),

                        Textarea::make('note')
                            ->label('Note')
                            ->rows(2),
                    ]),
            ])
            ->action(function (Article $record, array $data, StockMouvementService $service) {
                try {
                    $service->create(
                        type: StockMouvementType::EXIT,
                        article: $record,
                        warehouse: Warehouse::findOrFail($data['warehouse_id']),
                        quantity: (float) $data['quantity'],
                        user: auth()->user(),
                        options: array_filter([
                            'reference' => $data['reference'] ?? null,
                            'note' => $data['note'] ?? null,
                            'serial_number_id' => $data['serial_number_id'] ?? null,
                        ]),
                    );

                    Notification::make()
                        ->title('Sortie enregistrée')
                        ->body("- {$data['quantity']} {$record->unit->getAbrv()} du dépôt sélectionné.")
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Stock insuffisant')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }
}
