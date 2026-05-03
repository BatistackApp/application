<?php

namespace App\Models\Article;

use App\Enums\Article\TrackingType;
use App\Enums\Tiers\TiersCategory;
use App\Enums\UnitOfMesure;
use App\Models\Core\Warehouse;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Représente un article ou un produit dans le système.
 *
 * Cette classe gère les informations de base de l'article, son suivi de stock,
 * ses relations avec les fournisseurs, les entrepôts et les prix.
 */
class Article extends Model
{
    use HasFactory, SoftDeletes;

    /** @var array Les attributs qui peuvent être assignés en masse. */
    protected $fillable = [
        'article_category_id',
        'default_supplier_id',
        'sku',
        'name',
        'description',
        'unit',
        'tracking_type',
        'barcode',
        'qr_code_base',
        'poids',
        'volume',
    ];

    /** @var array Les types de conversion pour les attributs. */
    protected function casts(): array
    {
        return [
            'unit' => UnitOfMesure::class,
            'poids' => 'decimal:3',
            'volume' => 'decimal:3',
            'tracking_type' => TrackingType::class,
        ];
    }

    /** Obtient la catégorie associée à l'article. */
    public function articleCategory(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class);
    }

    /** Obtient le fournisseur par défaut de l'article. */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'default_supplier_id');
    }

    /** Obtient l'historique des prix de l'article. */
    public function prices(): HasMany
    {
        return $this->hasMany(ArticlePrice::class);
    }

    /** Obtient les numéros de série associés (si le type de suivi le permet). */
    public function serialNumbers(): HasMany
    {
        return $this->hasMany(ArticleSerialNumber::class);
    }

    /** Obtient les entrepôts où l'article est stocké avec les niveaux de stock. */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'article_warehouse')
            ->withPivot('min_stock', 'max_stock', 'alert_stock', 'actual_stock', 'bin_location')
            ->withTimestamps();
    }

    /** Obtient les ouvrages dans lesquels cet article est utilisé. */
    public function ouvrages(): BelongsToMany
    {
        return $this->belongsToMany(Ouvrage::class, 'ouvrage_article')
            ->withPivot('quantity_needed')
            ->withTimestamps();
    }

    /** Accesseur pour obtenir le premier prix de vente client trouvé. */
    protected function firstPriceCustomer(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->prices()->where('price_type', TiersCategory::Customer)->first()->amount ?? 0,
        );
    }
}
