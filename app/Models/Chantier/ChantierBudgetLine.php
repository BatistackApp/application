<?php

namespace App\Models\Chantier;

use App\Enums\Chantier\ChantierBudgetType;
use App\Models\Article\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChantierBudgetLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'chantier_id',
        'article_id',
        'type',
        'designation',
        'quantite',
        'unite',
        'cout_unitaire',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'type' => ChantierBudgetType::class,
            'quantite' => 'decimal:3',
            'cout_unitaire' => 'decimal:2',
            'cout_total' => 'decimal:2',
        ];
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(
            ChantierTask::class,
            'chantier_task_budget_line',
            'chantier_budget_line_id',
            'chantier_task_id',
        )->withPivot('allocation_pct')->withTimestamps();
    }
}
