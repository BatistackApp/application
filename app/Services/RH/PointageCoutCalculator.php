<?php

namespace App\Services\RH;

use App\Models\RH\PointageLine;
use App\Models\RH\PointageSession;
use App\Models\RH\RhConfiguration;

class PointageCoutCalculator
{
    private RhConfiguration $config;

    public function __construct()
    {
        $this->config = RhConfiguration::current();
    }

    /**
     * Calcule le coût total d'une ligne de pointage.
     * Retourne un tableau détaillé par poste.
     */
    public function getCoutLigne(PointageLine $line): array
    {
        $tauxHoraire = (float) $line->session->employee->taux_horaire;

        $coutMo = $this->getCoutMainOeuvre($line, $tauxHoraire);
        $coutTrajet = $this->getCoutTrajet($line, $tauxHoraire);
        $coutGd = $this->getCoutGrandDeplacement($line);
        $coutPanier = $this->getCoutPanierRepas($line);

        return [
            'main_oeuvre' => $coutMo,
            'trajet' => $coutTrajet,
            'grand_deplacement' => $coutGd,
            'panier_repas' => $coutPanier,
            'total' => round($coutMo + $coutTrajet + $coutGd + $coutPanier, 2),
            'imputable' => $line->type_heure->isImputable() && $line->chantier_id !== null,
        ];
    }

    /**
     * Calcule le coût total d'une session.
     */
    public function getCoutSession(PointageSession $session): array
    {
        $session->loadMissing(['lines.session.employee', 'lines.chantier']);

        $totaux = [
            'main_oeuvre' => 0.0,
            'trajet' => 0.0,
            'grand_deplacement' => 0.0,
            'panier_repas' => 0.0,
            'total' => 0.0,
            'total_heures' => 0.0,
            'total_heures_trajet' => 0.0,
            'par_chantier' => [],
        ];

        foreach ($session->lines as $line) {
            $cout = $this->getCoutLigne($line);

            $totaux['main_oeuvre'] += $cout['main_oeuvre'];
            $totaux['trajet'] += $cout['trajet'];
            $totaux['grand_deplacement'] += $cout['grand_deplacement'];
            $totaux['panier_repas'] += $cout['panier_repas'];
            $totaux['total'] += $cout['total'];
            $totaux['total_heures'] += (float) $line->heures;
            $totaux['total_heures_trajet'] += (float) $line->heures_trajet;

            // Regroupement par chantier
            if ($line->chantier_id && $cout['imputable']) {
                $key = $line->chantier_id;
                if (! isset($totaux['par_chantier'][$key])) {
                    $totaux['par_chantier'][$key] = [
                        'chantier' => $line->chantier,
                        'main_oeuvre' => 0.0,
                        'trajet' => 0.0,
                        'total' => 0.0,
                        'heures' => 0.0,
                    ];
                }
                $totaux['par_chantier'][$key]['main_oeuvre'] += $cout['main_oeuvre'];
                $totaux['par_chantier'][$key]['trajet'] += $cout['trajet'];
                $totaux['par_chantier'][$key]['total'] += $cout['main_oeuvre'] + $cout['trajet'];
                $totaux['par_chantier'][$key]['heures'] += (float) $line->heures;
            }
        }

        // Arrondir les totaux
        foreach (['main_oeuvre', 'trajet', 'grand_deplacement', 'panier_repas', 'total'] as $key) {
            $totaux[$key] = round($totaux[$key], 2);
        }

        return $totaux;
    }

    /**
     * Coût main d'œuvre = heures × taux horaire.
     */
    public function getCoutMainOeuvre(PointageLine $line, float $tauxHoraire): float
    {
        if (! $line->type_heure->isImputable()) {
            return 0.0;
        }

        return round((float) $line->heures * $tauxHoraire, 2);
    }

    /**
     * Coût trajet = heures_trajet × taux horaire × taux prise en charge.
     */
    public function getCoutTrajet(PointageLine $line, float $tauxHoraire): float
    {
        if (! $this->config->prise_en_charge_trajet) {
            return 0.0;
        }

        if ((float) $line->heures_trajet <= 0) {
            return 0.0;
        }

        $taux = (float) $this->config->taux_prise_en_charge_trajet / 100;

        return round((float) $line->heures_trajet * $tauxHoraire * $taux, 2);
    }

    /**
     * Indemnité grand déplacement — montant forfaitaire journalier.
     */
    public function getCoutGrandDeplacement(PointageLine $line): float
    {
        if (! $this->config->grand_deplacement_actif) {
            return 0.0;
        }

        if (! $line->grand_deplacement) {
            return 0.0;
        }

        // Pour une demi-journée, on applique la moitié du forfait
        if ($line->periode->value !== 'journee_complete') {
            return round((float) $this->config->grand_deplacement_montant_jour / 2, 2);
        }

        return round((float) $this->config->grand_deplacement_montant_jour, 2);
    }

    /**
     * Indemnité panier repas — montant forfaitaire.
     * Uniquement pour une journée complète ou l'après-midi.
     */
    public function getCoutPanierRepas(PointageLine $line): float
    {
        if (! $this->config->panier_repas_actif) {
            return 0.0;
        }

        if (! $line->panier_repas) {
            return 0.0;
        }

        // Le panier repas ne s'applique pas pour le matin seul
        if ($line->periode->value === 'matin') {
            return 0.0;
        }

        return round((float) $this->config->panier_repas_montant, 2);
    }
}
