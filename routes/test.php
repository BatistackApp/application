<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    dd(app(\App\Services\Core\GeocodingService::class)->getGeocodePlace('2 Rue du Vieux Chateau, 85600 Montaigu-Vendée'));
});
