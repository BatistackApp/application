<?php

namespace App\Enums\Article;

use Filament\Support\Contracts\HasDescription;
use Illuminate\Contracts\Support\Htmlable;

enum TrackingType: string implements HasDescription
{
    case QUANTITY = 'quantity';
    case SERIAL_NUMBER = 'serial_number';

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::QUANTITY => 'Suivi classique par masse/quantité',
            self::SERIAL_NUMBER => 'Suivi unitaire par numéro de série',
        };
    }
}
