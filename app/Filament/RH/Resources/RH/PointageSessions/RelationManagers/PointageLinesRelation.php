<?php

namespace App\Filament\RH\Resources\RH\PointageSessions\RelationManagers;

use App\Enums\RH\PointageStatus;
use App\Enums\RH\TypeHeure;
use App\Models\Chantier\Chantier;
use App\Models\RH\PointageLine;
use App\Models\RH\RhConfiguration;
use App\Services\RH\PointageService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PointageLinesRelation extends RelationManager
{
    protected static string $relationship = 'lines';

    protected static ?string $title = 'Lignes de pointage';

    public function form(Schema $schema): Schema
    {
        $config = RhConfiguration::current();

        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('chantier_id')
                            ->label('Chantier')
                            ->options(
                                Chantier::enCours()
                                    ->orderBy('reference')
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [
                                        $c->id => "[{$c->reference}] {$c->nom}",
                                    ])
                            )
                            ->searchable()
                            ->placeholder('Absence / Congés')
                            ->nullable(),

                        Select::make('type_heure')
                            ->label('Type d\'heure')
                            ->options(TypeHeure::class)
                            ->default(TypeHeure::NORMALE)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, string $state) {
                                // Si absence, vide le chantier
                                if (in_array($state, [
                                    TypeHeure::CONGES->value,
                                    TypeHeure::MALADIE->value,
                                ])) {
                                    $set('chantier_id', null);
                                }
                            }),

                        TextInput::make('heures')
                            ->label('Heures')
                            ->numeric()
                            ->suffix('h')
                            ->default($config->heures_matin)
                            ->minValue(0)
                            ->maxValue(24)
                            ->required(),
                    ]),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        // Trajet (visible si config active)
                        TextInput::make('heures_trajet')
                            ->label('Heures de trajet')
                            ->numeric()
                            ->suffix('h')
                            ->default(0)
                            ->minValue(0)
                            ->visible($config->prise_en_charge_trajet),

                        // Grand déplacement (visible si config active)
                        Checkbox::make('grand_deplacement')
                            ->label('Grand déplacement')
                            ->live()
                            ->afterStateUpdated(function (Set $set, bool $state) {
                                if ($state) {
                                    $set('panier_repas', false);
                                }
                            })
                            ->visible($config->grand_deplacement_actif),

                        // Panier repas (visible si config active)
                        Checkbox::make('panier_repas')
                            ->label('Panier repas')
                            ->live()
                            ->afterStateUpdated(function (Set $set, bool $state) {
                                if ($state) {
                                    $set('grand_deplacement', false);
                                }
                            })
                            ->visible($config->panier_repas_actif),
                    ]),

                Textarea::make('note')
                    ->label('Note')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultGroup(
                Group::make('date')
                    ->label('Date')
                    ->date('l d/m/Y')
                    ->collapsible()
                    ->orderQueryUsing(
                        fn ($query, string $direction) => $query->orderBy('date', $direction)
                    )
            )
            ->defaultSort('date')
            ->columns([
                TextColumn::make('periode')
                    ->label('Période')
                    ->badge()
                    ->sortable(),

                TextColumn::make('chantier.nom')
                    ->label('Chantier')
                    ->description(fn (PointageLine $record) => $record->chantier?->reference)
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('type_heure')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('heures')
                    ->label('Heures')
                    ->suffix('h')
                    ->alignRight()
                    ->weight('bold'),

                TextColumn::make('heures_trajet')
                    ->label('Trajet')
                    ->suffix('h')
                    ->alignRight()
                    ->placeholder('—')
                    ->visible(fn () => RhConfiguration::current()->prise_en_charge_trajet),

                IconColumn::make('panier_repas')
                    ->label('Panier')
                    ->boolean()
                    ->visible(fn () => RhConfiguration::current()->panier_repas_actif),

                IconColumn::make('grand_deplacement')
                    ->label('GD')
                    ->boolean()
                    ->visible(fn () => RhConfiguration::current()->grand_deplacement_actif),
            ])
            ->recordActions([
                Action::make('saisir')
                    ->label('Saisir')
                    ->icon(Phosphor::PencilSimple)
                    ->color('primary')
                    ->visible(fn () => in_array(
                        $this->getOwnerRecord()->status,
                        [PointageStatus::DRAFT, PointageStatus::REJECTED],
                    ))
                    ->mountUsing(function (Schema $schema, PointageLine $record) {
                        $schema->fill([
                            'chantier_id' => $record->chantier_id,
                            'type_heure' => $record->type_heure->value,
                            'heures' => $record->heures,
                            'heures_trajet' => $record->heures_trajet,
                            'panier_repas' => $record->panier_repas,
                            'grand_deplacement' => $record->grand_deplacement,
                            'note' => $record->note,
                        ]);
                    })
                    ->schema(fn () => $this->form($this->getTableSchema()))
                    ->action(function (PointageLine $record, array $data, PointageService $service) {
                        $service->saveLine($record, $data);
                    }),
            ]);
    }

    public function isReadOnly(): bool
    {
        return ! in_array(
            $this->getOwnerRecord()->status,
            [PointageStatus::DRAFT, PointageStatus::REJECTED],
        );
    }
}
