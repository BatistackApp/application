<?php

namespace App\Filament\Tiers\Widgets;

use App\Models\Chantier\Chantier;
use App\Models\Tiers\Tiers;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class TopClientsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        // Agrégation des clients par nombre de chantiers
        $clientsChantiers = Chantier::select('client_id')
            ->selectRaw('COUNT(*) as nb_chantiers')
            ->groupBy('client_id')
            ->orderByDesc('nb_chantiers')
            ->limit(10)
            ->pluck('nb_chantiers', 'client_id');

        if ($clientsChantiers->isEmpty()) {
            return $table
                ->query(Tiers::query()->whereRaw('1 = 0'))
                ->columns([
                    TextColumn::make('name')->label('Client'),
                ])
                ->heading('Top 10 clients par chantiers')
                ->emptyStateHeading('Aucun chantier enregistré')
                ->emptyStateIcon('heroicon-o-building-office-2')
                ->paginated(false);
        }

        $ids = $clientsChantiers->keys()->toArray();

        return $table
            ->query(
                Tiers::query()
                    ->whereIn('id', $ids)
                    ->orderByRaw('FIELD(id, '.implode(',', $ids).')')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Client')
                    ->searchable()
                    ->weight('bold')
                    ->limit(40),

                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge(),

                TextColumn::make('email')
                    ->label('Email')
                    ->placeholder('—')
                    ->limit(30),

                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->placeholder('—'),

                TextColumn::make('nb_chantiers')
                    ->label('Chantiers')
                    ->state(fn (Tiers $record) => $clientsChantiers[$record->id] ?? 0)
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Voir')
                    ->icon(Phosphor::Eye)
                    ->url(fn (Tiers $record) => route(
                        'filament.tiers.resources.tiers.view',
                        ['record' => $record]
                    )),
            ])
            ->heading('Top 10 clients par nombre de chantiers')
            ->description('Clients les plus actifs')
            ->paginated(false);
    }
}
