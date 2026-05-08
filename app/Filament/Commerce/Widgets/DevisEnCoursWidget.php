<?php

namespace App\Filament\Commerce\Widgets;

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\DocumentType;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\CommercialDocumentResource;
use App\Models\Commerce\CommercialDocument;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DevisEnCoursWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CommercialDocument::with(['client', 'chantier'])
                    ->where('type', DocumentType::DEVIS)
                    ->where('status', DocumentStatus::SENT)
                    ->orderBy('date_validite')
            )
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('client.name')
                    ->label('Client')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('chantier.reference')
                    ->label('Chantier')
                    ->placeholder('—'),

                TextColumn::make('date_document')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('date_validite')
                    ->label('Validité')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (CommercialDocument $record) => $record->date_validite?->isPast() ? 'danger' : 'warning'),

                TextColumn::make('delai_validite')
                    ->label('Délai')
                    ->state(function (CommercialDocument $record) {
                        if (! $record->date_validite) {
                            return '—';
                        }
                        if ($record->date_validite->isPast()) {
                            return 'Expiré';
                        }
                        $jours = now()->diffInDays($record->date_validite);

                        return "J-{$jours}";
                    })
                    ->badge()
                    ->color(function (CommercialDocument $record) {
                        if (! $record->date_validite || $record->date_validite->isPast()) {
                            return 'danger';
                        }
                        $jours = now()->diffInDays($record->date_validite);

                        return match (true) {
                            $jours <= 5 => 'danger',
                            $jours <= 15 => 'warning',
                            default => 'success',
                        };
                    }),

                TextColumn::make('total_ttc')
                    ->label('Montant TTC')
                    ->money('EUR')
                    ->weight('bold')
                    ->alignRight(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Voir')
                    ->icon(Phosphor::Eye)
                    ->url(fn (CommercialDocument $record) => CommercialDocumentResource::getUrl('view', ['record' => $record])),
            ])
            ->heading('Devis en attente de réponse')
            ->description('Devis envoyés non encore acceptés ou refusés')
            ->emptyStateHeading('Aucun devis en attente')
            ->emptyStateIcon('heroicon-o-document-text')
            ->paginated([5, 10]);
    }
}
