<?php

namespace App\Filament\Tiers\Widgets;

use App\Models\Tiers\Tiers;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class TiersRecentWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tiers::with(['addresses'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->weight('bold')
                    ->limit(40),

                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->sortable(),

                TextColumn::make('typology')
                    ->label('Type')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('email')
                    ->label('Email')
                    ->placeholder('—')
                    ->limit(30),

                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->placeholder('—'),

                TextColumn::make('addresses')
                    ->label('Ville')
                    ->state(fn (Tiers $record) => $record->addresses->first()?->city)
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable(),
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
            ->heading('Tiers récents')
            ->description('Les 10 derniers tiers créés')
            ->paginated(false);
    }
}
