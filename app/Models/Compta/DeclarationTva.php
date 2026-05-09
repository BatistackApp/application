<?php

namespace App\Models\Compta;

use App\Enums\Compta\RegimeTva;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeclarationTva extends Model
{
    use HasFactory;

    protected $table = 'declarations_tva';

    protected $fillable = [
        'exercice_comptable_id',
        'periode',
        'date_debut',
        'date_fin',
        'regime',
        'base_tva_collectee_20',
        'montant_tva_collectee_20',
        'base_tva_collectee_10',
        'montant_tva_collectee_10',
        'base_tva_collectee_55',
        'montant_tva_collectee_55',
        'total_tva_collectee',
        'tva_deductible_immobilisations',
        'tva_deductible_biens_services',
        'total_tva_deductible',
        'tva_nette',
        'credit_periode_precedente',
        'tva_due',
        'validee',
        'validee_at',
        'validee_by',
        'transmise',
        'transmise_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'regime' => RegimeTva::class,
            'base_tva_collectee_20' => 'decimal:2',
            'montant_tva_collectee_20' => 'decimal:2',
            'base_tva_collectee_10' => 'decimal:2',
            'montant_tva_collectee_10' => 'decimal:2',
            'base_tva_collectee_55' => 'decimal:2',
            'montant_tva_collectee_55' => 'decimal:2',
            'total_tva_collectee' => 'decimal:2',
            'tva_deductible_immobilisations' => 'decimal:2',
            'tva_deductible_biens_services' => 'decimal:2',
            'total_tva_deductible' => 'decimal:2',
            'tva_nette' => 'decimal:2',
            'credit_periode_precedente' => 'decimal:2',
            'tva_due' => 'decimal:2',
            'validee' => 'boolean',
            'validee_at' => 'datetime',
            'transmise' => 'boolean',
            'transmise_at' => 'datetime',
        ];
    }

    public function exercice(): BelongsTo
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_comptable_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validee_by');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function canBeModified(): bool
    {
        return ! $this->validee && ! $this->transmise;
    }

    public function canBeValidated(): bool
    {
        return ! $this->validee && $this->total_tva_collectee > 0;
    }

    public function canBeTransmitted(): bool
    {
        return $this->validee && ! $this->transmise;
    }

    public function getSoldeAttribute(): float
    {
        return (float) $this->tva_due;
    }

    public function hasCredit(): bool
    {
        return $this->tva_nette < 0;
    }
}
