<?php

namespace App\Models\Article;

use App\Observers\Article\ArticleCategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Openplain\FilamentTreeView\Concerns\HasTreeStructure;

#[ObservedBy([ArticleCategoryObserver::class])]
class ArticleCategory extends Model
{
    use HasFactory, HasTreeStructure;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'order',
        'is_active',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'parent_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
