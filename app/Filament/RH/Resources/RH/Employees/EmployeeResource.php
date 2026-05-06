<?php

namespace App\Filament\RH\Resources\RH\Employees;

use App\Filament\RH\Resources\RH\Employees\Pages\CreateEmployee;
use App\Filament\RH\Resources\RH\Employees\Pages\EditEmployee;
use App\Filament\RH\Resources\RH\Employees\Pages\ListEmployees;
use App\Filament\RH\Resources\RH\Employees\Pages\ViewEmployee;
use App\Filament\RH\Resources\RH\Employees\Schemas\EmployeeForm;
use App\Filament\RH\Resources\RH\Employees\Tables\EmployeesTable;
use App\Models\RH\Employee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static ?string $modelLabel = 'Salarié';
    protected static ?string $pluralModelLabel = 'Salariés';
    protected static ?string $navigationLabel = 'Salariés';

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
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
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'view' => ViewEmployee::route('/{record}'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
