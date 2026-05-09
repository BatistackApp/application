<?php

namespace App\Services\Compta;

use App\Enums\Compta\CompteSens;
use App\Enums\Compta\JournalType;
use App\Models\Commerce\CommercialDocument;
use App\Models\Commerce\Paiement;
use App\Models\Compta\Journal;
use App\Models\Compta\PlanComptable;
use App\Models\RH\PointageSession;
use App\Services\RH\PointageCoutCalculator;

class ComptabilisationService
{
    public function __construct(
        protected EcritureService $ecritureService
    ) {}

    /**
     * Comptabilise une facture client.
     * @throws \Throwable
     */
    public function comptabiliserFactureClient(CommercialDocument $facture): void
    {
        if (! $facture->isFacture()) {
            throw new \InvalidArgumentException('Document non facturable.');
        }

        // Journal des ventes
        $journal = Journal::where('type', JournalType::VENTES)->firstOrFail();

        // Comptes
        $compteClient = PlanComptable::where('numero', '411000')->firstOrFail();
        $compteProduit = PlanComptable::where('numero', '707000')->firstOrFail();
        $compteTvaCollectee = PlanComptable::where('numero', '445710')->firstOrFail();

        $lignes = [];

        // Débit client TTC
        $lignes[] = [
            'compte_id' => $compteClient->id,
            'sens' => CompteSens::DEBIT->value,
            'montant' => $facture->total_ttc,
            'libelle' => "Client {$facture->client->name} - Facture {$facture->reference}",
        ];

        // Crédit produit HT
        $lignes[] = [
            'compte_id' => $compteProduit->id,
            'sens' => CompteSens::CREDIT->value,
            'montant' => $facture->total_ht,
            'libelle' => "Vente - Facture {$facture->reference}",
            'chantier_id' => $facture->chantier_id,
        ];

        // Crédit TVA collectée
        if ($facture->total_tva > 0) {
            $lignes[] = [
                'compte_id' => $compteTvaCollectee->id,
                'sens' => CompteSens::CREDIT->value,
                'montant' => $facture->total_tva,
                'libelle' => "TVA collectée - Facture {$facture->reference}",
            ];
        }

        $this->ecritureService->create(
            $journal,
            $facture->date_document,
            "Facture client {$facture->reference}",
            $lignes,
            $facture
        );
    }

    /**
     * Comptabilise un encaissement client.
     * @throws \Throwable
     */
    public function comptabiliserEncaissement(Paiement $paiement): void
    {
        $facture = $paiement->facture;

        // Journal banque ou caisse
        $journal = Journal::where('type', JournalType::BANQUE)->firstOrFail();

        // Comptes
        $compteBanque = PlanComptable::where('numero', '512000')->firstOrFail();
        $compteClient = PlanComptable::where('numero', '411000')->firstOrFail();

        $lignes = [
            // Débit banque
            [
                'compte_id' => $compteBanque->id,
                'sens' => CompteSens::DEBIT->value,
                'montant' => $paiement->montant,
                'libelle' => "Encaissement {$facture->reference} - {$paiement->mode_paiement->getLabel()}",
            ],
            // Crédit client
            [
                'compte_id' => $compteClient->id,
                'sens' => CompteSens::CREDIT->value,
                'montant' => $paiement->montant,
                'libelle' => "Règlement client {$facture->client->name} - {$facture->reference}",
            ],
        ];

        $this->ecritureService->create(
            $journal,
            $paiement->date_paiement,
            "Encaissement facture {$facture->reference}",
            $lignes,
            $paiement
        );
    }

