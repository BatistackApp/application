<?php

namespace App\Filament\Chantier\Resources\Chantiers\RelationManagers;

use App\Enums\Chantier\ChantierTaskStatus;
use App\Models\Chantier\ChantierTask;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tâches';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('designation')
                            ->label('Désignation')
                            ->required()
                            ->columnSpanFull(),

                        Select::make('parent_task_id')
                            ->label('Tâche parente')
                            ->options(fn () => $this->getOwnerRecord()
                                ->tasks()
                                ->whereNull('parent_task_id')
                                ->pluck('designation', 'id')
                            )
                            ->searchable()
                            ->placeholder('Tâche racine'),

                        Select::make('depends_on_task_id')
                            ->label('Dépend de')
                            ->options(fn () => $this->getOwnerRecord()
                                ->tasks()
                                ->pluck('designation', 'id')
                            )
                            ->searchable()
                            ->placeholder('Aucune dépendance'),

                        Select::make('assignee_id')
                            ->label('Assignée à')
                            ->options(User::pluck('name', 'id'))
                            ->searchable(),

                        Select::make('status')
                            ->label('Statut')
                            ->options(ChantierTaskStatus::class)
                            ->default(ChantierTaskStatus::TODO),
                    ]),

                Section::make('Planification')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        DatePicker::make('date_debut')
                            ->label('Date de début')
                            ->required(),

                        DatePicker::make('date_fin')
                            ->label('Date de fin')
                            ->after('date_debut')
                            ->required(),

                        TextInput::make('avancement_pct')
                            ->label('Avancement')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0),
                    ]),

                Section::make('Budget alloué')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('budgetLines')
                            ->label('Lignes budget liées')
                            ->options(fn () => $this->getOwnerRecord()
                                ->budgetLines()
                                ->get()
                                ->mapWithKeys(fn ($l) => [
                                    $l->id => "[{$l->type->getLabel()}] {$l->designation} — "
                                        .number_format($l->cout_total, 2, ',', ' ').' €',
                                ])
                            )
                            ->multiple()
                            ->searchable()
                            ->helperText('Sélectionnez les lignes budget couvertes par cette tâche'),
                    ]),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(2)
                    ->columnSpanFull(),

                TextInput::make('ordre')
                    ->label('Ordre d\'affichage')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre')
            ->columns([
                TextColumn::make('designation')
                    ->label('Tâche')
                    ->searchable()
                    ->formatStateUsing(fn (ChantierTask $record) => $record->parent_task_id
                        ? '↳ '.$record->designation
                        : $record->designation
                    ),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('assignee.name')
                    ->label('Responsable')
                    ->placeholder('—'),

                TextColumn::make('date_debut')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('date_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (ChantierTask $record) => $record->date_fin->isPast()
                    && $record->status !== ChantierTaskStatus::DONE
                        ? 'danger' : null
                    ),

                TextColumn::make('avancement_pct')
                    ->label('Avancement')
                    ->formatStateUsing(fn (int $state) => $state.' %')
                    ->alignRight(),

                TextColumn::make('budget_alloue')
                    ->label('Budget alloué')
                    ->state(fn (ChantierTask $record) => $record->budget_alloue)
                    ->money('EUR')
                    ->alignRight(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ChantierTaskStatus::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon(Phosphor::PlusCircle)
                    ->label('Ajouter une tâche')
                    ->after(function (ChantierTask $record, array $data) {
                        if (! empty($data['budgetLines'])) {
                            $record->budgetLines()->attach(
                                collect($data['budgetLines'])
                                    ->mapWithKeys(fn ($id) => [$id => ['allocation_pct' => 100]])
                                    ->toArray()
                            );
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function (ChantierTask $record, array $data) {
                        if (isset($data['budgetLines'])) {
                            $record->budgetLines()->sync(
                                collect($data['budgetLines'])
                                    ->mapWithKeys(fn ($id) => [$id => ['allocation_pct' => 100]])
                                    ->toArray()
                            );
                        }
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return $this->getOwnerRecord()->status->value === 'archived';
    }
}
