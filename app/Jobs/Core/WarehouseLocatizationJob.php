<?php

namespace App\Jobs\Core;

use App\Models\Core\Warehouse;
use App\Services\Core\GeocodingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WarehouseLocatizationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Warehouse $warehouse) {}

    public function handle(GeocodingService $geocodingService): void
    {
        $location = $geocodingService->getGeocodePlace($this->warehouse->location);

        $this->warehouse->updateQuietly([
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
        ]);
    }
}
