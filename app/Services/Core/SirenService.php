<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Log;

class SirenService
{
    public string $api_key = '';

    public string $base_url = '';

    public function __construct()
    {
        $this->api_key = config('services.siren.api_key');
        $this->base_url = config('services.siren.base_url');
    }

    /**
     * Valide le format et l'algorithme de Luhn localement.
     * (Évite des appels API inutiles pour des numéros erronés)
     */
    public function isValid(string $identifier): bool
    {
        $identifier = preg_replace('/\s+/', '', $identifier);

        if (! preg_match('/^[0-9]{9}$|^[0-9]{14}$/', $identifier)) {
            return false;
        }

        return $this->luhnChecksum($identifier);
    }

    /**
     * Vérifie si le SIREN/SIRET existe réellement dans la base INSEE.
     */
    public function exists(string $identifier): bool
    {
        if (! $this->isValid($identifier)) {
            return false;
        }

        $identifier = preg_replace('/\s+/', '', $identifier);

        // On met en cache le résultat de l'existence pendant 30 jours
        return Cache::remember("insee_exists_{$identifier}", now()->addDay(), function () use ($identifier) {
            $isSiret = strlen($identifier) === 14;
            $endpoint = $isSiret ? "/siret/{$identifier}" : "/siren/{$identifier}";

            try {
                $response = Http::withHeaders([
                    'X-INSEE-Api-Key-Integration' => $this->api_key,
                ])
                    ->get($this->base_url.$endpoint.'?champs=siret,siren');

                return $response->successful();
            } catch (\Exception $e) {
                Log::error("Échec de vérification d'existence INSEE: ".$e->getMessage());

                return false;
            }
        });
    }

    /**
     * Récupère les informations complètes d'un établissement.
     */
    public function getInformation(string $identifier): ?array
    {
        if (! $this->isValid($identifier)) {
            return null;
        }

        $identifier = preg_replace('/\s+/', '', $identifier);

        return Cache::remember("insee_info_{$identifier}", now()->addDays(7), function () use ($identifier) {
            $isSiret = strlen($identifier) === 14;
            $endpoint = $isSiret ? "/siret/{$identifier}" : "/siren/{$identifier}";

            try {
                $response = Http::withHeaders([
                    'X-INSEE-Api-Key-Integration' => $this->api_key,
                ])
                    ->get($this->base_url.$endpoint);

                if ($response->successful()) {
                    return $response->json();
                }

                if ($response->status() === 404) {
                    return null;
                }

                Log::warning("Réponse INSEE inhabituelle ({$response->status()}): ".$response->body());

                return null;

            } catch (\Exception $e) {
                Log::error("Erreur critique lors de l'appel INSEE: ".$e->getMessage());

                return null;
            }
        });
    }

    /**
     * Algorithme de Luhn pour la validation des identifiants français.
     */
    private function luhnChecksum(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $number[$i];
            if ($i % 2 === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return $sum % 10 === 0;
    }
}
