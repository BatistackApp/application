<?php

namespace App\Observers\RH;

use App\Enums\RH\PointageStatus;
use App\Models\RH\PointageSession;
use App\Services\RH\PointageService;

class PointageSessionObserver
{
    public function updated(PointageSession $session): void
    {
        if (
            $session->wasChanged('status')
            && $session->status === PointageStatus::VALIDATED
        ) {
            app(PointageService::class)->impute($session);
        }
    }
}
