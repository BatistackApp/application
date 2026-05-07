<?php

namespace App\Filament\Widgets;

use App\Enums\RH\PointageStatus;
use App\Models\Chantier\Chantier;
use App\Models\RH\PointageSession;
use App\Models\Stock\StockMouvement;
use App\Models\Tiers\Tiers;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActiviteRecenteWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Agrégation des activités récentes
        $activites = collect();

        // Chantiers créés (5 derniers)
        Chantier::latest()->limit(5)->get()->each(function ($item) use (&$activites) {
            $activites->push([
                'type' => 'Chantier créé',
                'badge_color' => 'success',
                'description' => $item->reference.' — '.$item->nom,
                'date' => $item->created_at,
                'icon' => 'heroicon-o-building-office-2',
            ]);
        });

        // Mouvements stock (5 derniers)
        StockMouvement::with(['article'])->latest()->limit(5)->get()->each(function ($item) use (&$activites) {
            $activites->push([
                'type' => 'Mouvement stock',
                'badge_color' => 'info',
                'description' => $item->type->getLabel().' — '.$item->article->name,
                'date' => $item->created_at,
                'icon' => 'heroicon-o-arrow-path',
            ]);
        });

        // Pointages validés (5 derniers)
        PointageSession::with(['employee.user'])
            ->where('status', PointageStatus::VALIDATED)
            ->latest('validated_at')
            ->limit(5)
            ->get()
            ->each(function ($item) use (&$activites) {
                $activites->push([
                    'type' => 'Pointage validé',
                    'badge_color' => 'warning',
                    'description' => $item->employee->user->name.' — '.$item->label_semaine,
                    'date' => $item->validated_at,
                    'icon' => 'heroicon-o-check-circle',
                ]);
            });

        // Triers créés (5 derniers)
        Tiers::latest()->limit(5)->get()->each(function ($item) use (&$activites) {
            $activites->push([
                'type' => 'Tiers créé',
                'badge_color' => 'primary',
                'description' => $item->name.' — '.$item->category->getLabel(),
                'date' => $item->created_at,
                'icon' => 'heroicon-o-user-plus',
            ]);
        });

        // Tri par date décroissante et limitation à 15
        $activites = $activites->sortByDesc('date')->take(15)->values();

        return $table
            ->query(
                User::query()->whereRaw('1 = 0')
            )
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($rowLoop) => $activites[$rowLoop->index]['badge_color'] ?? 'gray')
                    ->state(fn ($rowLoop) => $activites[$rowLoop->index]['type'] ?? ''),

                TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->state(fn ($rowLoop) => $activites[$rowLoop->index]['description'] ?? ''),

                TextColumn::make('date')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->state(fn ($rowLoop) => $activites[$rowLoop->index]['date'] ?? null),
            ])
            ->heading('Activité récente')
            ->description('15 dernières actions sur la plateforme')
            ->paginated(false)
            ->recordAction(null)
            ->recordUrl(null);
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return new LengthAwarePaginator(
            $this->getTableRecords(),
            15,
            15
        );
    }
}
