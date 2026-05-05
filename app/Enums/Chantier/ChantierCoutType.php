<?php

namespace App\Enums\Chantier;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ChantierCoutType: string implements HasColor, HasLabel
{
    case MAIN_OEUVRE = 'main_oeuvre';
    case MATERIAUX = 'materiaux';
    case LOCATION = 'location';
    case SOUS_TRAITANCE = 'sous_traitance';
    case DIVERS = 'divers';
    case NOTE_FRAIS = 'note_frais';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::MAIN_OEUVRE => 'Main d\'œuvre',
            self::MATERIAUX => 'Matériaux',
            self::LOCATION => 'Location',
            self::SOUS_TRAITANCE => 'Sous-traitance',
            self::DIVERS => 'Frais divers',
            self::NOTE_FRAIS => 'Note de frais',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MAIN_OEUVRE => 'info',
            self::MATERIAUX => 'success',
            self::LOCATION => 'warning',
            self::SOUS_TRAITANCE => 'danger',
            self::DIVERS => 'gray',
            self::NOTE_FRAIS => 'primary',
        };
    }

    public function toBudgetType(): ChantierBudgetType
    {
        return match ($this) {
            self::MAIN_OEUVRE => ChantierBudgetType::MAIN_OEUVRE,
            self::MATERIAUX => ChantierBudgetType::MATERIAUX,
            self::LOCATION => ChantierBudgetType::LOCATION,
            self::SOUS_TRAITANCE => ChantierBudgetType::SOUS_TRAITANCE,
            self::DIVERS,
            self::NOTE_FRAIS => ChantierBudgetType::DIVERS,
        };
    }
}
