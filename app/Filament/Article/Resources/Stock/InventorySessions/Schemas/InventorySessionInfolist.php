<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\Schemas;

use App\Models\Stock\InventorySession;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventorySessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('reference')
                            ->label('Référence')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),

                        TextEntry::make('warehouse.name')
                            ->label('Dépôt'),

                        TextEntry::make('opened_at')
                            ->label('Ouvert le')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('closed_at')
                            ->label('Fermé le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),

                        TextEntry::make('validated_at')
                            ->label('Validé le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),

                        TextEntry::make('creator.name')
                            ->label('Créé par'),

                        TextEntry::make('validator.name')
                            ->label('Validé par')
                            ->placeholder('—'),

                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Synthèse du comptage')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('lines_count')
                            ->label('Articles')
                            ->state(fn (InventorySession $record) => $record->lines()->count()),

                        TextEntry::make('counted_count')
                            ->label('Comptés')
                            ->state(fn (InventorySession $record) => $record->lines()
                                ->whereNotNull('counted_quantity')->count()
                            )
                            ->color('success'),

                        TextEntry::make('diff_count')
                            ->label('Avec écart')
                            ->state(fn (InventorySession $record) => $record->lines()
                                ->whereNotNull('counted_quantity')
                                ->whereRaw('counted_quantity != theoretical_quantity')
                                ->count()
                            )
                            ->color('danger'),

                        TextEntry::make('remaining_count')
                            ->label('Non comptés')
                            ->state(fn (InventorySession $record) => $record->lines()
                                ->whereNull('counted_quantity')->count()
                            )
                            ->color('warning'),
                    ]),
            ]);
    }
}
