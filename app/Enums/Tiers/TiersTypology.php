<?php

namespace App\Enums\Tiers;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TiersTypology: string implements HasLabel
{
    case Entreprise = 'entreprise';
    case Public = 'public';
    case Particulier = 'particulier';


    public function getLabel(): string|Htmlable|null
    {
        return match($this) {
            self::Entreprise => 'Entreprise',
            self::Public => 'Entité Public',
            self::Particulier => 'Particulier',
        };
    }
}
