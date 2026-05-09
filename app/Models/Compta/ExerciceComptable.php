<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciceComptable extends Model
{
    use HasFactory;

    protected $table = 'exercices_comptables';

    protected $fillable = [
        'libelle',
        'date_debut',
        'date_fin',
        'cloture',
        'date_cloture',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'date_cloture' => 'date',
            'cloture' => 'boolean',
        ];
    }

    public function ecritures(): HasMany
    {
        return $this->hasMany(Ecriture::class);
    }

    public function declarationsTva(): HasMany
    {
        return $this->hasMany(DeclarationTva::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeEnCours($query)
    {
        return $query->where('cloture', false);
    }

    public function scopeCurrent($query)
    {
        $today = now();

        return $query->where('date_debut', '<=', $today)
            ->where('date_fin', '>=', $today)
            ->where('cloture', false);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isInPeriod(\DateTimeInterface $date): bool
    {
        return $date >= $this->date_debut && $date <= $this->date_fin;
    }

    public function canBeModified(): bool
    {
        return ! $this->cloture;
    }
}
