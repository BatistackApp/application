<?php

namespace App\Filament\Article\Resources\Article\Articles;

use App\Filament\Article\Resources\Article\Articles\Pages\CreateArticle;
use App\Filament\Article\Resources\Article\Articles\Pages\EditArticle;
use App\Filament\Article\Resources\Article\Articles\Pages\ListArticles;
use App\Filament\Article\Resources\Article\Articles\Pages\ViewArticle;
use App\Filament\Article\Resources\Article\Articles\RelationManagers\PricesRelationManager;
use App\Filament\Article\Resources\Article\Articles\RelationManagers\SerialNumbersRelationManager;
use App\Filament\Article\Resources\Article\Articles\RelationManagers\WarehousesRelationManager;
use App\Filament\Article\Resources\Article\Articles\Schemas\ArticleForm;
use App\Filament\Article\Resources\Article\Articles\Schemas\ArticleInfolist;
use App\Filament\Article\Resources\Article\Articles\Tables\ArticlesTable;
use App\Models\Article\Article;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArchiveBox;
    protected static ?string $navigationLabel = 'Articles';
    protected static ?string $modelLabel = 'Article';
    protected static ?string $pluralModelLabel = 'Articles';
    protected static string | UnitEnum | null $navigationGroup = 'Articles';

    public static function form(Schema $schema): Schema
    {
        return ArticleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ArticleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticlesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PricesRelationManager::class,
            WarehousesRelationManager::class,
            SerialNumbersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'view' => ViewArticle::route('/{record}'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
