<?php

namespace App\Services\Article;

class ArticleService
{
    public function generateSKU(): string
    {
        return 'ART'.mt_rand(100000, 999999);
    }
}
