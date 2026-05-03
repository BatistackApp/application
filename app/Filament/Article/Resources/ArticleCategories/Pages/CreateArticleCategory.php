<?php

namespace App\Filament\Article\Resources\ArticleCategories\Pages;

use App\Filament\Article\Resources\ArticleCategories\ArticleCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticleCategory extends CreateRecord
{
    protected static string $resource = ArticleCategoryResource::class;
}
