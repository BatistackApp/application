<?php

namespace App\Enums\Article;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TrackingType: string implements HasDescription, HasLabel
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

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::QUANTITY => 'Par Quantité',
            self::SERIAL_NUMBER => 'Par numéro de série',
        };
    }
}
