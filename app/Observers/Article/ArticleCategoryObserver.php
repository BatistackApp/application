<?php

namespace App\Observers\Article;

use App\Models\Article\ArticleCategory;
use Illuminate\Support\Str;

class ArticleCategoryObserver
{
    public function creating(ArticleCategory $articleCategory): void
    {
        if (empty($articleCategory->slug)) {
            $articleCategory->slug = Str::slug($articleCategory->name);
        }
    }

    public function updating(ArticleCategory $articleCategory): void
    {
        if (empty($articleCategory->slug)) {
            $articleCategory->slug = Str::slug($articleCategory->name);
        }
    }
}
