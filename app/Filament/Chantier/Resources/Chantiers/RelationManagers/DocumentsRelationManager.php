<?php

namespace App\Filament\Chantier\Resources\Chantiers\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documents';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nom')
                            ->label('Nom du document')
                            ->required(),

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'plan' => 'Plan',
                                'contrat' => 'Contrat',
                                'photo' => 'Photo',
                                'rapport' => 'Rapport',
                                'autre' => 'Autre',
                            ])
                            ->default('autre')
                            ->required(),
                    ]),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(2)
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('fichier')
                    ->label('Fichier')
                    ->collection('chantier_documents')
                    ->multiple(false)
                    ->downloadable()
                    ->openable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'plan' => 'Plan',
                        'contrat' => 'Contrat',
                        'photo' => 'Photo',
                        'rapport' => 'Rapport',
                        default => 'Autre',
                    }),

                TextColumn::make('description')
                    ->label('Description')
                    ->placeholder('—')
                    ->limit(50),

                TextColumn::make('user.name')
                    ->label('Ajouté par')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d/m/Y'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'plan' => 'Plan',
                        'contrat' => 'Contrat',
                        'photo' => 'Photo',
                        'rapport' => 'Rapport',
                        'autre' => 'Autre',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un document')
                    ->icon(Phosphor::PlusCircle)
                    ->mutateFormDataUsing(function (array $data) {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                DeleteAction::make(),
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
