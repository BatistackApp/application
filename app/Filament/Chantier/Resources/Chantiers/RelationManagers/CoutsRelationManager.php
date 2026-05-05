<?php

namespace App\Filament\Chantier\Resources\Chantiers\RelationManagers;

use App\Enums\Chantier\ChantierCoutType;
use App\Models\Chantier\ChantierCout;
use App\Services\Chantier\ChantierBudgetService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class CoutsRelationManager extends RelationManager
{
    protected static string $relationship = 'couts';

    protected static ?string $title = 'Coûts réels imputés';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('type')
                            ->label('Type de coût')
                            ->options(ChantierCoutType::class)
                            ->required(),

                        DatePicker::make('date_imputation')
                            ->label('Date')
                            ->default(now())
                            ->required(),
                    ]),

                TextInput::make('designation')
                    ->label('Désignation')
                    ->required()
                    ->columnSpanFull(),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('montant_ht')
                            ->label('Montant HT')
                            ->numeric()
                            ->suffix('€')
                            ->required(),

                        Textarea::make('note')
                            ->label('Note')
                            ->rows(2),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('date_imputation', 'desc')
            ->columns([
                TextColumn::make('date_imputation')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('designation')
                    ->label('Désignation')
                    ->searchable()
                    ->description(fn (ChantierCout $record) => $record->note),

                TextColumn::make('montant_ht')
                    ->label('Montant HT')
                    ->money('EUR')
                    ->alignRight()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Saisi par')
                    ->placeholder('—'),

                TextColumn::make('source_type')
                    ->label('Source')
                    ->formatStateUsing(fn (ChantierCout $record) => $record->source_type
                        ? class_basename($record->source_type)
                        : 'Manuel'
                    )
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(ChantierCoutType::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon(Phosphor::PlusCircle)
                    ->label('Imputer un coût')
                    ->using(function (array $data, ChantierBudgetService $service) {
                        return $service->imputerCout(
                            $this->getOwnerRecord(),
                            $data,
                        );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
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
        return $this->getOwnerRecord()->status->value === 'archived';
    }
}
