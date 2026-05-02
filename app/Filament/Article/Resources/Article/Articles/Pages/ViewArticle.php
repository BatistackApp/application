<?php

namespace App\Filament\Article\Resources\Article\Articles\Pages;

use App\Filament\Article\Resources\Article\Articles\ArticleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewArticle extends ViewRecord
{
    protected static string $resource = ArticleResource::class;
    protected static ?string $breadcrumb = 'Fiche d\'un article';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Editer un article')
                ->icon('heroicon-s-pencil'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Article: {$this->getRecord()->name}";
    }
}
