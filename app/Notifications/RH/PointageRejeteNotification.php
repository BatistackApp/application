<?php

namespace App\Notifications\RH;

use App\Models\RH\PointageSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PointageRejeteNotification extends Notification implements ShouldQueue
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
            'title' => 'Pointage rejeté',
            'body' => "{$this->session->label_semaine} a été rejetée. Motif : {$this->session->rejection_reason}",
            'session_id' => $this->session->id,
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->danger()
            ->title('Pointage Rejeté')
            ->body("{$this->session->label_semaine} a été rejetée. Motif : {$this->session->rejection_reason}")
            ->getDatabaseMessage();
    }
}
