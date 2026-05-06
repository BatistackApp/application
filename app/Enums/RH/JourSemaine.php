<?php

namespace App\Enums\RH;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum JourSemaine: string implements HasLabel
{
    case LUNDI = 'lundi';
    case MARDI = 'mardi';
    case MERCREDI = 'mercredi';
    case JEUDI = 'jeudi';
    case VENDREDI = 'vendredi';
    case SAMEDI = 'samedi';
    case DIMANCHE = 'dimanche';

    public function getLabel(): string|null|Htmlable
    {
        return match ($this) {
            self::LUNDI => 'Lundi',
            self::MARDI => 'Mardi',
            self::MERCREDI => 'Mercredi',
            self::JEUDI => 'Jeudi',
            self::VENDREDI => 'Vendredi',
            self::SAMEDI => 'Samedi',
            self::DIMANCHE => 'Dimanche',
        };
    }

    /**
     * Retourne la configuration par défaut (lundi → vendredi).
     */
    public static function defaultJours(): array
    {
        return [
            self::LUNDI->value,
            self::MARDI->value,
            self::MERCREDI->value,
            self::JEUDI->value,
            self::VENDREDI->value,
        ];
    }

    /**
     * Retourne le numéro ISO du jour (1=lundi, 7=dimanche).
     */
    public function toIsoNumber(): int
    {
        return match ($this) {
            self::LUNDI => 1,
            self::MARDI => 2,
            self::MERCREDI => 3,
            self::JEUDI => 4,
            self::VENDREDI => 5,
            self::SAMEDI => 6,
            self::DIMANCHE => 7,
        };
    }
}
