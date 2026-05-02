<?php

namespace App\Filament\Article\Resources\Article\Articles\Pages;

use App\Filament\Article\Resources\Article\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;
    protected static ?string $title = 'Création d\'un article';
    protected static ?string $breadcrumb = 'Création d\'un article';
}
