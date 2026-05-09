<?php

namespace App\Enums\Compta;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EcritureStatus: string implements HasColor, HasLabel
{
    case BROUILLON = 'brouillon';
    case VALIDE = 'valide';
    case EXTOURNE = 'extourne';

    public function getLabel(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::VALIDE => 'Validé',
            self::EXTOURNE => 'Extourné',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::BROUILLON => 'gray',
            self::VALIDE => 'success',
            self::EXTOURNE => 'danger',
        };
    }
}
