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

class Article extends Model
{
    use HasFactory, SoftDeletes;

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

    protected function casts(): array
    {
        return [
            'unit' => UnitOfMesure::class,
            'poids' => 'decimal:3',
            'volume' => 'decimal:3',
            'tracking_type' => TrackingType::class,
        ];
    }

    public function articleCategory(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'default_supplier_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ArticlePrice::class);
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'article_warehouse')
            ->withPivot('min_stock', 'max_stock', 'alert_stock', 'actual_stock', 'bin_location')
            ->withTimestamps();
    }

    protected function firstPriceCustomer(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->prices()->where('price_type', TiersCategory::Customer)->first()->amount,
        );
    }
}
