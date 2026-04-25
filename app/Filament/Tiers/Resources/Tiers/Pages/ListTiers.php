<?php

namespace App\Filament\Tiers\Resources\Tiers\Pages;

use App\Enums\Tiers\TiersAddressType;
use App\Enums\Tiers\TiersCategory;
use App\Enums\Tiers\TiersTypology;
use App\Filament\Tiers\Resources\Tiers\TiersResource;
use App\Models\Tiers\Tiers;
use App\Models\Tiers\TiersAddress;
use App\Services\Core\SirenService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListTiers extends ListRecords
{
    protected static string $resource = TiersResource::class;
    protected static ?string $title = 'Liste des tiers';
    protected static ?string $breadcrumb = 'Liste des tiers';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau Tier')
                ->icon(Phosphor::PlusCircle),

            Action::make('createWithSiret')
                ->label('Créer par siret')
                ->icon(Phosphor::Globe)
                ->modalHeading('Création du tiers par recherche')
                ->modalDescription('Gagnez du temps en saisissant le numéro de siret du professionnel à créer et pré-remplir sa fiche.')
                ->schema([
                    TextInput::make('siret')
                        ->required()
                        ->label('Siret / Siren'),

                    Select::make('category')
                        ->label('Type de Tiers')
                        ->required()
                        ->options(TiersCategory::class),
                ])
                ->action(function (array $data) {
                    $api = app(SirenService::class);

                    $calling = $api->getInformation($data['siret']);

                    if ($calling['etablissement']) {
                        $tier = Tiers::create([
                            'name' => $calling['etablissement']['uniteLegale']['denominationUniteLegale'],
                            'typology' => TiersTypology::Entreprise->value,
                            'category' => $data['category'],
                            'siren' => $calling['etablissement']['siren'],
                            'naf' => Str::replace('.', '', $calling['etablissement']['activitePrincipaleNAF25Etablissement']),
                        ]);

                        $address = TiersAddress::create([
                            'tiers_id' => $tier->id,
                            'address_name' => 'Default',
                            'address_type' => TiersAddressType::INVOICING->value,
                            'address' => $calling['etablissement']['adresseEtablissement']['numeroVoieEtablissement'].' '.$calling['etablissement']['adresseEtablissement']['typeVoieEtablissement'].' '.$calling['etablissement']['adresseEtablissement']['libelleVoieEtablissement'],
                            'postal_code' => $calling['etablissement']['adresseEtablissement']['codePostalEtablissement'],
                            'city' => $calling['etablissement']['adresseEtablissement']['libelleCommuneEtablissement'],
                            'country' => 'France',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Nouveau tier')
                            ->body("Le Tier {$calling['etablissement']['uniteLegale']['denominationUniteLegale']} à été créer avec succès")
                            ->send();
                    } else {
                        Notification::make()
                            ->info()
                            ->title('Nouveau Tier')
                            ->body("Aucun tiers trouver pour le siret/siren: {$data['siret']}")
                            ->send();
                    }
                }),
        ];
    }
}
