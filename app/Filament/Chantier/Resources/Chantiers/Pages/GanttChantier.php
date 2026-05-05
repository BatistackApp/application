<?php

namespace App\Filament\Chantier\Resources\Chantiers\Pages;

use App\Enums\Chantier\ChantierTaskStatus;
use App\Filament\Chantier\Resources\Chantiers\ChantierResource;
use App\Models\Chantier\Chantier;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class GanttChantier extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ChantierResource::class;

    protected static ?string $breadcrumb = 'Planning Gantt';

    protected string $view = 'filament.chantier.resources.chantiers.pages.gantt-chantier';

    public function mount(int|string $record): void
    {
        $this->record = Chantier::with([
            'tasks.assignee',
            'tasks.children',
            'tasks.budgetLines',
        ])->findOrFail($record);
    }

    public function getGanttData(): array
    {
        return $this->record->tasks
            ->sortBy('ordre')
            ->map(fn ($task) => [
                'id' => $task->id,
                'name' => $task->designation,
                'start' => $task->date_debut->format('Y-m-d'),
                'end' => $task->date_fin->format('Y-m-d'),
                'progress' => $task->avancement_pct,
                'assignee' => $task->assignee?->name,
                'status' => $task->status->value,
                'color' => match ($task->status) {
                    ChantierTaskStatus::DONE => '#22c55e',
                    ChantierTaskStatus::IN_PROGRESS => '#3b82f6',
                    ChantierTaskStatus::BLOCKED => '#ef4444',
                    default => '#94a3b8',
                },
                'dependencies' => $task->depends_on_task_id
                    ? (string) $task->depends_on_task_id
                    : '',
            ])
            ->values()
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_view')
                ->label('Retour à la fiche')
                ->icon(Phosphor::ArrowLeft)
                ->color('gray')
                ->url(ChantierResource::getUrl('view', ['record' => $this->record])),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Planning — {$this->record->reference}";
    }
}
