<?php

namespace App\Filament\Article\Resources\Article\Articles\Pages;

use App\Filament\Article\Resources\Article\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;
    protected static ?string $breadcrumb = 'Edition d\'un article';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Edition d'un article {$this->getRecord()->name}";
    }
}
