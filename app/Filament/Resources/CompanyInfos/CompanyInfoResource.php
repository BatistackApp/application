<?php

namespace App\Filament\Resources\CompanyInfos;

use App\Filament\Resources\CompanyInfos\Pages\EditCompanyInfo;
use App\Filament\Resources\CompanyInfos\Pages\ListCompanyInfos;
use App\Filament\Resources\CompanyInfos\Schemas\CompanyInfoForm;
use App\Filament\Resources\CompanyInfos\Tables\CompanyInfosTable;
use App\Models\Core\CompanyInfo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyInfoResource extends Resource
{
    protected static ?string $model = CompanyInfo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::HomeModern;

    protected static ?string $navigationLabel = 'Entreprise';

    protected static ?string $breadcrumb = 'Entreprise';

    public static function form(Schema $schema): Schema
    {
        return CompanyInfoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyInfosTable::configure($table);
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
            'index' => ListCompanyInfos::route('/'),
            'edit' => EditCompanyInfo::route('/{record}/edit'),
        ];
    }
}
