<?php

namespace App\Filament\RH\Resources\RH\Employees\Tables;

use App\Enums\RH\TypeContrat;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('matricule')
                    ->label('Matricule')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type_contrat')
                    ->label('Contrat')
                    ->badge()
                    ->sortable(),

                TextColumn::make('taux_horaire')
                    ->label('Taux horaire')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('date_embauche')
                    ->label('Embauche')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type_contrat')
                    ->label('Type de contrat')
                    ->options(TypeContrat::class),

                TernaryFilter::make('is_active')
                    ->label('Actif')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ]);
    }
}
