<?php

namespace App\Notifications\Chantier;

use App\Models\Chantier\Chantier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BudgetDepassementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Chantier $chantier,
        public float $budgetTotal,
        public float $coutReel,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $depassement = $this->coutReel - $this->budgetTotal;

        return \Filament\Notifications\Notification::make()
            ->warning()
            ->title("Dépassement budget — {$this->chantier->reference}")
            ->body("Le chantier {$this->chantier->nom} dépasse son budget de "
                .number_format($depassement, 2, ',', ' ').' €.')
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        $depassement = $this->coutReel - $this->budgetTotal;

        return [
            'title' => "Dépassement budget — {$this->chantier->reference}",
            'body' => "Le chantier {$this->chantier->nom} dépasse son budget de "
                .number_format($depassement, 2, ',', ' ').' €.',
            'chantier_id' => $this->chantier->id,
            'montant' => $depassement,
        ];
    }
}
