<?php

namespace App\Enums\Tiers;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TiersCategory: string implements HasColor, HasLabel
{
    case Customer = 'customer';
    case Supplier = 'supplier';
    case Subcontractor = 'subcontractor';
    case Other = 'other';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Customer => 'success',
            self::Supplier => 'danger',
            self::Subcontractor => 'warning',
            self::Other => 'primary',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Customer => 'Client',
            self::Supplier => 'Fournisseur',
            self::Subcontractor => 'Sous-Traitant',
            self::Other => 'Autre',
        };
    }
}
