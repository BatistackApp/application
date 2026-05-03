<?php

namespace App\Observers\Core;

use App\Jobs\Core\WarehouseLocatizationJob;
use App\Models\Core\Warehouse;

class WarehouseObserver
{
    public function created(Warehouse $warehouse): void
    {
        if ($warehouse->wasChanged('location')) {
            WarehouseLocatizationJob::dispatch($warehouse);
        }
    }

    public function updated(Warehouse $warehouse): void
    {
        if ($warehouse->wasChanged('location')) {
            WarehouseLocatizationJob::dispatch($warehouse);
        }
    }
}
