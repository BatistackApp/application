<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum UnitOfMesure: string implements HasLabel
{
    case UNIT = 'unit';
    case KILOGRAM = 'kg';
    case GRAM = 'g';
    case LITER = 'l';
    case METER = 'm';
    case SQUARE_METER = 'm2';
    case CUBIC_METER = 'm3';
    case LINEAR_METER = 'ml';
    case TONNE = 'ton';
    case HOUR = 'h';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::UNIT => 'Unité (Pce)',
            self::KILOGRAM => 'Kilogramme (kg)',
            self::GRAM => 'Gramme (g)',
            self::LITER => 'Litre (l)',
            self::METER => 'Mètre (m)',
            self::SQUARE_METER => 'Mètre Carré (m²)',
            self::CUBIC_METER => 'Mètre Cube (m³)',
            self::HOUR => 'Heure (h)',
            self::LINEAR_METER => 'Mètre Linéaire',
            self::TONNE => 'Tonne',
        };
    }

    public function getAbrv(): string
    {
        return match ($this) {
            self::UNIT => 'U',
            self::KILOGRAM => 'Kg',
            self::GRAM => 'g',
            self::LITER => 'l',
            self::METER => 'm',
            self::SQUARE_METER => 'm²',
            self::CUBIC_METER => 'm³',
            self::HOUR => 'h',
            self::LINEAR_METER => 'ml',
            self::TONNE => 't',
        };
    }
}
