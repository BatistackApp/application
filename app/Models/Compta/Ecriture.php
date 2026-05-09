<?php

namespace App\Models\Compta;

use App\Enums\Compta\CompteSens;
use App\Enums\Compta\EcritureStatus;
use App\Models\User;
use App\Observers\Compta\EcritureObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy([EcritureObserver::class])]
class Ecriture extends Model
{
    use HasFactory;

    protected $table = 'ecritures';

    protected $fillable = [
        'exercice_comptable_id',
        'journal_id',
        'numero_piece',
        'date_ecriture',
        'libelle',
        'status',
        'source_type',
        'source_id',
        'created_by',
        'validated_by',
        'validated_at',
        'extourne_ecriture_id',
    ];

    protected function casts(): array
    {
        return [
            'date_ecriture' => 'date',
            'status' => EcritureStatus::class,
            'validated_at' => 'datetime',
        ];
    }

    public function exercice(): BelongsTo
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_comptable_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneEcriture::class)->orderBy('ordre');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function extourne(): BelongsTo
    {
        return $this->belongsTo(Ecriture::class, 'extourne_ecriture_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeValide($query)
    {
        return $query->where('status', EcritureStatus::VALIDE);
    }

    public function scopeBrouillon($query)
    {
        return $query->where('status', EcritureStatus::BROUILLON);
    }

    public function scopePeriode($query, \DateTimeInterface $debut, \DateTimeInterface $fin)
    {
        return $query->whereBetween('date_ecriture', [$debut, $fin]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lignes()
            ->where('sens', CompteSens::DEBIT)
            ->sum('montant');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lignes()
            ->where('sens', CompteSens::CREDIT)
            ->sum('montant');
    }

    public function isEquilibree(): bool
    {
        return round($this->total_debit, 2) === round($this->total_credit, 2);
    }

    public function canBeModified(): bool
    {
        return $this->status === EcritureStatus::BROUILLON
            && $this->exercice->canBeModified();
    }

    public function canBeValidated(): bool
    {
        return $this->status === EcritureStatus::BROUILLON
            && $this->isEquilibree()
            && $this->lignes()->count() >= 2;
    }
}
