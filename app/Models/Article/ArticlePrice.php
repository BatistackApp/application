<?php

namespace App\Models\Article;

use App\Enums\Tiers\TiersCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticlePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'price_type',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'price_type' => TiersCategory::class,
            'amount' => 'decimal:2',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
