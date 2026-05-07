<?php

namespace App\Enums\RH;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Periode: string implements HasLabel
{
    case MATIN = 'matin';
    case APREM = 'aprem';
    case JOURNEE_COMPLETE = 'journee_complete';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::MATIN => 'Matin',
            self::APREM => 'Après-midi',
            self::JOURNEE_COMPLETE => 'Journée complète',
        };
    }
}
