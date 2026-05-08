<?php

namespace App\Models\Commerce;

use App\Models\Article\Article;
use App\Models\Article\Ouvrage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialDocumentLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'commercial_document_id',
        'article_id',
        'ouvrage_id',
        'designation',
        'quantite',
        'unite',
        'prix_unitaire_ht',
        'taux_tva',
        'remise_pct',
        'remise_montant',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'decimal:3',
            'prix_unitaire_ht' => 'decimal:2',
            'taux_tva' => 'decimal:2',
            'remise_pct' => 'decimal:2',
            'remise_montant' => 'decimal:2',
            'total_ht' => 'decimal:2',
            'total_tva' => 'decimal:2',
            'total_ttc' => 'decimal:2',
            'ordre' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(CommercialDocument::class, 'commercial_document_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function ouvrage(): BelongsTo
    {
        return $this->belongsTo(Ouvrage::class);
    }
}
