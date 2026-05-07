<?php

namespace App\Filament\Article\Widgets;

use App\Models\Stock\StockMouvement;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MouvementsRecentsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => StockMouvement::with(['article', 'warehouse', 'user'])
                ->latest()
                ->limit(10))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('article.name')
                    ->label('Article')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('warehouse.name')
                    ->label('Dépôt')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('quantity')
                    ->label('Quantité')
                    ->state(fn (StockMouvement $record) => $record->quantity.' '.$record->article->unit->getAbrv()
                    )
                    ->alignRight()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->placeholder('—'),
            ])
            ->heading('Mouvements récents')
            ->description('Les 10 derniers mouvements de stock')
            ->paginated(false);
    }
}
