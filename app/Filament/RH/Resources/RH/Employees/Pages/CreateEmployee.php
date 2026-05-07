<?php

namespace App\Filament\RH\Resources\RH\Employees\Pages;

use App\Enums\Tiers\TiersAddressType;
use App\Enums\Tiers\TiersCategory;
use App\Enums\Tiers\TiersTypology;
use App\Enums\TypeAccount;
use App\Filament\RH\Resources\RH\Employees\EmployeeResource;
use App\Models\RH\Employee;
use App\Models\Tiers\Tiers;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        if ($data['dgpd_concilient'] === true && ! empty($data['email'])) {
            $user = $this->createUser($data);
        }

        $this->createTier($data);

        $emp = Employee::create([
            'type_contrat' => $data['type_contrat'],
            'taux_horaire' => $data['taux_horaire'],
            'date_embauche' => $data['date_embauche'],
            'date_fin_contrat' => $data['date_fin_contrat'],
            'jours_travailles' => $data['jours_travailles'],
            'user_id' => $user->id ?? null,
        ]);

        Notification::make()
            ->success()
            ->title('Salariés créer avec succès')
            ->send();

        return $emp;
    }

    private function createUser(array $data): Model
    {
        $password = Str::random(10);

        return User::create([
            'name' => $data['first_name'].' '.$data['last_name'],
            'email' => $data['email'],
            'type_account' => TypeAccount::EMPLOYEE->value,
            'password' => Hash::make($password),
        ]);
    }

    private function createTier(array $data): void
    {
        $tiers = Tiers::create([
            'civility' => $data['civility'],
            'name' => $data['first_name'].' '.$data['last_name'],
            'typology' => TiersTypology::Particulier,
            'category' => TiersCategory::Other,
            'dgpd_concilient' => $data['dgpd_concilient'],
        ]);

        $tiers->addresses()->create([
            'address_name' => 'Default',
            'address_type' => TiersAddressType::INVOICING,
            'address' => $data['address'],
            'postal_code' => $data['postal_code'],
            'city' => $data['city'],
            'country' => $data['country'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
        ]);

        $tiers->contacts()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'fonction' => 'Salariés',
            'tel_fix' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'dgcp_concilent' => $data['dgpd_concilient'],
        ]);

    }
}
