<?php

namespace App\Enums\RH;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TypeContrat: string implements HasColor, HasLabel
{
    case CDI = 'cdi';
    case CDD = 'cdd';
    case INTERIM = 'interim';
    case APPRENTI = 'apprenti';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::CDI => 'CDI',
            self::CDD => 'CDD',
            self::INTERIM => 'Intérim',
            self::APPRENTI => 'Apprenti',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CDI => 'success',
            self::CDD => 'warning',
            self::INTERIM => 'info',
            self::APPRENTI => 'gray',
        };
    }
}
