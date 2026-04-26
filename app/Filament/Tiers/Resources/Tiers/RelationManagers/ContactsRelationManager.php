<?php

namespace App\Filament\Tiers\Resources\Tiers\RelationManagers;

use App\Models\Tiers\TiersContact;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
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
