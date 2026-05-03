<?php

namespace App\Enums\Article;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AdjustementType: string implements HasLabel
{
    case GAIN = 'gain';
    case LOSS = 'loss';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::GAIN => 'Gain',
            self::LOSS => 'Perte',
        };
    }
}
