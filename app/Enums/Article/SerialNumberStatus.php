<?php

namespace App\Enums\Article;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum SerialNumberStatus: string implements HasColor, HasLabel
{
    case IN_STOCK = 'in_stock';
    case ASSIGNED = 'assigned'; // Affecté à un chantier
    case MAINTENANCE = 'maintenance';
    case LOST = 'lost';
    case SOLD = 'sold';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::IN_STOCK => 'success',
            self::ASSIGNED => 'info',
            self::MAINTENANCE => 'warning',
            self::LOST, self::SOLD => 'danger',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::IN_STOCK => 'En stock',
            self::ASSIGNED => 'Affecté',
            self::MAINTENANCE => 'En maintenance',
            self::LOST => 'Perdu/Volée',
            self::SOLD => 'Vendu',
        };
    }
}
