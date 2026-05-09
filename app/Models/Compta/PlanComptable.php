<?php

namespace App\Models\Compta;

use App\Enums\Compta\CompteType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanComptable extends Model
{
    use HasFactory;

    protected $table = 'plan_comptable';

    protected $fillable = [
        'numero',
        'libelle',
        'type',
        'actif',
        'lettrable',
        'analytique',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => CompteType::class,
            'actif' => 'boolean',
            'lettrable' => 'boolean',
            'analytique' => 'boolean',
        ];
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneEcriture::class, 'compte_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeByType($query, CompteType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeClasse($query, string $classe)
    {
        return $query->where('numero', 'like', "{$classe}%");
    }

    public function scopeTiers($query)
    {
        return $query->classe('4');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function getClasse(): string
    {
        return substr($this->numero, 0, 1);
    }

    public function isCompteClient(): bool
    {
        return str_starts_with($this->numero, '411');
    }

    public function isCompteFournisseur(): bool
    {
        return str_starts_with($this->numero, '401');
    }

    public function isCompteTva(): bool
    {
        return str_starts_with($this->numero, '4457') || str_starts_with($this->numero, '4456');
    }
}
