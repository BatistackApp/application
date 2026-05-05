<?php

namespace App\Enums\Chantier;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum ChantierTaskStatus: string implements HasColor, HasIcon, HasLabel
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';
    case BLOCKED = 'blocked';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::TODO => 'À faire',
            self::IN_PROGRESS => 'En cours',
            self::DONE => 'Terminé',
            self::BLOCKED => 'Bloqué',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::TODO => 'gray',
            self::IN_PROGRESS => 'info',
            self::DONE => 'success',
            self::BLOCKED => 'danger',
        };
    }

    public function getIcon(): string|\BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::TODO => Phosphor::Circle,
            self::IN_PROGRESS => Phosphor::CircleHalf,
            self::DONE => Phosphor::CheckCircle,
            self::BLOCKED => Phosphor::XCircle,
        };
    }
}
