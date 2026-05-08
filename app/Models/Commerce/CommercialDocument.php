<?php

namespace App\Models\Commerce;

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\DocumentType;
use App\Models\Chantier\Chantier;
use App\Models\Tiers\Tiers;
use App\Observers\Commerce\CommercialDocumentObserver;
use Attribute;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([CommercialDocumentObserver::class])]
class CommercialDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'reference',
        'status',
        'client_id',
        'chantier_id',
        'date_document',
        'date_validite',
        'date_echeance',
        'total_ht',
        'total_tva',
        'total_ttc',
        'remise_globale_pct',
        'remise_globale_montant',
        'avancement_pct',
        'notes',
        'conditions_reglement',
        'parent_document_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'status' => DocumentStatus::class,
            'date_document' => 'date',
            'date_validite' => 'date',
            'date_echeance' => 'date',
            'total_ht' => 'decimal:2',
            'total_tva' => 'decimal:2',
            'total_ttc' => 'decimal:2',
            'remise_globale_pct' => 'decimal:2',
            'remise_globale_montant' => 'decimal:2',
            'avancement_pct' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'client_id');
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CommercialDocumentLine::class)->orderBy('ordre');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'facture_id');
    }

    public function relances(): HasMany
    {
        return $this->hasMany(Relance::class, 'facture_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CommercialDocument::class, 'parent_document_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CommercialDocument::class, 'parent_document_id');
    }

    // ─── Accesseurs ──────────────────────────────────────────────────────────

    /**
     * Calcule le solde en utilisant la somme pré-calculée si disponible,
     * sinon en faisant une requête.
     */
    protected function solde(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                if (! $this->isFacture()) {
                    return 0.0;
                }

                // Utilise la somme eager-loaded par withSum('paiements', 'montant')
                // Le nom par défaut est 'paiements_sum_montant'
                if (isset($this->paiements_sum_montant)) {
                    $totalPaiements = (float) $this->paiements_sum_montant;
                } else {
                    // Fallback si la somme n'est pas chargée (ex: accès direct à un modèle)
                    $totalPaiements = (float) $this->paiements()->sum('montant');
                }

                return round((float) $this->total_ttc - $totalPaiements, 2);
            }
        );
    }

    public function isFacture(): bool
    {
        return in_array($this->type, [DocumentType::FACTURE, DocumentType::FACTURE_ACOMPTE]);
    }

    public function isFullyPaid(): bool
    {
        return $this->isFacture() && $this->solde <= 0;
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeFactures($query)
    {
        return $query->whereIn('type', [
            DocumentType::FACTURE,
            DocumentType::FACTURE_ACOMPTE,
        ]);
    }

    public function scopeImpayes($query)
    {
        return $query->factures()
            ->whereIn('status', [
                DocumentStatus::SENT,
                DocumentStatus::PARTIALLY_PAID,
            ])
            ->where('date_echeance', '<', now());
    }
}
