<?php

namespace App\Services\Article;

use App\Models\Article\Article;

class ArticleService
{
    /**
     * Génère un code SKU (Stock Keeping Unit) basé sur le temps et un nombre aléatoire.
     *
     * @return string
     */
    public function generateSKU(): string
    {
        return 'ART'.substr(time(), -3).str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Génère un SKU unique en vérifiant son existence dans la base de données.
     *
     * @param int $maxRetry Nombre maximum de tentatives.
     * @return string
     * @throws \Exception Si aucun code unique n'est trouvé après le nombre maximum de tentatives.
     */
    public function generateWithRetry(int $maxRetry = 5): string
    {
        for ($i = 0; $i < $maxRetry; $i++) {
            $code = $this->generateSKU();

            if (! Article::where('sku', $code)->exists()) {
                return $code;
            }
        }

        throw new \Exception('Unable to generate unique article code after '.$maxRetry.' attempts');
    }
}
