<?php

namespace App\Notifications\RH;

use App\Models\RH\PointageSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PointageSoumisNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PointageSession $session) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Pointage soumis — {$this->session->employee->user->name}",
            'body' => "{$this->session->label_semaine} en attente de validation.",
            'session_id' => $this->session->id,
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->title("Pointage soumis — {$this->session->employee->user->name}")
            ->body("{$this->session->label_semaine} en attente de validation.")
            ->getDatabaseMessage();
    }
}
