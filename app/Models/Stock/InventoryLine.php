<?php

namespace App\Models\Stock;

use App\Models\Article\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_session_id',
        'article_id',
        'theoretical_quantity',
        'counted_quantity',
        'difference',
    ];

    public function inventorySession(): BelongsTo
    {
        return $this->belongsTo(InventorySession::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    protected function casts(): array
    {
        return [
            'theoretical_quantity' => 'decimal:3',
            'counted_quantity' => 'decimal:3',
        ];
    }
}
