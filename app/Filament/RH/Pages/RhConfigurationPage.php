<?php

namespace App\Filament\RH\Pages;

use App\Enums\RH\JourSemaine;
use App\Models\RH\RhConfiguration;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class RhConfigurationPage extends Page implements HasSchemas
{
    protected string $view = 'filament.r-h.pages.rh-configuration-page';

    protected static ?int $navigationSort = 99;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configuration RH';

    public ?array $data = [];

    public function mount(): void
    {
        $config = RhConfiguration::current();

        $this->form->fill([
            'heures_matin' => $config->heures_matin,
            'heures_aprem' => $config->heures_aprem,
            'jours_travailles' => $config->jours_travailles,
            'prise_en_charge_trajet' => $config->prise_en_charge_trajet,
            'taux_prise_en_charge_trajet' => $config->taux_prise_en_charge_trajet,
            'grand_deplacement_actif' => $config->grand_deplacement_actif,
            'grand_deplacement_montant_jour' => $config->grand_deplacement_montant_jour,
            'grand_deplacement_montant_repas' => $config->grand_deplacement_montant_repas,
            'grand_deplacement_montant_heberg' => $config->grand_deplacement_montant_heberg,
            'panier_repas_actif' => $config->panier_repas_actif,
            'panier_repas_montant' => $config->panier_repas_montant,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Planning & Horaires')
                    ->columns(3)
                    ->schema([
                        TextInput::make('heures_matin')
                            ->label('Heures matin')
                            ->numeric()
                            ->default(3.50)
                            ->suffix('h')
                            ->required(),

                        TextInput::make('heures_aprem')
                            ->label('Heures après-midi')
                            ->numeric()
                            ->default(4.00)
                            ->suffix('h')
                            ->required(),

                        CheckboxList::make('jours_travailles')
                            ->label('Jours travaillés par défaut')
                            ->options(JourSemaine::class)
                            ->default(JourSemaine::defaultJours())
                            ->columns(3)
                            ->columnSpanFull()
                            ->required(),
                    ]),

                Section::make('Prise en charge trajet')
                    ->columns(2)
                    ->schema([
                        Checkbox::make('prise_en_charge_trajet')
                            ->label('Activer la prise en charge des trajets')
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('taux_prise_en_charge_trajet')
                            ->label('Taux de prise en charge')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->visible(fn ($get) => $get('prise_en_charge_trajet')),
                    ]),

                Section::make('Grand déplacement')
                    ->columns(2)
                    ->schema([
                        Checkbox::make('grand_deplacement_actif')
                            ->label('Activer le grand déplacement')
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('grand_deplacement_montant_jour')
                            ->label('Montant forfait journalier')
                            ->numeric()
                            ->suffix('€')
                            ->default(0)
                            ->visible(fn ($get) => $get('grand_deplacement_actif')),

                        TextInput::make('grand_deplacement_montant_repas')
                            ->label('Part repas')
                            ->numeric()
                            ->suffix('€')
                            ->default(0)
                            ->visible(fn ($get) => $get('grand_deplacement_actif')),

                        TextInput::make('grand_deplacement_montant_heberg')
                            ->label('Part hébergement')
                            ->numeric()
                            ->suffix('€')
                            ->default(0)
                            ->visible(fn ($get) => $get('grand_deplacement_actif')),
                    ]),

                Section::make('Panier repas')
                    ->columns(2)
                    ->schema([
                        Checkbox::make('panier_repas_actif')
                            ->label('Activer le panier repas')
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('panier_repas_montant')
                            ->label('Montant forfaitaire')
                            ->numeric()
                            ->suffix('€')
                            ->default(0)
                            ->visible(fn ($get) => $get('panier_repas_actif')),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer')
                ->icon(Phosphor::FloppyDisk)
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        RhConfiguration::current()->update($data);

        Notification::make()
            ->title('Configuration enregistrée')
            ->success()
            ->send();
    }
}
