<?php

namespace App\Models\Chantier;

use App\Enums\Chantier\ChantierTaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChantierTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'chantier_id',
        'parent_task_id',
        'depends_on_task_id',
        'assignee_id',
        'designation',
        'description',
        'status',
        'date_debut',
        'date_fin',
        'avancement_pct',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChantierTaskStatus::class,
            'date_debut' => 'date',
            'date_fin' => 'date',
            'avancement_pct' => 'integer',
            'ordre' => 'integer',
        ];
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChantierTask::class, 'parent_task_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChantierTask::class, 'parent_task_id');
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(ChantierTask::class, 'depends_on_task_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function budgetLines(): BelongsToMany
    {
        return $this->belongsToMany(
            ChantierBudgetLine::class,
            'chantier_task_budget_line',
            'chantier_task_id',
            'chantier_budget_line_id',
        )->withPivot('allocation_pct')->withTimestamps();
    }

    // ─── Accesseurs ──────────────────────────────────────────────────────────

    public function getDureeJoursAttribute(): int
    {
        return $this->date_debut->diffInDays($this->date_fin) + 1;
    }

    public function getBudgetAlloueAttribute(): float
    {
        return $this->budgetLines->sum(
            fn ($line) => $line->cout_total * ($line->pivot->allocation_pct / 100)
        );
    }
}
