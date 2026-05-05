<?php

namespace App\Observers\Chantier;

use App\Models\Chantier\Chantier;

class ChantierObserver
{
    public function updated(Chantier $chantier): void
    {
        // Notification de dépassement de budget
        // Déclenché uniquement quand les coûts changent indirectement
        // (via ChantierCout::created) — voir notification dans le service
    }
}
