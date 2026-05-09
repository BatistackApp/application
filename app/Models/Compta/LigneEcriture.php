<?php

namespace App\Models\Compta;

use App\Enums\Compta\CompteSens;
use App\Models\Chantier\Chantier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneEcriture extends Model
{
    use HasFactory;

    protected $table = 'lignes_ecriture';

    protected $fillable = [
        'ecriture_id',
        'compte_id',
        'sens',
        'montant',
        'libelle',
        'chantier_id',
        'lettrage',
        'date_lettrage',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'sens' => CompteSens::class,
            'montant' => 'decimal:2',
            'date_lettrage' => 'date',
            'ordre' => 'integer',
        ];
    }

    public function ecriture(): BelongsTo
    {
        return $this->belongsTo(Ecriture::class);
    }

    public function compte(): BelongsTo
    {
        return $this->belongsTo(PlanComptable::class, 'compte_id');
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isDebit(): bool
    {
        return $this->sens === CompteSens::DEBIT;
    }

    public function isCredit(): bool
    {
        return $this->sens === CompteSens::CREDIT;
    }

    public function isLettree(): bool
    {
        return $this->lettrage !== null;
    }
}
