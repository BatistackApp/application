<?php

namespace App\Observers\Article;

use App\Models\Article\Ouvrage;

class OuvrageObserver
{
    public function created(Ouvrage $ouvrage): void
    {
        $ouvrage->article()->create([
            'sku' => $ouvrage->sku,
            'name' => $ouvrage->name,
            'description' => $ouvrage->description,
        ]);
    }

    public function updated(Ouvrage $ouvrage): void
    {
        $ouvrage->article()->update([
            'sku' => $ouvrage->sku,
            'name' => $ouvrage->name,
            'description' => $ouvrage->description,
        ]);
    }

    public function deleted(Ouvrage $ouvrage): void
    {
        $ouvrage->article()->delete();
    }
}
