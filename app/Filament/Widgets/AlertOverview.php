<?php

namespace App\Filament\Widgets;

use App\Models\Core\CompanyInfo;
use Filament\Actions\Action;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class AlertOverview extends Widget implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.widgets.alert-overview';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    public function infolist(Schema $schema): Schema
    {
        $true_company = CompanyInfo::where('name', 'Demo Company')->exists();

        return $schema
            ->schema([
                Callout::make('Configuration requise')
                    ->description('Votre entreprise n\'est actuellement pas enregistrer dans le logiciel')
                    ->info()
                    ->icon(Phosphor::ExclamationMark)
                    ->visible(fn () => ! $true_company)
                    ->columnSpanFull()
                    ->controls([
                        Action::make('redirect')
                            ->label('Configurer')
                            ->color('danger')
                            ->icon(Phosphor::Wrench)
                            ->url('/core/company-infos'),
                    ]),

            ]);
    }
}
