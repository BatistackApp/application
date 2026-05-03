<?php

namespace App\Services\Article;

use App\Models\Article\Ouvrage;

class OuvrageService
{
    /**
     * Génère un SKU (code article) unique basé sur le temps et un nombre aléatoire.
     *
     * @return string
     */
    public function generateSKU(): string
    {
        return 'OUV'.substr(time(), -3).str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Génère un SKU unique avec plusieurs tentatives pour éviter les doublons en base de données.
     *
     * @param int $maxRetry Nombre maximum de tentatives.
     * @return string
     * @throws \Exception Si aucun code unique n'est trouvé après le nombre maximum de tentatives.
     */
    public function generateWithRetry(int $maxRetry = 5): string
    {
        for ($i = 0; $i < $maxRetry; $i++) {
            $code = $this->generateSKU();

            if (! Ouvrage::where('sku', $code)->exists()) {
                return $code;
            }
        }

        throw new \Exception('Impossible de générer un code ouvrage unique après '.$maxRetry.' tentatives');
    }

    /**
     * Active un ouvrage.
     *
     * @param Ouvrage $ouvrage
     * @return bool
     */
    public function activate(Ouvrage $ouvrage): bool
    {
        return $ouvrage->update(['is_active' => true]);
    }

    /**
     * Désactive un ouvrage.
     *
     * @param Ouvrage $ouvrage
     * @return bool
     */
    public function deactivate(Ouvrage $ouvrage): bool
    {
        return $ouvrage->update(['is_active' => false]);
    }
}
