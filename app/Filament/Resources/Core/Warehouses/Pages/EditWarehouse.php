<?php

namespace App\Filament\Resources\Core\Warehouses\Pages;

use App\Filament\Resources\Core\Warehouses\WarehouseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditWarehouse extends EditRecord
{
    protected static string $resource = WarehouseResource::class;

    protected static ?string $breadcrumb = 'Edition';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Supprimer le dépot')
                ->icon('heroicon-s-trash'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Edition du dépot: '.$this->getRecord()->name;
    }
}
