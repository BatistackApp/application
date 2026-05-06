<?php

namespace App\Filament\RH\Resources\RH\PointageSessions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PointageSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('employee_id')
                            ->label('Salarié')
                            ->relationship('employee.user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('semaine_du')
                            ->label('Semaine du (lundi)')
                            ->default(now()->startOfWeek())
                            ->displayFormat('d/m/Y')
                            ->firstDayOfWeek(1)
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $lundi = Carbon::parse($state)->startOfWeek();
                                    $set('semaine_du', $lundi->toDateString());
                                }
                            }),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
