<?php

namespace App\Filament\Resources\CompanyInfos\Pages;

use App\Filament\Resources\CompanyInfos\CompanyInfoResource;
use Filament\Resources\Pages\ListRecords;

class ListCompanyInfos extends ListRecords
{
    protected static string $resource = CompanyInfoResource::class;

    protected static ?string $title = 'Mes entreprises';
}
