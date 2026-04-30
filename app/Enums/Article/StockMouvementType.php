<?php

namespace App\Enums\Article;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StockMouvementType: string implements HasColor, HasLabel
{
    case ENTRY = 'entry';
    case EXIT = 'exit';
    case TRANSFER = 'transfer';
    case ADJUSTEMENT = 'adjustement';
    case RETURN = 'return';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ENTRY => 'success',
            self::EXIT => 'danger',
            self::TRANSFER => 'info',
            self::ADJUSTEMENT => 'warning',
            self::RETURN => 'primary',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ENTRY => 'Entrée',
            self::EXIT => 'Sortie',
            self::TRANSFER => 'Transfert',
            self::ADJUSTEMENT => 'Ajustement',
            self::RETURN => 'Retour',
        };
    }
}
