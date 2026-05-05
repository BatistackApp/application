<?php

namespace App\Filament\Article\Resources\Article\Articles\Pages;

use App\Filament\Article\Resources\Article\Articles\Actions\StockAdjustmentAction;
use App\Filament\Article\Resources\Article\Articles\Actions\StockEntryAction;
use App\Filament\Article\Resources\Article\Articles\Actions\StockExitAction;
use App\Filament\Article\Resources\Article\Articles\Actions\StockReturnAction;
use App\Filament\Article\Resources\Article\Articles\Actions\StockTransferAction;
use App\Filament\Article\Resources\Article\Articles\ArticleResource;
use Filament\Actions\ActionGroup;
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

            ActionGroup::make([
                StockEntryAction::make(),
                StockExitAction::make(),
                StockTransferAction::make(),
                StockAdjustmentAction::make(),
                StockReturnAction::make(),
            ])
                ->label('Mouvement de stock')
                ->icon('heroicon-s-arrows-right-left')
                ->color('gray')
                ->button(),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Article: {$this->getRecord()->name}";
    }
}
