<?php

namespace App\Models\Stock;

use App\Enums\Article\AdjustementType;
use App\Enums\Article\StockMouvementType;
use App\Models\Article\Article;
use App\Models\Article\ArticleSerialNumber;
use App\Models\Article\Ouvrage;
use App\Models\Core\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMouvement extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'warehouse_id',
        'target_warehouse_id',
        'type',
        'adjustement_type',
        'quantity',
        'unit_cost_ht',
        'reference',
        'note',
        'user_id',
        'serial_number_id',
        'ouvrage_id',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function targetWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serialNumber(): BelongsTo
    {
        return $this->belongsTo(ArticleSerialNumber::class, 'serial_number_id');
    }

    public function ouvrage(): BelongsTo
    {
        return $this->belongsTo(Ouvrage::class);
    }

    protected function casts(): array
    {
        return [
            'type' => StockMouvementType::class,
            'quantity' => 'decimal:3',
            'unit_cost_ht' => 'decimal:2',
            'adjustement_type' => AdjustementType::class,
        ];
    }
}
