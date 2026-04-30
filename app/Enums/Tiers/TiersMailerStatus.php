<?php

namespace App\Enums\Tiers;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TiersMailerStatus: string implements HasLabel
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PUBLISHED => 'Publié',
        };
    }
}
