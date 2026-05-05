<?php

namespace App\Notifications\Stock;

use App\Models\Stock\InventorySession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InventorySessionValidatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public InventorySession $session) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->title("Inventaire {$this->session->reference} validé")
            ->body("Les ajustements de stock ont été appliqués automatiquement sur le dépôt {$this->session->warehouse->name}.")
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => "Inventaire {$this->session->reference} validé",
            'body'  => "Les ajustements de stock ont été appliqués automatiquement sur le dépôt {$this->session->warehouse->name}.",
            'session_id' => $this->session->id,
        ];
    }
}
