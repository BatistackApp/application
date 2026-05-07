<?php

namespace App\Filament\Article\Widgets;

use App\Models\Article\Article;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class StockAlertsWidget extends TableWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Article::query()
                ->select('articles.*')
                ->join('article_warehouse', 'articles.id', '=', 'article_warehouse.article_id')
                ->whereRaw('article_warehouse.actual_stock <= article_warehouse.alert_stock')
                ->where('article_warehouse.actual_stock', '>', 0)
                ->groupBy('articles.id'))
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Article')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('warehouses')
                    ->label('Dépôts en alerte')
                    ->state(function (Article $record) {
                        return $record->warehouses()
                            ->wherePivot('actual_stock', '<=', \DB::raw('article_warehouse.alert_stock'))
                            ->wherePivot('actual_stock', '>', 0)
                            ->get()
                            ->map(fn ($w) => "{$w->name} : {$w->pivot->actual_stock}/{$w->pivot->alert_stock}")
                            ->join(', ');
                    })
                    ->wrap()
                    ->color('danger'),

                TextColumn::make('stock_total')
                    ->label('Stock total')
                    ->state(fn (Article $record) => $record->warehouses->sum('pivot.actual_stock'))
                    ->suffix(fn (Article $record) => ' '.$record->unit->getAbrv()),
            ])
            ->heading('Articles en alerte stock')
            ->description('Articles dont le stock est inférieur ou égal au seuil d\'alerte')
            ->emptyStateHeading('Aucune alerte stock')
            ->emptyStateDescription('Tous les articles sont au-dessus du seuil d\'alerte')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10]);
    }
}
