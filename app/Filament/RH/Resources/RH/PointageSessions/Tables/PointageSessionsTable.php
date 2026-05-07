<?php

namespace App\Filament\RH\Resources\RH\PointageSessions\Tables;

use App\Enums\RH\PointageStatus;
use App\Models\RH\PointageSession;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PointageSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('semaine_du', 'desc')
            ->columns([
                TextColumn::make('employee.user.name')
                    ->label('Salarié')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('semaine_du')
                    ->label('Semaine')
                    ->date('d/m/Y')
                    ->description(fn (PointageSession $record) => $record->label_semaine)
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('submitted_at')
                    ->label('Soumis le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('validator.name')
                    ->label('Validé par')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(PointageStatus::class),

                SelectFilter::make('employee_id')
                    ->label('Salarié')
                    ->relationship('employee.user', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
