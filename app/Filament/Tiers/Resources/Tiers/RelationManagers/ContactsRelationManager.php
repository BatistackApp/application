<?php

namespace App\Filament\Tiers\Resources\Tiers\RelationManagers;

use App\Models\Tiers\TiersContact;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Contacts';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('first_name')
                            ->label('Nom de famille')
                            ->required(),

                        TextInput::make('last_name')
                            ->label('Prénom')
                            ->required(),

                        TextInput::make('fonction')
                            ->label('Fonction / Poste'),
                    ]),

                Grid::make(4)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('tel_fixe')
                            ->label('Téléphone Fixe')
                            ->tel(),

                        TextInput::make('tel_portable')
                            ->label('Téléphone Portable')
                            ->tel(),

                        TextInput::make('email')
                            ->label('Email')
                            ->email(),

                        Checkbox::make('dgcp_concilent')
                            ->label('Autorise la réutilisation de ses données personnelles'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('first_name')
                    ->label('Identité')
                    ->formatStateUsing(fn (TiersContact $record) => $record->getFullName()),

                TextColumn::make('fonction')
                    ->label('Fonction'),

                TextColumn::make('email')
                    ->label('Coordonnée')
                    ->formatStateUsing(fn (TiersContact $record) => view('filament.tiers.info_contact', [
                        'tel_fix' => $record->tel_fix,
                        'tel_portable' => $record->tel_portable,
                        'email' => $record->email,
                    ])),

                IconColumn::make('dgcp_concilent')
                    ->label('RGPD')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('Nouveau Contact')->icon(Phosphor::PlusCircle),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
