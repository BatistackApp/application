<?php

namespace App\Observers\Stock;

use App\Enums\Article\InventorySessionStatus;
use App\Models\Stock\InventorySession;
use App\Notifications\Stock\InventorySessionValidatedNotification;

class InventorySessionObserver
{
    public function updated(InventorySession $session): void
    {
        if (
            $session->wasChanged('status')
            && $session->status === InventorySessionStatus::VALIDATED
            && $session->validator
        ) {
            $session->creator->notify(
                new InventorySessionValidatedNotification($session)
            );
        }
    }
}
