<?php

namespace App\Enums\Compta;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CompteSens: string implements HasColor, HasLabel
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEBIT => 'Débit',
            self::CREDIT => 'Crédit',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DEBIT => 'danger',
            self::CREDIT => 'success',
        };
    }

    public function opposite(): self
    {
        return match ($this) {
            self::DEBIT => self::CREDIT,
            self::CREDIT => self::DEBIT,
        };
    }
}
