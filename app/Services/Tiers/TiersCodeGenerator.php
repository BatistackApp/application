<?php

namespace App\Services\Tiers;

use App\Enums\Tiers\TiersCategory;
use App\Models\Tiers\Tiers;
use Random\RandomException;

class TiersCodeGenerator
{
    /**
     * Génère un code unique pour un tiers basé sur sa catégorie.
     *
     * @param  TiersCategory  $tiersCategory  La catégorie du tiers (Client, Fournisseur, etc.)
     * @return string Le code généré (ex: CUS-123456)
     *
     * @throws RandomException
     */
    public function generate(TiersCategory $tiersCategory): string
    {
        $prefix = match ($tiersCategory) {
            TiersCategory::Customer => 'CUS',
            TiersCategory::Supplier => 'SUP',
            TiersCategory::Subcontractor => 'SU',
            TiersCategory::Other => 'OTH',
        };

        $number = substr(time(), -3).str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$number}";
    }

    /**
     * Génère un code unique avec un mécanisme de tentative pour éviter les doublons en base de données.
     *
     * @param  TiersCategory  $tiersCategory  La catégorie du tiers.
     * @param  int  $maxAttemps  Le nombre maximum de tentatives de génération.
     * @return string Le code unique généré.
     *
     * @throws \Exception Si aucun code unique n'est trouvé après le nombre maximum de tentatives.
     * @throws RandomException
     */
    public function generateWithRetry(TiersCategory $tiersCategory, int $maxAttemps = 5): string
    {
        for ($i = 0; $i < $maxAttemps; $i++) {
            $code = $this->generate($tiersCategory);

            if (! Tiers::where('code', $code)->exists()) {
                return $code;
            }
        }

        throw new \Exception("Impossible de générer un code de tiers après {$maxAttemps} tentatives");
    }
}
