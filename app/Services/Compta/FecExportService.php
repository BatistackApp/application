<?php

namespace App\Services\Compta;

use App\Models\Compta\ExerciceComptable;
use App\Models\Compta\LigneEcriture;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FecExportService
{
    /**
     * Génère le fichier FEC pour un exercice.
     *
     * Format FEC : fichier texte avec séparateur pipe (|) ou tabulation.
     * Norme : BOI-CF-IOR-60-40-20 du 03/09/2013
     */
    public function generer(ExerciceComptable $exercice): string
    {
        // Récupérer toutes les lignes d'écritures validées
        $lignes = LigneEcriture::with(['ecriture.journal', 'compte'])
            ->whereHas('ecriture', function ($query) use ($exercice) {
                $query->where('exercice_comptable_id', $exercice->id)
                    ->valide();
            })
            ->orderBy('ecriture_id')
            ->orderBy('ordre')
            ->get();

        // En-tête FEC (18 colonnes obligatoires)
        $header = implode('|', [
            'JournalCode',
            'JournalLib',
            'EcritureNum',
            'EcritureDate',
            'CompteNum',
            'CompteLib',
            'CompAuxNum',
            'CompAuxLib',
            'PieceRef',
            'PieceDate',
            'EcritureLib',
            'Debit',
            'Credit',
            'EcritureLet',
            'DateLet',
            'ValidDate',
            'Montantdevise',
            'Idevise',
        ]);

        $content = [$header];

        foreach ($lignes as $ligne) {
            $ecriture = $ligne->ecriture;

            $debit = $ligne->isDebit() ? number_format($ligne->montant, 2, ',', '') : '';
            $credit = $ligne->isCredit() ? number_format($ligne->montant, 2, ',', '') : '';

            $row = implode('|', [
                $ecriture->journal->code,                        // JournalCode
                $ecriture->journal->libelle,                     // JournalLib
                $ecriture->numero_piece,                         // EcritureNum
                $ecriture->date_ecriture->format('Ymd'),         // EcritureDate (AAAAMMJJ)
                $ligne->compte->numero,                          // CompteNum
                $ligne->compte->libelle,                         // CompteLib
                '',                                              // CompAuxNum (compte auxiliaire)
                '',                                              // CompAuxLib
                $ecriture->numero_piece,                         // PieceRef
                $ecriture->date_ecriture->format('Ymd'),         // PieceDate
                $this->cleanText($ligne->libelle),               // EcritureLib
                $debit,                                          // Debit
                $credit,                                         // Credit
                $ligne->lettrage ?? '',                          // EcritureLet
                $ligne->date_lettrage?->format('Ymd') ?? '',     // DateLet
                $ecriture->validated_at?->format('Ymd') ?? '',   // ValidDate
                '',                                              // Montantdevise
                '',                                              // Idevise
            ]);

            $content[] = $row;
        }

        // Générer le nom du fichier FEC
        $siren = '123456789'; // À récupérer depuis config entreprise
        $dateClotureExercice = $exercice->date_fin->format('Ymd');
        $filename = "{$siren}FEC{$dateClotureExercice}.txt";

        // Sauvegarder dans storage
        $path = "fec/{$filename}";
        Storage::put($path, implode("\n", $content));

        return $path;
    }

    /**
     * Nettoie le texte pour le FEC (supprime caractères spéciaux).
     */
    protected function cleanText(string $text): string
    {
        // Supprimer les retours à la ligne et pipes
        $text = str_replace(["\n", "\r", '|'], ' ', $text);

        // Remplacer guillemets
        $text = str_replace('"', '\'', $text);

        return trim($text);
    }

    /**
     * Télécharge le FEC.
     */
    public function telecharger(ExerciceComptable $exercice): StreamedResponse
    {
        $path = $this->generer($exercice);

        return Storage::download($path);
    }
}
