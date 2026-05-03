<?php

namespace App\Filament\Article\Resources\ArticleCategories\Pages;

use App\Filament\Article\Resources\ArticleCategories\ArticleCategoryResource;
use Filament\Actions\CreateAction;
use Openplain\FilamentTreeView\Resources\Pages\TreePage;

class TreeCategories extends TreePage
{
    protected static string $resource = ArticleCategoryResource::class;
    protected static ?string $title = 'Catégories';
    protected static ?string $breadcrumb = 'Categories';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-s-plus')
                ->label('Nouvelle Catégorie'),
        ];
    }
}
