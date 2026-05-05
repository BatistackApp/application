<?php

namespace App\Enums\Chantier;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum ChantierStatus: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case DRAFT = 'draft';
    case OPEN = 'open';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::OPEN => 'Ouvert',
            self::ACTIVE => 'En cours',
            self::PAUSED => 'En pause',
            self::CLOSED => 'Terminé',
            self::ARCHIVED => 'Archivé',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::OPEN => 'info',
            self::ACTIVE => 'success',
            self::PAUSED => 'warning',
            self::CLOSED => 'danger',
            self::ARCHIVED => 'gray',
        };
    }

    public function getIcon(): string|\BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => Phosphor::FileDashed,
            self::OPEN => Phosphor::FolderOpen,
            self::ACTIVE => Phosphor::HardHat,
            self::PAUSED => Phosphor::PauseCircle,
            self::CLOSED => Phosphor::CheckCircle,
            self::ARCHIVED => Phosphor::BoxArrowDown,
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => 'Chantier en préparation, aucun coût imputé',
            self::OPEN => 'Chantier planifié, en attente de démarrage',
            self::ACTIVE => 'Chantier en cours, imputations actives',
            self::PAUSED => 'Chantier suspendu temporairement',
            self::CLOSED => 'Chantier terminé, clôture en cours',
            self::ARCHIVED => 'Chantier archivé, lecture seule',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::DRAFT => in_array($next, [self::OPEN]),
            self::OPEN => in_array($next, [self::ACTIVE, self::DRAFT]),
            self::ACTIVE => in_array($next, [self::PAUSED, self::CLOSED]),
            self::PAUSED => in_array($next, [self::ACTIVE, self::CLOSED]),
            self::CLOSED => in_array($next, [self::ARCHIVED, self::ACTIVE]),
            self::ARCHIVED => [],
        };
    }
}
