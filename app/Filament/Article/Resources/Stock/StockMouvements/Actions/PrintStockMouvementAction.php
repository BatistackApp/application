<?php

namespace App\Filament\Article\Resources\Stock\StockMouvements\Actions;

use App\Enums\Article\StockMouvementType;
use App\Models\Article\Article;
use App\Models\Core\Warehouse;
use App\Services\Stock\StockMouvementDocumentGenerator;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PrintStockMouvementAction
{
    public static function make(): Action
    {
        return Action::make('print_stock')
            ->label('Imprimer')
            ->icon(Phosphor::Printer)
            ->color('gray')
            ->modalHeading('Impression des mouvements de stock')
            ->modalDescription('Les filtres actifs de la table ont été repris automatiquement.')
            ->mountUsing(function (array &$arguments, $form) {
                // Pré-remplissage depuis les arguments passés par la page
                $form->fill([
                    'rapport' => 'journal',
                    'date_from' => $arguments['date_from'] ?? now()->startOfMonth()->toDateString(),
                    'date_to' => $arguments['date_to'] ?? now()->toDateString(),
                    'warehouse_id' => $arguments['warehouse_id'] ?? null,
                    'type' => $arguments['type'] ?? null,
                    'article_id' => $arguments['article_id'] ?? null,
                ]);
            })
            ->schema([
                Section::make('Type de rapport')
                    ->schema([
                        Select::make('rapport')
                            ->label('Rapport')
                            ->options([
                                'journal' => 'Journal des mouvements',
                                'recap_article' => 'Récapitulatif par article',
                                'recap_depot' => 'Récapitulatif par dépôt',
                            ])
                            ->default('journal')
                            ->live()
                            ->required(),
                    ]),

                Section::make('Filtres')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('date_from')
                                    ->label('Du')
                                    ->default(now()->startOfMonth())
                                    ->required(),

                                DatePicker::make('date_to')
                                    ->label('Au')
                                    ->default(now())
                                    ->required(),
                            ]),

                        Select::make('warehouse_id')
                            ->label('Dépôt')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->placeholder('Tous les dépôts')
                            ->searchable()
                            ->visible(fn (Get $get) => $get('rapport') !== 'recap_depot'),

                        Select::make('type')
                            ->label('Type de mouvement')
                            ->options(StockMouvementType::class)
                            ->placeholder('Tous les types')
                            ->visible(fn (Get $get) => $get('rapport') === 'journal'),

                        Select::make('article_id')
                            ->label('Article')
                            ->options(Article::orderBy('name')->pluck('name', 'id'))
                            ->placeholder('Tous les articles')
                            ->searchable()
                            ->visible(fn (Get $get) => $get('rapport') === 'journal'),
                    ]),
            ])
            ->action(function (array $data, StockMouvementDocumentGenerator $generator) {
                try {
                    $dateFrom = Carbon::parse($data['date_from']);
                    $dateTo = Carbon::parse($data['date_to']);

                    $path = match ($data['rapport']) {
                        'journal' => $generator->journal(
                            dateFrom: $dateFrom,
                            dateTo: $dateTo,
                            warehouseId: $data['warehouse_id'] ?? null,
                            type: $data['type'] ?? null,
                            articleId: $data['article_id'] ?? null,
                        ),
                        'recap_article' => $generator->recapArticle(
                            dateFrom: $dateFrom,
                            dateTo: $dateTo,
                            warehouseId: $data['warehouse_id'] ?? null,
                        ),
                        'recap_depot' => $generator->recapDepot(
                            dateFrom: $dateFrom,
                            dateTo: $dateTo,
                        ),
                    };

                    return response()->download($path);

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Erreur de génération')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
