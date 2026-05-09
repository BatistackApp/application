<?php

namespace App\Enums\Compta;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JournalType: string implements HasColor, HasLabel
{
    case VENTES = 'VE';
    case ACHATS = 'AC';
    case BANQUE = 'BQ';
    case CAISSE = 'CA';
    case OPERATIONS_DIVERSES = 'OD';
    case A_NOUVEAUX = 'AN';
    case PAIE = 'PAIE';

    public function getLabel(): string
    {
        return match ($this) {
            self::VENTES => 'Ventes',
            self::ACHATS => 'Achats',
            self::BANQUE => 'Banque',
            self::CAISSE => 'Caisse',
            self::OPERATIONS_DIVERSES => 'Opérations diverses',
            self::A_NOUVEAUX => 'À-nouveaux',
            self::PAIE => 'Paie',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::VENTES => 'success',
            self::ACHATS => 'danger',
            self::BANQUE => 'primary',
            self::CAISSE => 'warning',
            self::PAIE => 'info',
            default => 'gray',
        };
    }

    public function getCode(): string
    {
        return $this->value;
    }
}
