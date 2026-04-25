<?php

namespace App\Enums\Tiers;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TiersAddressType: string implements HasLabel
{
    case INVOICING = 'invoicing';
    case DELIVERING = 'delivering';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::INVOICING => 'Adresse de facturation',
            self::DELIVERING => 'Adresse de livraison/chantier',
        };
    }
}
