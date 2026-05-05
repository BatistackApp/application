<?php

namespace App\Filament\Article\Resources\Article\Articles\Actions;

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
use Illuminate\Validation\ValidationException;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class StockEntryAction
{
    public static function make(): Action
    {
        return Action::make('stock_entry')
            ->label('Entrée')
            ->icon(Phosphor::ArrowCircleDown)
            ->color('success')
            ->modalHeading(fn (Article $record) => "Entrée de stock — {$record->name}")
            ->modalDescription('Enregistre une réception de marchandise dans un dépôt.')
            ->schema(fn (Article $record) => [
                Grid::make(2)
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Dépôt de destination')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('quantity')
                            ->label('Quantité')
                            ->numeric()
                            ->minValue(0.001)
                            ->step(0.001)
                            ->suffix($record->unit->getAbrv())
                            ->required(),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('unit_cost_ht')
                            ->label('Coût unitaire HT')
                            ->numeric()
                            ->suffix('€')
                            ->helperText('Optionnel — pour le calcul du CUMP'),

                        TextInput::make('reference')
                            ->label('Référence')
                            ->placeholder('N° BL, commande...'),
                    ]),

                Select::make('serial_number_id')
                    ->label('Numéro de série')
                    ->visible(fn () => $record->tracking_type === TrackingType::SERIAL_NUMBER)
                    ->options(
                        fn () => $record->serialNumbers()
                            ->whereIn('status', ['in_stock', 'maintenance'])
                            ->pluck('serial_number', 'id')
                    )
                    ->searchable(),

                Textarea::make('note')
                    ->label('Note')
                    ->rows(2),
            ])
            ->action(function (Article $record, array $data, StockMouvementService $service) {
                try {
                    $service->create(
                        type: StockMouvementType::ENTRY,
                        article: $record,
                        warehouse: Warehouse::findOrFail($data['warehouse_id']),
                        quantity: (float) $data['quantity'],
                        user: auth()->user(),
                        options: array_filter([
                            'unit_cost_ht' => $data['unit_cost_ht'] ?? null,
                            'reference' => $data['reference'] ?? null,
                            'note' => $data['note'] ?? null,
                            'serial_number_id' => $data['serial_number_id'] ?? null,
                        ]),
                    );

                    Notification::make()
                        ->title('Entrée enregistrée')
                        ->body("+ {$data['quantity']} {$record->unit->getAbrv()} dans le dépôt sélectionné.")
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Erreur de validation')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }
}
