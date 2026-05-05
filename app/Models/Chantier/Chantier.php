<?php

namespace App\Models\Chantier;

use App\Enums\Chantier\ChantierStatus;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Observers\Chantier\ChantierObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ChantierObserver::class])]
class Chantier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'nom',
        'description',
        'client_id',
        'responsable_id',
        'status',
        'adresse',
        'code_postal',
        'ville',
        'pays',
        'latitude',
        'longitude',
        'date_debut_prevue',
        'date_fin_prevue',
        'date_debut_reelle',
        'date_fin_reelle',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChantierStatus::class,
            'date_debut_prevue' => 'date',
            'date_fin_prevue' => 'date',
            'date_debut_reelle' => 'date',
            'date_fin_reelle' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'client_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function budgetLines(): HasMany
    {
        return $this->hasMany(ChantierBudgetLine::class);
    }

    public function couts(): HasMany
    {
        return $this->hasMany(ChantierCout::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ChantierTask::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ChantierDocument::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', ChantierStatus::ACTIVE);
    }

    public function scopeEnCours($query)
    {
        return $query->whereIn('status', [
            ChantierStatus::OPEN,
            ChantierStatus::ACTIVE,
            ChantierStatus::PAUSED,
        ]);
    }
}
