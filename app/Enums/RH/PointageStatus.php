<?php

namespace App\Enums\RH;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum PointageStatus: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case VALIDATED = 'validated';
    case REJECTED = 'rejected';
    case IMPUTED = 'imputed';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::SUBMITTED => 'Soumis',
            self::VALIDATED => 'Validé',
            self::REJECTED => 'Rejeté',
            self::IMPUTED => 'Imputé',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SUBMITTED => 'warning',
            self::VALIDATED => 'success',
            self::REJECTED => 'danger',
            self::IMPUTED => 'info',
        };
    }

    public function getIcon(): string|\BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => Phosphor::FileDashed,
            self::SUBMITTED => Phosphor::PaperPlane,
            self::VALIDATED => Phosphor::CheckCircle,
            self::REJECTED => Phosphor::XCircle,
            self::IMPUTED => Phosphor::CurrencyEur,
        };
    }
}
