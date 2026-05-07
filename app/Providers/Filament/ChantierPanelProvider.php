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

class ChantierPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('chantier')
            ->path('chantier')
            ->colors([
                'primary' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Chantier/Resources'), for: 'App\Filament\Chantier\Resources')
            ->discoverPages(in: app_path('Filament/Chantier/Pages'), for: 'App\Filament\Chantier\Pages')
            ->plugins([
                CalculatorPlugin::make(),
                TableLayoutTogglePlugin::make()
                    ->setDefaultLayout('list')
                    ->displayToggleAction(true),
                KnowledgeBaseCompanionPlugin::make()
                    ->knowledgeBasePanelId('knowledge-base'),
            ])
            ->discoverWidgets(in: app_path('Filament/Chantier/Widgets'), for: 'App\Filament\Chantier\Widgets')
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
            ->brandName('BATISTACK - Chantiers')
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
