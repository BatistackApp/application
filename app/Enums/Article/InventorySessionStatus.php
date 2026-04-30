<?php

namespace App\Enums\Article;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum InventorySessionStatus: string implements HasColor, HasLabel
{
    case OPEN = 'open';
    case COUNTING = 'counting';
    case CLOSED = 'closed';
    case VALIDATED = 'validated';
    case CANCELLED = 'cancelled';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::OPEN => 'gray',
            self::COUNTING => 'info',
            self::CLOSED, self::CANCELLED => 'danger',
            self::VALIDATED => 'success',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::OPEN => 'Ouvert',
            self::COUNTING => 'Comptage',
            self::CLOSED => 'Fermé',
            self::CANCELLED => 'Annulé',
            self::VALIDATED => 'Validé',
        };
    }
}
