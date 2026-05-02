<?php

namespace App\Filament\Article\Resources\ArticleCategories;

use App\Filament\Article\Resources\ArticleCategories\Pages\CreateArticleCategory;
use App\Filament\Article\Resources\ArticleCategories\Pages\EditArticleCategory;
use App\Filament\Article\Resources\ArticleCategories\Pages\TreeCategories;
use App\Filament\Article\Resources\ArticleCategories\Pages\ViewArticleCategory;
use App\Filament\Article\Resources\ArticleCategories\Schemas\ArticleCategoryForm;
use App\Filament\Article\Resources\ArticleCategories\Schemas\ArticleCategoryInfolist;
use App\Filament\Article\Resources\ArticleCategories\Tables\ArticleCategoriesTable;
use App\Models\Article\ArticleCategory;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Openplain\FilamentTreeView\Fields\IconField;
use Openplain\FilamentTreeView\Fields\TextField;
use Openplain\FilamentTreeView\Tree;
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

    public static function tree(Tree $tree): Tree
    {
        return $tree
            ->fields([
                TextField::make('name'),
                IconField::make('is_active'),
            ])
            ->recordActions([
                Action::make('editModal')
                    ->iconButton()
                    ->icon('heroicon-s-pencil')
                    ->tooltip('Editer')
                    ->fillForm(fn (ArticleCategory $record): array => [
                        'name' => $record->name,
                    ])
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Désignation')
                                    ->required(),

                                Select::make('parent_id')
                                    ->relationship('parent', 'name'),

                                TextInput::make('order')
                                    ->label('Ordre')
                                    ->default(0)
                                    ->numeric(),

                                Toggle::make('is_active')
                                    ->label('Actif'),
                            ]),
                    ])
                    ->action(function (ArticleCategory $record, array $data) {
                        $record->update($data);

                        Notification::make()
                            ->title('Catégorie mise à jours')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->iconButton()
                    ->icon('heroicon-s-trash')
                    ->tooltip('Supprimer')
                    ->modalDescription(function (ArticleCategory $record) {
                        $count = $record->descendants()->count();

                        if ($count === 0) {
                            return 'Êtes-vous sûr de vouloir supprimer cette catégorie ?';
                        }

                        return "Cette catégorie comporte {$count} descendants qui seront également supprimés.";
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => TreeCategories::route('/'),
            'create' => CreateArticleCategory::route('/create'),
        ];
    }
}
