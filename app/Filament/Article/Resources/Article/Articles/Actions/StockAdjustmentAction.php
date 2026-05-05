<?php

namespace App\Filament\Article\Resources\Article\Articles\Actions;

use App\Enums\Article\AdjustementType;
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
use Illuminate\Validation\ValidationException;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class StockAdjustmentAction
{
    public static function make(): Action
    {
        return Action::make('stock_adjustment')
            ->label('Ajustement')
            ->icon(Phosphor::Sliders)
            ->color('warning')
            ->modalHeading(fn (Article $record) => "Ajustement de stock — {$record->name}")
            ->modalDescription('Corrige le stock suite à un inventaire ou une erreur de saisie.')
            ->schema(fn (Article $record) => [
                Grid::make(2)
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Dépôt')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('adjustement_type')
                            ->label("Type d'ajustement")
                            ->options(AdjustementType::class)
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
                            ->label('Référence')
                            ->placeholder('N° inventaire...'),
                    ]),

                Textarea::make('note')
                    ->label('Motif de l\'ajustement')
                    ->rows(2)
                    ->required(),
            ])
            ->action(function (Article $record, array $data, StockMouvementService $service) {
                try {
                    $service->create(
                        type: StockMouvementType::ADJUSTEMENT,
                        article: $record,
                        warehouse: Warehouse::findOrFail($data['warehouse_id']),
                        quantity: (float) $data['quantity'],
                        user: auth()->user(),
                        options: [
                            'adjustement_type' => AdjustementType::from($data['adjustement_type']),
                            'reference' => $data['reference'] ?? null,
                            'note' => $data['note'],
                        ],
                    );

                    Notification::make()
                        ->title('Ajustement enregistré')
                        ->body("Stock ajusté de {$data['quantity']} {$record->unit->getAbrv()}.")
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
