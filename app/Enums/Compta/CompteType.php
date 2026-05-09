<?php

namespace App\Enums\Compta;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CompteType: string implements HasColor, HasLabel
{
    case CLASSE_1 = '1'; // Capitaux
    case CLASSE_2 = '2'; // Immobilisations
    case CLASSE_3 = '3'; // Stocks
    case CLASSE_4 = '4'; // Tiers
    case CLASSE_5 = '5'; // Financiers
    case CLASSE_6 = '6'; // Charges
    case CLASSE_7 = '7'; // Produits
    case CLASSE_8 = '8'; // Comptes spéciaux

    public function getLabel(): string
    {
        return match ($this) {
            self::CLASSE_1 => 'Classe 1 - Capitaux',
            self::CLASSE_2 => 'Classe 2 - Immobilisations',
            self::CLASSE_3 => 'Classe 3 - Stocks',
            self::CLASSE_4 => 'Classe 4 - Tiers',
            self::CLASSE_5 => 'Classe 5 - Financiers',
            self::CLASSE_6 => 'Classe 6 - Charges',
            self::CLASSE_7 => 'Classe 7 - Produits',
            self::CLASSE_8 => 'Classe 8 - Comptes spéciaux',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CLASSE_1 => 'info',
            self::CLASSE_2 => 'primary',
            self::CLASSE_3 => 'warning',
            self::CLASSE_4, self::CLASSE_6 => 'danger',
            self::CLASSE_5, self::CLASSE_7 => 'success',
            self::CLASSE_8 => 'gray',
        };
    }

    public function isActif(): bool
    {
        return in_array($this, [self::CLASSE_2, self::CLASSE_3, self::CLASSE_4, self::CLASSE_5]);
    }

    public function isPassif(): bool
    {
        return $this === self::CLASSE_1;
    }

    public function isCharge(): bool
    {
        return $this === self::CLASSE_6;
    }

    public function isProduit(): bool
    {
        return $this === self::CLASSE_7;
    }

    /**
     * Sens naturel du compte (débit ou crédit).
     */
    public function getSensNaturel(): CompteSens
    {
        return match ($this) {
            self::CLASSE_1, self::CLASSE_7 => CompteSens::CREDIT,
            default => CompteSens::DEBIT,
        };
    }
}
