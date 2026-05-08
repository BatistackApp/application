<?php

namespace App\Enums\Commerce;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TauxTva: string implements HasLabel
{
    case TVA_20 = '20.00';
    case TVA_10 = '10.00';
    case TVA_5_5 = '5.50';
    case TVA_2_1 = '2.10';
    case EXONERE = '0.00';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::TVA_20 => '20 % (taux normal)',
            self::TVA_10 => '10 % (taux intermédiaire)',
            self::TVA_5_5 => '5,5 % (taux réduit)',
            self::TVA_2_1 => '2,1 % (taux particulier)',
            self::EXONERE => 'Exonéré / Auto-liquidation',
        };
    }

    public function getRate(): float
    {
        return (float) $this->value;
    }
}
