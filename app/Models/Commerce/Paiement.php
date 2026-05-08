<?php

namespace App\Models\Commerce;

use App\Enums\Commerce\ModePaiement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'date_paiement',
        'montant',
        'mode_paiement',
        'reference_paiement',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date_paiement' => 'date',
            'montant' => 'decimal:2',
            'mode_paiement' => ModePaiement::class,
        ];
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(CommercialDocument::class, 'facture_id');
    }
}