    /**
     * Comptabilise les salaires d'une session de pointage.
     * @throws \Throwable
     */
    public function comptabiliserSalaires(PointageSession $session): void
    {
        $calculator = app(PointageCoutCalculator::class);
        $couts = $calculator->getCoutSession($session);

        // Journal paie
        $journal = Journal::where('type', JournalType::PAIE)->firstOrFail();

        // Comptes
        $compteChargeSalaire = PlanComptable::where('numero', '641000')->firstOrFail();
        $comptePersonnel = PlanComptable::where('numero', '421000')->firstOrFail();

        $montantTotal = $couts['main_oeuvre'] + $couts['trajet'];

        $lignes = [
            // Débit charges de personnel
            [
                'compte_id' => $compteChargeSalaire->id,
                'sens' => CompteSens::DEBIT->value,
                'montant' => $montantTotal,
                'libelle' => "Salaires {$session->employee->user->name} - Semaine {$session->label_semaine}",
            ],
            // Crédit personnel - rémunérations dues
            [
                'compte_id' => $comptePersonnel->id,
                'sens' => CompteSens::CREDIT->value,
                'montant' => $montantTotal,
                'libelle' => "Salaire à payer {$session->employee->user->name}",
            ],
        ];

        $this->ecritureService->create(
            $journal,
            now(),
            "Paie {$session->employee->user->name} - Semaine du {$session->semaine_du->format('d/m/Y')}",
            $lignes,
            $session
        );

        // Ventilation analytique par chantier
        if (! empty($couts['par_chantier'])) {
            $this->comptabiliserVentilationAnalytique($session, $couts['par_chantier']);
        }
    }

    /**
     * Ventilation analytique des coûts de main d'œuvre par chantier.
     */
    protected function comptabiliserVentilationAnalytique(PointageSession $session, array $coutParChantier): void
    {
        $journal = Journal::where('type', JournalType::OPERATIONS_DIVERSES)->firstOrFail();
        $compteCharge = PlanComptable::where('numero', '641000')->firstOrFail();

        foreach ($coutParChantier as $chantierId => $cout) {
            $lignes = [
                // Débit charge analytique (chantier)
                [
                    'compte_id' => $compteCharge->id,
                    'sens' => CompteSens::DEBIT->value,
                    'montant' => $cout['total'],
                    'libelle' => "Affectation MO chantier {$cout['chantier']->reference}",
                    'chantier_id' => $chantierId,
                ],
                // Crédit charge globale
                [
                    'compte_id' => $compteCharge->id,
                    'sens' => CompteSens::CREDIT->value,
                    'montant' => $cout['total'],
                    'libelle' => 'Ventilation analytique',
                ],
            ];

            $this->ecritureService->create(
                $journal,
                now(),
                "Analytique MO - {$cout['chantier']->reference}",
                $lignes
            );
        }
    }

    /**
     * Comptabilise une facture fournisseur (exemple simplifié).
     * @throws \Throwable
     */
    public function comptabiliserFactureFournisseur(
        \DateTimeInterface $date,
        string $fournisseur,
        float $montantHt,
        float $montantTva,
        ?int $chantierId = null
    ): void {
        $journal = Journal::where('type', JournalType::ACHATS)->firstOrFail();

        $compteFournisseur = PlanComptable::where('numero', '401000')->firstOrFail();
        $compteAchat = PlanComptable::where('numero', '607000')->firstOrFail();
        $compteTvaDeductible = PlanComptable::where('numero', '445660')->firstOrFail();

        $lignes = [
            // Débit charge
            [
                'compte_id' => $compteAchat->id,
                'sens' => CompteSens::DEBIT->value,
                'montant' => $montantHt,
                'libelle' => "Achat {$fournisseur}",
                'chantier_id' => $chantierId,
            ],
            // Débit TVA déductible
            [
                'compte_id' => $compteTvaDeductible->id,
                'sens' => CompteSens::DEBIT->value,
                'montant' => $montantTva,
                'libelle' => "TVA déductible {$fournisseur}",
            ],
            // Crédit fournisseur TTC
            [
                'compte_id' => $compteFournisseur->id,
                'sens' => CompteSens::CREDIT->value,
                'montant' => $montantHt + $montantTva,
                'libelle' => "Fournisseur {$fournisseur}",
            ],
        ];

        $this->ecritureService->create(
            $journal,
            $date,
            "Facture fournisseur {$fournisseur}",
            $lignes
        );
    }
}
