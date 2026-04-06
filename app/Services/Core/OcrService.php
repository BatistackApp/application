<?php

namespace App\Services\Core;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OcrService
{
    public string $api_key = '';
    public string $model = '';
    public string $url = '';

    public function __construct()
    {
        $this->api_key = config('services.google.gemini_api_key');
        $this->model = 'gemini-2.5-flash';
        $this->url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->api_key}";
    }

    public function analyze(string $filePath, string $subject): array
    {
        try {
            $imageData = base64_encode(file_get_contents($filePath));
            $mimeType = mime_content_type($filePath);

            return match ($subject) {
                'receipt' => $this->analyzeReceipt($imageData, $mimeType),
            };
        } catch (\Throwable $exception) {
            \Log::error("Échec de l'analyse Gemini OCR : ".$exception->getMessage());
            return [];
        }
    }

    /**
     * @throws \Exception
     */
    private function analyzeReceipt(string $imageData, false|string $mimeType)
    {
        $prompt = "Analyse ce ticket de caisse et extrais les informations suivantes au format JSON strict :
                - title (nom de l'enseigne/marchand)
                - amount_total (nombre décimal, montant TTC)
                - amount_taxe (nombre décimal, montant de la TVA)
                - tax_rate (nombre décimal, le taux de TVA en pourcentage, ex: 20)
                - expensed_at (date au format YYYY-MM-DD)
                - vehicle_id (Recherche si tu voie une plaque d'immatriculation)
                - odometer (Recherche une série de chiffre manuscrite avec 'km' à la fin et affiche la en nombre entier)
                - category_id (Catégorie de frais en français)
                - siren (Détecte et affiche le Siret/Siren de l'entreprise)

                Si une information est manquante, retourne null pour ce champ.";

        $response = $this->call(
            $prompt,
            $mimeType,
            $imageData,
        );

        return json_decode($response, true) ?? [];
    }

    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    private function call(string $prompt, false|string $mimeType, string $imageData): mixed
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($this->url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inlineData' => [
                                    'mimeType' => $mimeType,
                                    'data' => $imageData,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            throw new \Exception('Erreur API Gemini: '.$response->body());
        }

        $result = $response->json();
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
    }
}
