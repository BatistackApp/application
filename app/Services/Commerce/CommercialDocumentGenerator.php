<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\DocumentType;
use App\Models\Commerce\CommercialDocument;
use App\Services\Core\DocumentService;

class CommercialDocumentGenerator extends DocumentService
{
    public function __construct(
        protected CommercialCalculator $calculator
    ) {}

    /**
     * Génère le PDF d'un document commercial.
     */
    public function generatePdf(CommercialDocument $document): string
    {
        $view = $this->getViewForType($document->type);
        $data = $this->prepareData($document);
        $filename = $this->getFilename($document);

        return $this->generate($view, $data, $filename, 'commerce');
    }

    /**
     * Retourne la vue Blade correspondant au type de document.
     */
    protected function getViewForType(DocumentType $type): string
    {
        return match ($type) {
            DocumentType::DEVIS            => 'pdf.commerce.devis',
            DocumentType::BON_COMMANDE     => 'pdf.commerce.bon-commande',
            DocumentType::BON_LIVRAISON    => 'pdf.commerce.bon-livraison',
            DocumentType::FACTURE          => 'pdf.commerce.facture',
            DocumentType::FACTURE_ACOMPTE  => 'pdf.commerce.facture-acompte',
            DocumentType::AVOIR            => 'pdf.commerce.avoir',
        };
    }

    /**
     * Génère le nom du fichier.
     */
    protected function getFilename(CommercialDocument $document): string
    {
        $prefix = strtolower($document->type->getLabel());
        $prefix = str_replace([' ', "'"], ['-', ''], $prefix);

        return "{$prefix}_{$document->reference}.pdf";
    }

    /**
     * Prépare les données pour le PDF.
     */
    protected function prepareData(CommercialDocument $document): array
    {
        $totaux = $this->calculator->calculateDocumentTotals($document);

        return [
            'document'         => $document->load(['client.addresses', 'chantier', 'lines.article', 'lines.ouvrage']),
            'totaux'           => $totaux,
            'entreprise'       => $this->getEntrepriseInfo(),
            'conditions'       => $this->getConditionsReglement($document),
            'mentions_legales' => $this->getMentionsLegales($document),
        ];
    }

    /**
     * Informations de l'entreprise.
     */
    protected function getEntrepriseInfo(): array
    {
        return [
            'nom'           => config('app.name', 'C2ME'),
            'adresse'       => "Zone Industrielle\n49450 Saint-Macaire-en-Mauges",
            'telephone'     => '02 41 XX XX XX',
            'email'         => 'contact@c2me.fr',
            'siret'         => '123 456 789 00012',
            'tva'           => 'FR12 123456789',
            'rcs'           => 'Angers B 123 456 789',
            'capital'       => '50 000 €',
            'logo_url'      => asset('images/logo.png'),
        ];
    }

    /**
     * Conditions de règlement selon le type de document.
     */
    protected function getConditionsReglement(CommercialDocument $document): string
    {
        if ($document->conditions_reglement) {
            return $document->conditions_reglement;
        }

        return match ($document->type) {
            DocumentType::DEVIS => 'Devis valable 30 jours. Acompte de 30% à la commande.',
            DocumentType::FACTURE, DocumentType::FACTURE_ACOMPTE => 'Paiement à 30 jours fin de mois. Pénalités de retard : 3 fois le taux d\'intérêt légal.',
            default => '',
        };
    }

    /**
     * Mentions légales selon le type de document.
     */
    protected function getMentionsLegales(CommercialDocument $document): array
    {
        $mentions = [];

        if (in_array($document->type, [DocumentType::FACTURE, DocumentType::FACTURE_ACOMPTE])) {
            $mentions[] = 'En cas de retard de paiement, indemnité forfaitaire pour frais de recouvrement : 40 €';
            $mentions[] = 'Escompte pour paiement anticipé : néant';
        }

        if ($document->type === DocumentType::DEVIS) {
            $mentions[] = 'Devis gratuit ne valant pas engagement';
            $mentions[] = 'Bon pour accord et signature (précédé de la mention "Lu et approuvé")';
        }

        return $mentions;
    }
}
