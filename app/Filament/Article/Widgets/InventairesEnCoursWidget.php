<?php

namespace App\Filament\Article\Widgets;

use App\Enums\Article\InventorySessionStatus;
use App\Models\Stock\InventorySession;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class InventairesEnCoursWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => InventorySession::with(['warehouse', 'user'])
                ->whereIn('status', [
                    InventorySessionStatus::OPEN,
                    InventorySessionStatus::COUNTING,
                ])
                ->latest())
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('warehouse.name')
                    ->label('Dépôt')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Date ouverture')
                    ->date('d/m/Y'),

                TextColumn::make('progression')
                    ->label('Progression')
                    ->state(function (InventorySession $record) {
                        $total = $record->lines()->count();
                        $comptes = $record->lines()->whereNotNull('counted_quantity')->count();

                        if ($total === 0) {
                            return '0 %';
                        }

                        $pct = round(($comptes / $total) * 100);

                        return "{$comptes}/{$total} ({$pct}%)";
                    }),

                TextColumn::make('user.name')
                    ->label('Créé par')
                    ->placeholder('—'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Voir')
                    ->icon(Phosphor::Eye)
                    ->url(fn (InventorySession $record) => route(
                        'filament.article.resources.stock.inventory-sessions.view',
                        ['record' => $record]
                    )),
            ])
            ->heading('Inventaires en cours')
            ->description('Sessions ouvertes ou en comptage')
            ->emptyStateHeading('Aucun inventaire en cours')
            ->emptyStateDescription('Tous les inventaires sont terminés ou validés')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->paginated([5]);
    }
}
