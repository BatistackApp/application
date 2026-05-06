<?php

namespace App\Enums\RH;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TypeHeure: string implements HasColor, HasLabel
{
    case NORMALE = 'normale';
    case SUPPLEMENTAIRE = 'supplementaire';
    case NUIT = 'nuit';
    case INTEMPERIE = 'intemperie';
    case FORMATION = 'formation';
    case CONGES = 'conges';
    case MALADIE = 'maladie';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::NORMALE => 'Normale',
            self::SUPPLEMENTAIRE => 'Supplémentaire',
            self::NUIT => 'Nuit',
            self::INTEMPERIE => 'Intempérie',
            self::FORMATION => 'Formation',
            self::CONGES => 'Congés',
            self::MALADIE => 'Maladie',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NORMALE => 'success',
            self::SUPPLEMENTAIRE => 'warning',
            self::NUIT => 'info',
            self::INTEMPERIE => 'gray',
            self::FORMATION => 'primary',
            self::CONGES => 'danger',
            self::MALADIE => 'danger',
        };
    }

    /**
     * Les types qui ne génèrent pas de coût sur un chantier.
     */
    public function isImputable(): bool
    {
        return ! in_array($this, [
            self::CONGES,
            self::MALADIE,
        ]);
    }
}
