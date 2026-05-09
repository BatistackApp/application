<?php

namespace App\Models\Compta;

use App\Enums\Compta\JournalType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    use HasFactory;

    protected $table = 'journaux';

    protected $fillable = [
        'code',
        'libelle',
        'type',
        'actif',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => JournalType::class,
            'actif' => 'boolean',
        ];
    }

    public function ecritures(): HasMany
    {
        return $this->hasMany(Ecriture::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeByType($query, JournalType $type)
    {
        return $query->where('type', $type);
    }
}
