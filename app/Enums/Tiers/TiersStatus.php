<?php

namespace App\Enums\Tiers;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum TiersStatus: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case ACTIF = 'actif';
    case SLEEPING = 'sleeping';
    case PARTIALLY_BLOCKED = 'partially_blocked';
    case BLOCKED = 'blocked';
    case ARCHIVED = 'archived';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIF => 'success',
            self::SLEEPING => 'info',
            self::PARTIALLY_BLOCKED => 'warning',
            self::BLOCKED => 'danger',
            self::ARCHIVED => 'gray',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::ACTIF => Phosphor::CheckCircle,
            self::SLEEPING => Phosphor::EyeClosed,
            self::PARTIALLY_BLOCKED => Phosphor::Prohibit,
            self::BLOCKED => Phosphor::XCircle,
            self::ARCHIVED => Phosphor::BoxArrowDown,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ACTIF => 'Actif',
            self::SLEEPING => 'En sommeil',
            self::PARTIALLY_BLOCKED => 'Partiellement Bloqué',
            self::BLOCKED => 'Bloqué',
            self::ARCHIVED => 'Archivé',
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::ACTIF => 'Tiers Actifs',
            self::SLEEPING => 'Le tiers reste actif mais les compteurs de productions ne tourne plus',
            self::PARTIALLY_BLOCKED => 'Le tier reste actif mais aucun nouveau document ne peut être édité',
            self::BLOCKED => 'Le tier est inactif aucun document ne peut être créer, édité, au bout de 12 mois de blocage, le tier est automatiquement archivé',
            self::ARCHIVED => 'Le tier est archivé, son état est bloqué.',
        };
    }
}
