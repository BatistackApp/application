<?php

namespace App\Observers\Article;

use App\Models\Article\Article;
use App\Models\Article\Ouvrage;

class OuvrageObserver
{
    public function created(Ouvrage $ouvrage): void
    {
        // Créer l'Article séparément
        $article = Article::create([
            'sku' => $ouvrage->sku,
            'name' => $ouvrage->name,
            'description' => $ouvrage->description,
        ]);

        // Assigner l'ID de l'Article à l'Ouvrage et sauvegarder (sans déclencher l'observateur à nouveau)
        $ouvrage->article_id = $article->id;
        $ouvrage->saveQuietly(); // saveQuietly pour éviter une boucle infinie d'observateurs
    }

    public function updated(Ouvrage $ouvrage): void
    {
        // Mettre à jour l'Article si la relation existe
        if ($ouvrage->article) {
            $ouvrage->article->update([
                'sku' => $ouvrage->sku,
                'name' => $ouvrage->name,
                'description' => $ouvrage->description,
            ]);
        }
    }

    public function deleted(Ouvrage $ouvrage): void
    {
        // Supprimer (soft delete si configuré) l'Article si la relation existe
        if ($ouvrage->article) {
            $ouvrage->article->delete();
        }
    }
}
