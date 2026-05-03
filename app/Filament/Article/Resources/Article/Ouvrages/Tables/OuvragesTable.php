<?php

namespace App\Filament\Article\Resources\Article\Ouvrages\Tables;

use App\Models\Article\Ouvrage;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OuvragesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('Référence')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Désignation')
                    ->searchable()
                    ->description(fn (Ouvrage $record) => new HtmlString($record->description)),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->sortable()
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('active')
                        ->label('Activer')
                        ->icon('heroicon-s-check')
                        ->color('success')
                        ->visible(fn (Ouvrage $record) => ! $record->is_active)
                        ->action(fn (Ouvrage $record) => $record->update(['is_active' => true])),

                    Action::make('inactive')
                        ->label('Désactiver')
                        ->icon(Heroicon::XMark)
                        ->color('danger')
                        ->visible(fn (Ouvrage $record) => $record->is_active)
                        ->action(fn (Ouvrage $record) => $record->update(['is_active' => false])),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
