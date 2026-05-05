<?php

namespace App\Jobs\Chantier;

use App\Models\Chantier\Chantier;
use App\Services\Core\GeocodingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeocodeChantierJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Chantier $chantier) {}

    public function handle(GeocodingService $geocodingService): void
    {
        $adresse = "{$this->chantier->adresse}, {$this->chantier->code_postal} {$this->chantier->ville}";

        $location = $geocodingService->getGeocodePlace($adresse);

        if ($location) {
            $this->chantier->updateQuietly([
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);
        }
    }
}
