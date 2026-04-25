<?php

namespace App\Filament\Tiers\Resources\Tiers\Pages;

use App\Enums\Tiers\TiersStatus;
use App\Filament\Tiers\Resources\Tiers\Actions\PrintAction;
use App\Filament\Tiers\Resources\Tiers\TiersResource;
use App\Models\Tiers\Tiers;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewTiers extends ViewRecord
{
    protected static string $resource = TiersResource::class;

    protected static ?string $title = 'Fiche du tier';

    protected static ?string $breadcrumb = 'Fiche du tier';

    protected function getHeaderActions(): array
    {
        return [
            PrintAction::action(),
            EditAction::make()
                ->icon('heroicon-o-pencil')
                ->iconButton()
                ->tooltip('Modifier le tier'),

            DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->iconButton()
                ->requiresConfirmation()
                ->tooltip('Supprimer le tier'),

            ActionGroup::make([
                Action::make('new_chantier')
                    ->label('Nouveau Chantier')
                    ->icon('heroicon-o-plus'),

                Action::make('change_status')
                    ->label('Changer le Statut')
                    ->icon('heroicon-o-check')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(TiersStatus::class),
                    ])
                    ->action(fn(array $data, Tiers $record) => $record->update(['status' => $data['status']])),
            ]),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Fiche du Tier: ' . $this->record->name;
    }
}
