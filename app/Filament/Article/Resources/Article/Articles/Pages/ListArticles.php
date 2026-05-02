<?php

namespace App\Filament\Article\Resources\Article\Articles\Pages;

use App\Filament\Article\Resources\Article\Articles\ArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;
    protected static ?string $title = 'Liste des articles';
    protected static ?string $breadcrumb = 'Liste des articles';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-s-plus')
                ->label('Ajouter un article'),
        ];
    }
}
