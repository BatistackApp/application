<?php

namespace Database\Seeders\Compta;

use App\Enums\Compta\JournalType;
use App\Models\Compta\Journal;
use Illuminate\Database\Seeder;

class JournauxSeeder extends Seeder
{
    public function run(): void
    {
        $journaux = [
            ['VE', 'Ventes', JournalType::VENTES],
            ['AC', 'Achats', JournalType::ACHATS],
            ['BQ', 'Banque', JournalType::BANQUE],
            ['CA', 'Caisse', JournalType::CAISSE],
            ['OD', 'Opérations diverses', JournalType::OPERATIONS_DIVERSES],
            ['AN', 'À-nouveaux', JournalType::A_NOUVEAUX],
            ['PAIE', 'Paie', JournalType::PAIE],
        ];

        foreach ($journaux as [$code, $libelle, $type]) {
            Journal::create([
                'code' => $code,
                'libelle' => $libelle,
                'type' => $type,
                'actif' => true,
            ]);
        }

        $this->command->info('Journaux comptables initialisés : '.count($journaux).' journaux créés.');
    }
}
