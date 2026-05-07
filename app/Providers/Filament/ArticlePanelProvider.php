<?php

namespace App\Providers\Filament;

use Ariefng\FilamentCalculator\CalculatorPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Guava\FilamentKnowledgeBase\Plugins\KnowledgeBaseCompanionPlugin;
use Hydrat\TableLayoutToggle\TableLayoutTogglePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ArticlePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('article')
            ->path('article')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Article/Resources'), for: 'App\Filament\Article\Resources')
            ->discoverPages(in: app_path('Filament/Article/Pages'), for: 'App\Filament\Article\Pages')
            ->discoverWidgets(in: app_path('Filament/Article/Widgets'), for: 'App\Filament\Article\Widgets')
            ->plugins([
                CalculatorPlugin::make(),
                TableLayoutTogglePlugin::make()
                    ->setDefaultLayout('list')
                    ->displayToggleAction(true),
                KnowledgeBaseCompanionPlugin::make()
                    ->knowledgeBasePanelId('knowledge-base'),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->databaseNotifications()
            ->brandName('BATISTACK - Articles')
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
