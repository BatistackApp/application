<?php

namespace App\Models\RH;

use App\Enums\RH\PointageStatus;
use App\Models\User;
use App\Observers\RH\PointageSessionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([PointageSessionObserver::class])]
class PointageSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'semaine_du',
        'status',
        'submitted_at',
        'validated_at',
        'rejected_at',
        'imputed_at',
        'validated_by',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => PointageStatus::class,
            'semaine_du' => 'date',
            'submitted_at' => 'datetime',
            'validated_at' => 'datetime',
            'rejected_at' => 'datetime',
            'imputed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PointageLine::class, 'pointage_session_id');
    }

    /**
     * Retourne le label de la semaine : "Semaine du 02/06/2026 au 06/06/2026".
     */
    public function getLabelSemaineAttribute(): string
    {
        $debut = $this->semaine_du->format('d/m/Y');
        $fin = $this->semaine_du->addDays(6)->format('d/m/Y');

        return "Semaine du {$debut} au {$fin}";
    }

    public function scopeEnCours($query)
    {
        return $query->whereIn('status', [
            PointageStatus::DRAFT,
            PointageStatus::SUBMITTED,
        ]);
    }
}
