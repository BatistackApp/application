<?php

namespace App\Models\Article;

use App\Enums\UnitOfMesure;
use App\Observers\Article\OuvrageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([OuvrageObserver::class])]
/**
 * Représente un ouvrage composé de plusieurs articles (composants).
 */
class Ouvrage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        /**
         * @var string $sku Unité de gestion des stocks (Stock Keeping Unit).
         * @var string $name Nom de l'ouvrage.
         * @var float $cump_ht Coût Unitaire Moyen Pondéré Hors Taxe.
         */
        'sku',
        'name',
        'description',
        'unit',
        'is_active',
        'article_id',
        'cump_ht',
    ];

    /**
     * Obtient l'article de base associé à cet ouvrage.
     *
     * @return BelongsTo
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Liste des composants (articles) qui constituent cet ouvrage.
     *
     * @return BelongsToMany
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'ouvrage_article')
            ->withPivot('quantity_needed')
            ->withTimestamps();
    }

    /**
     * Définit les conversions de types pour les attributs.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'unit' => UnitOfMesure::class,
        ];
    }
}
