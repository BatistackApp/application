<?php

namespace App\Models\RH;

use App\Enums\RH\JourSemaine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RhConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'heures_matin',
        'heures_aprem',
        'jours_travailles',
        'prise_en_charge_trajet',
        'taux_prise_en_charge_trajet',
        'grand_deplacement_actif',
        'grand_deplacement_montant_jour',
        'grand_deplacement_montant_repas',
        'grand_deplacement_montant_heberg',
        'panier_repas_actif',
        'panier_repas_montant',
    ];

    protected function casts(): array
    {
        return [
            'heures_matin' => 'decimal:2',
            'heures_aprem' => 'decimal:2',
            'jours_travailles' => 'array',
            'prise_en_charge_trajet' => 'boolean',
            'taux_prise_en_charge_trajet' => 'decimal:2',
            'grand_deplacement_actif' => 'boolean',
            'grand_deplacement_montant_jour' => 'decimal:2',
            'grand_deplacement_montant_repas' => 'decimal:2',
            'grand_deplacement_montant_heberg' => 'decimal:2',
            'panier_repas_actif' => 'boolean',
            'panier_repas_montant' => 'decimal:2',
        ];
    }

    /**
     * Singleton — retourne la configuration ou la crée avec les valeurs par défaut.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'heures_matin' => 3.50,
            'heures_aprem' => 4.00,
            'jours_travailles' => JourSemaine::defaultJours(),
        ]);
    }

    /**
     * Retourne les jours travaillés sous forme d'instances JourSemaine.
     */
    public function getJoursEnum(): array
    {
        return array_map(
            fn ($jour) => JourSemaine::from($jour),
            $this->jours_travailles ?? JourSemaine::defaultJours(),
        );
    }

    /**
     * Retourne les numéros ISO des jours travaillés (1=lundi, 7=dimanche).
     */
    public function getJoursIso(): array
    {
        return array_map(
            fn ($jour) => JourSemaine::from($jour)->toIsoNumber(),
            $this->jours_travailles ?? JourSemaine::defaultJours(),
        );
    }
}
