<?php

namespace App\Notifications\Stock;

use App\Models\Article\Article;
use App\Models\Core\Warehouse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class StockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Article $article,
        public Warehouse $warehouse,
        public float $actualStock,
        public float $alertStock,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->warning()
            ->title('Alerte stock - '.$this->article->name)
            ->body("Stock actuel ({$this->actualStock}) ≤ seuil d'alerte ({$this->alertStock}) dans le dépôt {$this->warehouse->name}.")
            ->getDatabaseMessage();
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => "Alerte stock — {$this->article->name}",
            'body' => "Stock actuel ({$this->actualStock}) ≤ seuil d'alerte ({$this->alertStock}) dans le dépôt {$this->warehouse->name}.",
            'article_id' => $this->article->id,
            'warehouse_id' => $this->warehouse->id,
        ];
    }
}
