<?php

namespace App\Enums\Commerce;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ModePaiement: string implements HasLabel
{
    case VIREMENT = 'virement';
    case CHEQUE = 'cheque';
    case ESPECES = 'especes';
    case CB = 'cb';
    case PRELEVEMENT = 'prelevement';
    case AUTRE = 'autre';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::VIREMENT => 'Virement bancaire',
            self::CHEQUE => 'Chèque',
            self::ESPECES => 'Espèces',
            self::CB => 'Carte bancaire',
            self::PRELEVEMENT => 'Prélèvement',
            self::AUTRE => 'Autre',
        };
    }
}
