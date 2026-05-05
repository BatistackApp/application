<?php

namespace App\Filament\Chantier\Resources\Chantiers\Schemas;

use App\Enums\Tiers\TiersCategory;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ChantierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('nom')
                            ->label('Nom du chantier')
                            ->required()
                            ->columnSpan(2),

                        Select::make('client_id')
                            ->label('Client')
                            ->relationship(
                                name: 'client',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('category', TiersCategory::Customer),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('responsable_id')
                            ->label('Responsable')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->default(auth()->id()),

                        RichEditor::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ]),

                Section::make('Localisation')
                    ->columnSpanFull()
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        Textarea::make('adresse')
                            ->label('Adresse')
                            ->rows(2)
                            ->columnSpan(3),

                        TextInput::make('code_postal')
                            ->label('Code postal')
                            ->maxLength(10),

                        TextInput::make('ville')
                            ->label('Ville'),

                        TextInput::make('pays')
                            ->label('Pays')
                            ->default('France'),
                    ]),

                Section::make('Planification')
                    ->columnSpanFull()
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        DatePicker::make('date_debut_prevue')
                            ->label('Date de début prévue'),

                        DatePicker::make('date_fin_prevue')
                            ->label('Date de fin prévue')
                            ->after('date_debut_prevue'),

                        DatePicker::make('date_debut_reelle')
                            ->label('Date de début réelle'),

                        DatePicker::make('date_fin_reelle')
                            ->label('Date de fin réelle')
                            ->after('date_debut_reelle'),
                    ]),

                Section::make('Notes')
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('')
                            ->rows(4),
                    ]),
            ]);
    }
}
