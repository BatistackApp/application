<?php

namespace App\Filament\Article\Resources\ArticleCategories;

use App\Filament\Article\Resources\ArticleCategories\Pages\CreateArticleCategory;
use App\Filament\Article\Resources\ArticleCategories\Pages\EditArticleCategory;
use App\Filament\Article\Resources\ArticleCategories\Pages\ListArticleCategories;
use App\Filament\Article\Resources\ArticleCategories\Pages\ViewArticleCategory;
use App\Filament\Article\Resources\ArticleCategories\Schemas\ArticleCategoryForm;
use App\Filament\Article\Resources\ArticleCategories\Schemas\ArticleCategoryInfolist;
use App\Filament\Article\Resources\ArticleCategories\Tables\ArticleCategoriesTable;
use App\Models\Article\ArticleCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ArticleCategoryResource extends Resource
{
    protected static ?string $model = ArticleCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Tag;

    protected static ?string $navigationLabel = 'Catégories';

    protected static ?string $modelLabel = 'Catégorie';

    protected static ?string $pluralModelLabel = 'Categories';

    protected static string|UnitEnum|null $navigationGroup = 'Articles';

    public static function form(Schema $schema): Schema
    {
        return ArticleCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ArticleCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticleCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticleCategories::route('/'),
            'create' => CreateArticleCategory::route('/create'),
            'view' => ViewArticleCategory::route('/{record}'),
            'edit' => EditArticleCategory::route('/{record}/edit'),
        ];
    }
}
