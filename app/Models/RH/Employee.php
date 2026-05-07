<?php

namespace App\Models\RH;

use App\Enums\RH\JourSemaine;
use App\Enums\RH\TypeContrat;
use App\Models\User;
use App\Observers\RH\EmployeeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([EmployeeObserver::class])]
class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'matricule',
        'type_contrat',
        'taux_horaire',
        'date_embauche',
        'date_fin_contrat',
        'jours_travailles',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type_contrat' => TypeContrat::class,
            'taux_horaire' => 'decimal:2',
            'date_embauche' => 'date',
            'date_fin_contrat' => 'date',
            'jours_travailles' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(PointageSession::class);
    }

    /**
     * Retourne les jours travaillés effectifs
     * (surcharge individuelle ou config globale).
     */
    public function getJoursTravailles(): array
    {
        if (! empty($this->jours_travailles)) {
            return $this->jours_travailles;
        }

        return RhConfiguration::current()->jours_travailles
            ?? JourSemaine::defaultJours();
    }

    /**
     * Retourne les numéros ISO des jours travaillés.
     */
    public function getJoursIso(): array
    {
        return array_map(
            fn ($jour) => JourSemaine::from($jour)->toIsoNumber(),
            $this->getJoursTravailles(),
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
