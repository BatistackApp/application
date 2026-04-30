<?php

namespace App\Filament\Tiers\Resources\Tiers\RelationManagers;

use App\Enums\Tiers\TiersMailerStatus;
use App\Mail\Tiers\SendMailerPublishMail;
use App\Models\Tiers\TiersMailer;
use App\Services\Tiers\TiersDocumentGenerator;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class TiersMailerRelationManager extends RelationManager
{
    protected static string $relationship = 'mailers';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('subject')
                            ->label('Objet')
                            ->required()
                            ->maxLength(255),

                        RichEditor::make('content')
                            ->label('Contenu')
                            ->required(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                TextColumn::make('subject')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->label('Etat'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Etat du publipostage')
                    ->options(TiersMailerStatus::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading('Nouveau publipostage')
                    ->label('Nouveau publipostage')
                    ->icon(Phosphor::PlusCircle),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading('Editer le publipostage')
                        ->label('Editer le publipostage')
                        ->icon(Phosphor::Pencil),

                    DeleteAction::make()
                        ->label('Supprimer le publipostage')
                        ->icon(Phosphor::Trash)
                        ->requiresConfirmation(),

                    Action::make('print')
                        ->label('Imprimer le publipostage')
                        ->icon(Phosphor::Printer)
                        ->action(function (TiersDocumentGenerator $generator, TiersMailer $record) {
                            $tiers = $record->tiers;

                            $pdf = $generator->letter($tiers, $record->subject, $record->content);

                            return response()->download($pdf);
                        }),

                    Action::make('sending')
                        ->label('Envoyer par mail')
                        ->icon(Phosphor::PaperPlane)
                        ->action(function (TiersDocumentGenerator $generator, TiersMailer $record) {
                            try {
                                $mails = $record->tiers->contacts()->get();
                                $generator->letter($record->tiers, $record->subject, $record->content);

                                foreach ($mails as $mail) {
                                    Mail::to($mail->email)->send(new SendMailerPublishMail($record));
                                }

                                Notification::make()
                                    ->success()
                                    ->title('Mail envoyer avec succès')
                                    ->send();
                            } catch (\Exception $exception) {
                                Log::error($exception);
                            }
                        }),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
