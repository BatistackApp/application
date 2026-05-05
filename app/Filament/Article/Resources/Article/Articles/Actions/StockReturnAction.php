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
use Illuminate\Validation\ValidationException;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class StockReturnAction
{
    public static function make(): Action
    {
        return Action::make('stock_return')
            ->label('Retour')
            ->icon(Phosphor::ArrowUUpLeft)
            ->color('primary')
            ->modalHeading(fn (Article $record) => "Retour de stock — {$record->name}")
            ->modalDescription('Enregistre un retour de matériel depuis un chantier ou un client.')
            ->schema(fn (Article $record) => [
                Grid::make(2)
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Dépôt de retour')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
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
                        TextInput::make('reference')
                            ->label('Référence')
                            ->placeholder('N° chantier, BL retour...'),

                        Textarea::make('note')
                            ->label('Note')
                            ->rows(2),
                    ]),
            ])
            ->action(function (Article $record, array $data, StockMouvementService $service) {
                try {
                    $service->create(
                        type: StockMouvementType::RETURN,
                        article: $record,
                        warehouse: Warehouse::find($data['warehouse_id']),
                        quantity: (float) $data['quantity'],
                        user: auth()->user(),
                        options: array_filter([
                            'reference' => $data['reference'] ?? null,
                            'note' => $data['note'] ?? null,
                        ]),
                    );

                    Notification::make()
                        ->title('Retour enregistré')
                        ->body("+ {$data['quantity']} {$record->unit->getAbrv()} retournés au dépôt.")
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
