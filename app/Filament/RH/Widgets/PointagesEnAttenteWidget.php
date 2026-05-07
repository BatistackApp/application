<?php

namespace App\Filament\RH\Widgets;

use App\Enums\RH\PointageStatus;
use App\Models\RH\PointageSession;
use App\Services\RH\PointageService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PointagesEnAttenteWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => PointageSession::with(['employee.user'])
                ->where('status', PointageStatus::SUBMITTED)
                ->latest('submitted_at'))
            ->columns([
                TextColumn::make('employee.user.name')
                    ->label('Salarié')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('employee.matricule')
                    ->label('Matricule')
                    ->searchable(),

                TextColumn::make('label_semaine')
                    ->label('Semaine')
                    ->wrap(),

                TextColumn::make('submitted_at')
                    ->label('Soumis le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('delai')
                    ->label('Délai')
                    ->state(function (PointageSession $record) {
                        $heures = now()->diffInHours($record->submitted_at);
                        if ($heures < 24) {
                            return $heures.' h';
                        }
                        $jours = now()->diffInDays($record->submitted_at);

                        return $jours.' jour'.($jours > 1 ? 's' : '');
                    })
                    ->badge()
                    ->color(function (PointageSession $record) {
                        $heures = now()->diffInHours($record->submitted_at);

                        return match (true) {
                            $heures < 24 => 'success',
                            $heures < 72 => 'warning',
                            default => 'danger',
                        };
                    }),
            ])
            ->recordActions([
                Action::make('validate')
                    ->label('Valider')
                    ->icon(Phosphor::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PointageSession $record, PointageService $service) {
                        try {
                            $service->validate($record, auth()->user());

                            Notification::make()
                                ->title('Pointage validé')
                                ->body('Les coûts ont été imputés aux chantiers.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('view')
                    ->label('Voir')
                    ->icon(Phosphor::Eye)
                    ->url(fn (PointageSession $record) => route(
                        'filament.rh.resources.pointages.view',
                        ['record' => $record]
                    )),
            ])
            ->heading('Pointages en attente de validation')
            ->description('Sessions soumises par les salariés')
            ->emptyStateHeading('Aucun pointage en attente')
            ->emptyStateDescription('Tous les pointages sont validés ou en brouillon')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10]);
    }
}
