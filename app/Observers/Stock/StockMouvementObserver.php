<?php

namespace App\Observers\Stock;

use App\Models\Stock\StockMouvement;
use App\Notifications\Stock\StockAlertNotification;

class StockMouvementObserver
{
    public function created(StockMouvement $mouvement): void
    {
        $pivot = $mouvement->article
            ->warehouses()
            ->where('warehouse_id', $mouvement->warehouse_id)
            ->first()
            ?->pivot;

        if (! $pivot) {
            return;
        }

        if ($pivot->alert_stock > 0 && $pivot->actual_stock <= $pivot->alert_stock) {
            $responsibleUser = $mouvement->warehouse->responsibleUser;

            if ($responsibleUser) {
                $responsibleUser->notify(new StockAlertNotification($mouvement->article, $mouvement->warehouse, $pivot->actual_stock, $pivot->alert_stock));
            }
        }
    }
}
