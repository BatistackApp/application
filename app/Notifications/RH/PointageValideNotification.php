<?php

namespace App\Notifications\RH;

use App\Models\RH\PointageSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PointageValideNotification extends Notification implements ShouldQueue
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
            'title' => 'Pointage validé et imputé',
            'body' => "{$this->session->label_semaine} a été validée et les coûts ont été imputés aux chantiers.",
            'session_id' => $this->session->id,
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->title('Pointage validé et imputé')
            ->body("{$this->session->label_semaine} a été validée et les coûts ont été imputés aux chantiers.")
            ->getDatabaseMessage();
    }
}
