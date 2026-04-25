<?php

namespace App\Filament\Tiers\Resources\Tiers\Tables;

use App\Enums\Tiers\TiersCategory;
use App\Enums\Tiers\TiersStatus;
use App\Filament\Tiers\Resources\Tiers\Actions\PrintAction;
use App\Models\Tiers\Tiers;
use App\Services\Tiers\TiersDocumentGenerator;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use TinusG\FilamentCompanyLogoColumn\CompanyLogoColumn;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class TiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                CompanyLogoColumn::make('website')
                    ->label('')
                    ->tooltip(fn (Tiers $record): string => $record->name),

                TextColumn::make('code')
                    ->label('Code Tiers')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nom / Raison Social')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Categorie')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(TiersCategory::class),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(TiersStatus::class),
            ])
            ->recordActions([
                ViewAction::make()->iconButton()->tooltip('Voir'),
                EditAction::make()->iconButton()->tooltip('Modifier'),
            ])
            ->headerActions([
                PrintAction::action(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
