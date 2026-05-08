<?php

namespace App\Services\Core;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Exceptions\ReportableHandler;
use Illuminate\Support\Facades\Http;

class ReportException
{
    public function report(Exceptions $exceptions): ReportableHandler
    {
        return $exceptions->reportable(function (\Throwable $e) {
            $className = get_class($e);
            $file = $e->getFile();
            $module = 'Core';

            if (
                str_contains($file, 'app/Filament/Article') ||
                str_contains($file, 'app/Enums/Article') ||
                str_contains($file, 'app/Jobs/Article') ||
                str_contains($file, 'app/Mails/Article') ||
                str_contains($file, 'app/Models/Article') ||
                str_contains($file, 'app/Notifications/Article') ||
                str_contains($file, 'app/Observers/Article')
            ) {
                $module = 'Articles & Stocks';
            }

            if (
                str_contains($file, 'app/Filament/Chantier') ||
                str_contains($file, 'app/Enums/Chantier') ||
                str_contains($file, 'app/Jobs/Chantier') ||
                str_contains($file, 'app/Mails/Chantier') ||
                str_contains($file, 'app/Models/Chantier') ||
                str_contains($file, 'app/Notifications/Chantier') ||
                str_contains($file, 'app/Observers/Chantier')
            ) {
                $module = 'Chantiers';
            }

            if (
                str_contains($file, 'app/Filament/Commerce') ||
                str_contains($file, 'app/Enums/Commerce') ||
                str_contains($file, 'app/Jobs/Commerce') ||
                str_contains($file, 'app/Mails/Commerce') ||
                str_contains($file, 'app/Models/Commerce') ||
                str_contains($file, 'app/Notifications/Commerce') ||
                str_contains($file, 'app/Observers/Commerce')
            ) {
                $module = 'Commerce & Facturation';
            }

            if (
                str_contains($file, 'app/Filament/RH') ||
                str_contains($file, 'app/Enums/RH') ||
                str_contains($file, 'app/Jobs/RH') ||
                str_contains($file, 'app/Mails/RH') ||
                str_contains($file, 'app/Models/RH') ||
                str_contains($file, 'app/Notifications/RH') ||
                str_contains($file, 'app/Observers/RH')
            ) {
                $module = 'RH - Resources Humaines';
            }

            if (
                str_contains($file, 'app/Filament/Tiers') ||
                str_contains($file, 'app/Enums/Tiers') ||
                str_contains($file, 'app/Jobs/Tiers') ||
                str_contains($file, 'app/Mails/Tiers') ||
                str_contains($file, 'app/Models/Tiers') ||
                str_contains($file, 'app/Notifications/Tiers') ||
                str_contains($file, 'app/Observers/Tiers')
            ) {
                $module = 'Tiers';
            }

            Http::async()->withHeaders([
                'X-N8N-TOKEN' => config('services.n8n.token'),
            ])
                ->post(config('services.n8n.webhook_url'), [
                    'module' => $module,
                    'exception' => $className,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => request()->fullUrl(),
                    'user_id' => auth()->id() ?? 'Invité',
                    'env' => app()->environment(),
                    'trace' => substr($e->getTraceAsString(), 0, 1000), // On limite la taille
                ]);
        });
    }
}
