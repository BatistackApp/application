<?php

namespace App\Observers\Tiers;

use App\Models\Tiers\Tiers;
use App\Services\Tiers\TiersCodeGenerator;
use Random\RandomException;

class TiersObserver
{
    public function __construct(private TiersCodeGenerator $generator) {}

    /**
     * @throws RandomException
     */
    public function creating(Tiers $tiers): void
    {
        if (empty($tiers->code)) {
            $tiers->code = $this->generator->generateWithRetry($tiers->category);
        }
    }
}
