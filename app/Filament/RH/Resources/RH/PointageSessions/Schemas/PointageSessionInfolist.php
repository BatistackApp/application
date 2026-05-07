<?php

namespace App\Filament\RH\Resources\RH\PointageSessions\Schemas;

use App\Models\RH\PointageSession;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PointageSessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Session')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('employee.user.name')
                            ->label('Salarié'),

                        TextEntry::make('label_semaine')
                            ->label('Semaine'),

                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),

                        TextEntry::make('submitted_at')
                            ->label('Soumis le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),

                        TextEntry::make('validator.name')
                            ->label('Validé par')
                            ->placeholder('—'),

                        TextEntry::make('validated_at')
                            ->label('Validé le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),

                        TextEntry::make('rejection_reason')
                            ->label('Motif de rejet')
                            ->placeholder('—')
                            ->columnSpanFull()
                            ->visible(fn (PointageSession $record) => $record->rejection_reason !== null)
                            ->color('danger'),
                    ]),
            ]);
    }
}
