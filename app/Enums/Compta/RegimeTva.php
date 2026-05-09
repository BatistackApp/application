<?php

namespace App\Enums\Compta;

use Filament\Support\Contracts\HasLabel;

enum RegimeTva: string implements HasLabel
{
    case REEL_NORMAL = 'reel_normal';
    case REEL_SIMPLIFIE = 'reel_simplifie';
    case FRANCHISE = 'franchise';

    public function getLabel(): string
    {
        return match ($this) {
            self::REEL_NORMAL => 'Réel normal (CA3 mensuel)',
            self::REEL_SIMPLIFIE => 'Réel simplifié (CA12 trimestriel)',
            self::FRANCHISE => 'Franchise en base',
        };
    }

    public function isPeriodique(): bool
    {
        return in_array($this, [self::REEL_NORMAL, self::REEL_SIMPLIFIE]);
    }

    public function getPeriodicite(): ?string
    {
        return match ($this) {
            self::REEL_NORMAL => 'mensuel',
            self::REEL_SIMPLIFIE => 'trimestriel',
            self::FRANCHISE => null,
        };
    }
}
