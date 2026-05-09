<?php

namespace App\Services\Compta;

use App\Models\Compta\DeclarationTva;
use App\Models\Compta\Ecriture;
use App\Models\Compta\ExerciceComptable;
use App\Models\Compta\Journal;
use App\Services\Core\DocumentService;

class ComptaDocumentGenerator extends DocumentService
{
    public function __construct(
        protected GrandLivreService $grandLivreService,
        protected BalanceService $balanceService,
        protected DeclarationTvaService $declarationTvaService
    ) {}

    /**
     * Génère le PDF d'une écriture comptable.
     */
    public function generateEcriturePdf(Ecriture $ecriture): string
    {
        $data = [
            'ecriture' => $ecriture->load(['journal', 'exercice', 'lignes.compte', 'lignes.chantier']),
            'entreprise' => $this->getEntrepriseInfo(),
        ];

        return $this->generate('pdf.compta.ecriture', $data, "ecriture_{$ecriture->numero_piece}.pdf", 'compta');
    }

    /**
     * Génère le PDF du grand livre.
     */
    public function generateGrandLivrePdf(
        ExerciceComptable $exercice,
        ?\DateTimeInterface $dateDebut = null,
        ?\DateTimeInterface $dateFin = null
    ): string {
        $grandLivre = $this->grandLivreService->generer($exercice, $dateDebut, $dateFin);

        $data = [
            'exercice' => $exercice,
            'grandLivre' => $grandLivre,
            'dateDebut' => $dateDebut ?? $exercice->date_debut,
            'dateFin' => $dateFin ?? $exercice->date_fin,
            'entreprise' => $this->getEntrepriseInfo(),
        ];

        $filename = 'grand_livre_'.$exercice->libelle.'_'.now()->format('YmdHis').'.pdf';

        return $this->generate('pdf.compta.grand-livre', $data, $filename, 'compta', 'landscape');
    }

    /**
     * Génère le PDF de la balance comptable.
     */
    public function generateBalancePdf(
        ExerciceComptable $exercice,
        ?\DateTimeInterface $dateDebut = null,
        ?\DateTimeInterface $dateFin = null
    ): string {
        $balance = $this->balanceService->genererBalanceGenerale($exercice, $dateDebut, $dateFin);
        $verification = $this->balanceService->verifierEquilibre($balance);

        $data = [
            'exercice' => $exercice,
            'balance' => $balance,
            'verification' => $verification,
            'dateDebut' => $dateDebut ?? $exercice->date_debut,
            'dateFin' => $dateFin ?? $exercice->date_fin,
            'entreprise' => $this->getEntrepriseInfo(),
        ];

        $filename = 'balance_'.$exercice->libelle.'_'.now()->format('YmdHis').'.pdf';

        return $this->generate('pdf.compta.balance', $data, $filename, 'compta', 'landscape');
    }

    /**
     * Génère le PDF d'une déclaration de TVA (CA3).
     */
    public function generateDeclarationTvaPdf(DeclarationTva $declaration): string
    {
        $ca3Data = $this->declarationTvaService->exporterCa3($declaration);

        $data = [
            'declaration' => $declaration->load('exercice'),
            'ca3' => $ca3Data,
            'entreprise' => $this->getEntrepriseInfo(),
        ];

        return $this->generate(
            'pdf.compta.declaration-tva',
            $data,
            "ca3_{$declaration->periode}.pdf",
            'compta'
        );
    }

    /**
     * Génère le PDF de la balance auxiliaire clients.
     */
    public function generateBalanceClientsPdf(ExerciceComptable $exercice): string
    {
        $balance = $this->balanceService->genererBalanceAuxiliaire($exercice, '411');

        $data = [
            'exercice' => $exercice,
            'balance' => $balance,
            'type' => 'Clients',
            'entreprise' => $this->getEntrepriseInfo(),
        ];

        $filename = 'balance_clients_'.$exercice->libelle.'_'.now()->format('YmdHis').'.pdf';

        return $this->generate('pdf.compta.balance-auxiliaire', $data, $filename, 'compta');
    }

    /**
     * Génère le PDF de la balance auxiliaire fournisseurs.
     */
    public function generateBalanceFournisseursPdf(ExerciceComptable $exercice): string
    {
        $balance = $this->balanceService->genererBalanceAuxiliaire($exercice, '401');

        $data = [
            'exercice' => $exercice,
            'balance' => $balance,
            'type' => 'Fournisseurs',
            'entreprise' => $this->getEntrepriseInfo(),
        ];

        $filename = 'balance_fournisseurs_'.$exercice->libelle.'_'.now()->format('YmdHis').'.pdf';

        return $this->generate('pdf.compta.balance-auxiliaire', $data, $filename, 'compta');
    }

    /**
     * Génère le PDF du journal comptable.
     */
    public function generateJournalPdf(
        ExerciceComptable $exercice,
        int $journalId,
        ?\DateTimeInterface $dateDebut = null,
        ?\DateTimeInterface $dateFin = null
    ): string {
        $journal = Journal::findOrFail($journalId);

        $ecritures = Ecriture::with(['lignes.compte'])
            ->where('exercice_comptable_id', $exercice->id)
            ->where('journal_id', $journalId)
            ->valide()
            ->when($dateDebut, fn ($q) => $q->where('date_ecriture', '>=', $dateDebut))
            ->when($dateFin, fn ($q) => $q->where('date_ecriture', '<=', $dateFin))
            ->orderBy('date_ecriture')
            ->orderBy('numero_piece')
            ->get();

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($ecritures as $ecriture) {
            $totalDebit += $ecriture->total_debit;
            $totalCredit += $ecriture->total_credit;
        }

        $data = [
            'exercice' => $exercice,
            'journal' => $journal,
            'ecritures' => $ecritures,
            'dateDebut' => $dateDebut ?? $exercice->date_debut,
            'dateFin' => $dateFin ?? $exercice->date_fin,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'entreprise' => $this->getEntrepriseInfo(),
        ];

        $filename = "journal_{$journal->code}_{$exercice->libelle}_".now()->format('YmdHis').'.pdf';

        return $this->generate('pdf.compta.journal', $data, $filename, 'compta', 'landscape');
    }

    /**
     * Informations de l'entreprise.
     */
    protected function getEntrepriseInfo(): array
    {
        return [
            'nom' => config('app.name', 'C2ME'),
            'adresse' => "Zone Industrielle\n49450 Saint-Macaire-en-Mauges",
            'siret' => '123 456 789 00012',
            'tva' => 'FR12 123456789',
        ];
    }
}
