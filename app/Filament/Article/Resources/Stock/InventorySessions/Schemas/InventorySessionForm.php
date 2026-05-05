<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\Schemas;

use App\Models\Core\Warehouse;
use App\Models\Stock\InventorySession;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventorySessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Nouvelle session d\'inventaire')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Dépôt à inventorier')
                            ->options(function () {
                                return Warehouse::where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($warehouse) {
                                        $hasActive = InventorySession::where('warehouse_id', $warehouse->id)
                                            ->whereIn('status', ['open', 'counting'])
                                            ->exists();

                                        $label = $hasActive
                                            ? "{$warehouse->name} ⚠ session en cours"
                                            : $warehouse->name;

                                        return [$warehouse->id => $label];
                                    });
                            })
                            ->searchable()
                            ->required(),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Instructions particulières, périmètre du comptage...'),
                    ]),
            ]);
    }
}
