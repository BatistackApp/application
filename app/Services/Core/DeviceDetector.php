<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Request;

class DeviceDetector
{
    /**
     * Vérifie si l'utilisateur est sur mobile
     */
    public static function isMobile(): bool
    {
        $userAgent = Request::header('User-Agent');

        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPod', 'iPad',
            'Windows Phone', 'BlackBerry', 'webOS', 'Opera Mini',
        ];

        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si c'est un smartphone (pas tablette)
     */
    public static function isSmartphone(): bool
    {
        $userAgent = Request::header('User-Agent');

        // Exclure les tablettes
        if (stripos($userAgent, 'iPad') !== false ||
            stripos($userAgent, 'Tablet') !== false) {
            return false;
        }

        return self::isMobile();
    }

    /**
     * Détermine la plateforme
     */
    public static function getPlatform(): string
    {
        $userAgent = Request::header('User-Agent');

        if (stripos($userAgent, 'iPhone') !== false ||
            stripos($userAgent, 'iPad') !== false ||
            stripos($userAgent, 'iPod') !== false) {
            return 'ios';
        }

        if (stripos($userAgent, 'Android') !== false) {
            return 'android';
        }

        return 'desktop';
    }
}
