<?php

namespace App\Services\Core;

class OcrService
{
    public string $api_key = '';

    public function __construct()
    {
        $this->api_key = config('services.google.gemini_api_key');
    }
}
