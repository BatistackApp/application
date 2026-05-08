<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\RelationManagers;

use App\Services\Commerce\RelanceService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RelancesRelationManager extends RelationManager
{
    protected static string $relationship = 'relances';

    protected static ?string $title = 'Historique des relances';

    public function isReadOnly(): bool
    {
        return ! $this->getOwnerRecord()->isFacture();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        DatePicker::make('date_relance')
                            ->label('Date de relance')
                            ->default(now())
                            ->required(),

                        Select::make('type')
                            ->label('Type de relance')
                            ->options([
                                'email' => 'Email',
                                'courrier' => 'Courrier',
                                'appel' => 'Appel téléphonique',
                            ])
                            ->required()
                            ->default('email'),
                    ]),

                Textarea::make('contenu')
                    ->label('Contenu de la relance')
                    ->required()
                    ->rows(4)
                    ->default(fn () => $this->getDefaultContenu()),

                Textarea::make('reponse_client')
                    ->label('Réponse du client')
                    ->rows(3)
                    ->placeholder('Réponse reçue du client...'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date_relance')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'info' => 'email',
                        'warning' => 'courrier',
                        'success' => 'appel',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'email' => 'Email',
                        'courrier' => 'Courrier',
                        'appel' => 'Appel',
                        default => $state,
                    }),

                TextColumn::make('contenu')
                    ->label('Contenu')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('reponse_client')
                    ->label('Réponse client')
                    ->placeholder('Aucune réponse')
                    ->limit(40),

                TextColumn::make('user.name')
                    ->label('Envoyée par')
                    ->placeholder('Système'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter une relance')
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('date_relance', 'desc');
    }

    protected function getDefaultContenu(): string
    {
        $document = $this->getOwnerRecord();
        $joursRetard = app(RelanceService::class)->getJoursRetard($document);
        $solde = number_format($document->solde, 2, ',', ' ');

        return "Madame, Monsieur,\n\n"
            ."Sauf erreur de notre part, la facture {$document->reference} d'un montant de {$solde} € "
            ."reste impayée à ce jour (retard de {$joursRetard} jour(s)).\n\n"
            ."Nous vous remercions de bien vouloir procéder au règlement dans les meilleurs délais.\n\n"
            ."Cordialement";
    }
}
