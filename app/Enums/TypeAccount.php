<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TypeAccount: string implements HasLabel
{
    case PRIMARY = 'primary';
    case CUSTOMER = 'customer';
    case EMPLOYEE = 'employee';


    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PRIMARY => 'C2ME',
            self::CUSTOMER => 'Client',
            self::EMPLOYEE => 'Salarié',
        };
    }
}
