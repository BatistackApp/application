<?php

namespace App\Rules;

use App\Services\Core\SirenService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SirenSiretExistsRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $service = app(SirenService::class);

        if (! $service->isValid($value)) {
            $fail("Le format du numéro {$attribute} est invalide (clé de contrôle incorrecte).");

            return;
        }

        if (! $service->exists($value)) {
            $fail("Le numéro {$attribute} n'a pas été trouvé dans la base Sirene de l'INSEE.");
        }
    }
}
