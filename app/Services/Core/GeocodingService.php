<?php

namespace App\Services\Core;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google.maps_key');
    }

    /**
     * Récupère les coordonnées géographiques (latitude/longitude) à partir d'une adresse.
     *
     * @param  string  $address  L'adresse à géocoder.
     * @return array|null Un tableau contenant 'latitude' et 'longitude', ou null en cas d'erreur.
     */
    public function getGeocodePlace(string $address): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('Google Maps API Key manquante dans la configuration.');

            return null;
        }

        try {
            $response_place = Http::withHeaders([
                'X-Goog-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->post('https://places.googleapis.com/v1/places:autocomplete', [
                    'input' => $address,
                ]);

            $dataPlace = $response_place->json();
            dd($dataPlace);

            $reponse_geo = Http::get('https://geocode.googleapis.com/v4/geocode/places/'.$dataPlace['suggestions'][0]['placePrediction']['placeId'].'?key='.$this->apiKey);

            if ($reponse_geo->failed()) {
                throw new Exception("Erreur de connexion à l'API Google Maps.");
            }

            $dataGeo = $reponse_geo->json();

            return [
                'latitude' => $dataGeo['location']['latitude'],
                'longitude' => $dataGeo['location']['longitude'],
            ];
        } catch (Exception $exception) {
            Log::error('Échec du calcul de distance Google : '.$exception->getMessage());

            return null;
        }
    }

    /**
     * Calcule la distance en kilomètres entre deux adresses via l'API Google Distance Matrix.
     *
     * @param  string  $addressStart  L'adresse de départ.
     * @param  string  $addressEnd  L'adresse d'arrivée.
     * @return float|null La distance en km, ou null en cas d'erreur ou d'itinéraire introuvable.
     */
    public function getDistanceInKm(string $addressStart, string $addressEnd): ?float
    {
        if (empty($this->apiKey)) {
            Log::error('Google Maps API Key manquante dans la configuration.');

            return null;
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $addressStart,
                'destinations' => $addressEnd,
                'mode' => 'driving',
                'key' => $this->apiKey,
                'units' => 'metric',
            ]);

            if ($response->failed()) {
                throw new Exception("Erreur de connexion à l'API Google Maps.");
            }

            $data = $response->json();

            // Vérification du statut global de la réponse
            if (($data['status'] ?? '') !== 'OK') {
                throw new Exception('Erreur API Google : '.($data['error_message'] ?? $data['status']));
            }

            // Extraction de la distance
            $element = $data['rows'][0]['elements'][0] ?? null;

            if (! $element || $element['status'] !== 'OK') {
                Log::warning("Impossible de trouver un itinéraire pour l'adresse : {$addressStart} -> {$addressEnd}");

                return null;
            }

            // Google retourne la distance en mètres
            $distanceInMeters = $element['distance']['value'];

            return round($distanceInMeters / 1000, 2);
        } catch (Exception $exception) {
            Log::error('Échec du calcul de distance Google : '.$exception->getMessage());

            return null;
        }
    }
}
