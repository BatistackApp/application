<?php

namespace App\Filament\Tiers\Resources\Tiers\Pages;

use App\Filament\Tiers\Resources\Tiers\TiersResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTiers extends CreateRecord
{
    protected static string $resource = TiersResource::class;

    protected static ?string $title = 'Nouveau tier';

    protected static ?string $breadcrumb = 'Nouveau tier';

    protected function handleRecordCreation(array $data): Model
    {
        $tiers = static::getModel()::create([
            'civility' => $data['civility'],
            'name' => $data['name'],
            'typology' => $data['typology'],
            'category' => $data['category'],
            'siren' => $data['siren'],
            'naf' => $data['ape'],
            'dgpd_concilient' => $data['dgpd_concilient'],
            'website' => $data['website'],
        ]);

        foreach ($data['addresses'] as $address) {
            $tiers->addresses()->create($address);
        }

        $tiers->contacts()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'fonction' => $data['fonction'],
            'tel_fix' => $data['tel_fix'],
            'tel_portable' => $data['tel_portable'],
            'email' => $data['email'],
            'dgcp_concilient' => $data['dgcp_concilient'],
        ]);

        $tiers->setting()->create([
            'outstanding' => $data['outstanding'],
            'followup' => $data['followup'],
            'followup_terms' => $data['followup_terms'],
        ]);

        return $tiers;
    }
}
