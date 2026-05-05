<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\Pages;

use App\Filament\Article\Resources\Stock\InventorySessions\InventorySessionResource;
use App\Models\Core\Warehouse;
use App\Services\Stock\InventorySessionService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateInventorySession extends CreateRecord
{
    protected static string $resource = InventorySessionResource::class;

    protected static ?string $title = 'Nouvelle session d\'inventaire';

    /**
     * On surcharge la création pour passer par le service
     * qui génère les lignes automatiquement.
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(InventorySessionService::class)->open(
                warehouse: Warehouse::findOrFail($data['warehouse_id']),
                user: auth()->user(),
                notes: $data['notes'] ?? null,
            );
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Impossible de créer la session')
                ->body(collect($e->errors())->flatten()->first())
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
