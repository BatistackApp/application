<?php

namespace App\Services\Article;

class OuvrageService
{
    public function generateSKU(): string
    {
        return 'OUV'.mt_rand(100000, 999999);
    }
}
