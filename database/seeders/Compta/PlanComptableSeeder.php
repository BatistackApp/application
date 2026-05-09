<?php

namespace Database\Seeders\Compta;

use App\Enums\Compta\CompteType;
use App\Models\Compta\PlanComptable;
use Illuminate\Database\Seeder;

class PlanComptableSeeder extends Seeder
{
    public function run(): void
    {
        $comptes = [
            // ─── CLASSE 1 : Capitaux ─────────────────────────────────────────
            ['101000', 'Capital social', CompteType::CLASSE_1],
            ['106000', 'Réserves', CompteType::CLASSE_1],
            ['108000', 'Compte de l\'exploitant', CompteType::CLASSE_1],
            ['120000', 'Résultat de l\'exercice', CompteType::CLASSE_1],
            ['164000', 'Emprunts auprès des établissements de crédit', CompteType::CLASSE_1],

            // ─── CLASSE 2 : Immobilisations ──────────────────────────────────
            ['211000', 'Terrains', CompteType::CLASSE_2],
            ['213000', 'Constructions', CompteType::CLASSE_2],
            ['215400', 'Matériel de chantier', CompteType::CLASSE_2],
            ['215500', 'Outillage de chantier', CompteType::CLASSE_2],
            ['218000', 'Installations générales', CompteType::CLASSE_2],
            ['218100', 'Installations techniques', CompteType::CLASSE_2],
            ['218200', 'Matériel de transport', CompteType::CLASSE_2],
            ['218300', 'Matériel de bureau', CompteType::CLASSE_2],
            ['218400', 'Mobilier', CompteType::CLASSE_2],
            ['218500', 'Matériel informatique', CompteType::CLASSE_2],

            // ─── CLASSE 3 : Stocks ────────────────────────────────────────────
            ['310000', 'Matières premières', CompteType::CLASSE_3],
            ['320000', 'Autres approvisionnements', CompteType::CLASSE_3],
            ['330000', 'En-cours de production de biens', CompteType::CLASSE_3],
            ['340000', 'En-cours de production de services', CompteType::CLASSE_3],

            // ─── CLASSE 4 : Tiers ─────────────────────────────────────────────
            ['400000', 'Fournisseurs', CompteType::CLASSE_4, true, false],
            ['401000', 'Fournisseurs - Achats de biens et prestations', CompteType::CLASSE_4, true, false],
            ['408000', 'Fournisseurs - Factures non parvenues', CompteType::CLASSE_4, true, false],
            ['410000', 'Clients', CompteType::CLASSE_4, true, false],
            ['411000', 'Clients - Ventes de biens ou prestations', CompteType::CLASSE_4, true, false],
            ['416000', 'Clients douteux', CompteType::CLASSE_4, true, false],
            ['418000', 'Clients - Factures à établir', CompteType::CLASSE_4, true, false],
            ['421000', 'Personnel - Rémunérations dues', CompteType::CLASSE_4, false, false],
            ['422000', 'Personnel - Œuvres sociales', CompteType::CLASSE_4, false, false],
            ['428000', 'Personnel - Charges à payer', CompteType::CLASSE_4, false, false],
            ['431000', 'Sécurité sociale', CompteType::CLASSE_4, false, false],
            ['437000', 'Autres organismes sociaux', CompteType::CLASSE_4, false, false],
            ['444000', 'État - Impôts sur les bénéfices', CompteType::CLASSE_4, false, false],
            ['445000', 'État - Taxes sur le chiffre d\'affaires', CompteType::CLASSE_4, false, false],
            ['445620', 'État - TVA déductible sur immobilisations', CompteType::CLASSE_4, false, false],
            ['445660', 'État - TVA déductible sur biens et services', CompteType::CLASSE_4, false, false],
            ['445710', 'État - TVA collectée', CompteType::CLASSE_4, false, false],
            ['447000', 'Autres impôts et taxes', CompteType::CLASSE_4, false, false],
            ['455000', 'Associés - Comptes courants', CompteType::CLASSE_4, false, false],

            // ─── CLASSE 5 : Financiers ────────────────────────────────────────
            ['512000', 'Banque', CompteType::CLASSE_5],
            ['530000', 'Caisse', CompteType::CLASSE_5],

            // ─── CLASSE 6 : Charges ───────────────────────────────────────────
            ['601000', 'Achats stockés - Matières premières', CompteType::CLASSE_6, false, true],
            ['602000', 'Achats stockés - Autres approvisionnements', CompteType::CLASSE_6, false, true],
            ['604000', 'Achats d\'études et prestations de services', CompteType::CLASSE_6, false, true],
            ['605000', 'Achats de matériel, équipements et travaux', CompteType::CLASSE_6, false, true],
            ['606100', 'Fournitures non stockables (eau, énergie...)', CompteType::CLASSE_6, false, true],
            ['606300', 'Fournitures d\'entretien et de petit équipement', CompteType::CLASSE_6, false, true],
            ['606400', 'Fournitures administratives', CompteType::CLASSE_6, false, true],
            ['611000', 'Sous-traitance générale', CompteType::CLASSE_6, false, true],
            ['613200', 'Locations immobilières', CompteType::CLASSE_6, false, true],
            ['613500', 'Locations mobilières', CompteType::CLASSE_6, false, true],
            ['615000', 'Entretien et réparations', CompteType::CLASSE_6, false, true],
            ['616000', 'Primes d\'assurance', CompteType::CLASSE_6, false, true],
            ['622600', 'Honoraires', CompteType::CLASSE_6, false, true],
            ['623100', 'Annonces et insertions', CompteType::CLASSE_6, false, true],
            ['625100', 'Voyages et déplacements', CompteType::CLASSE_6, false, true],
            ['625500', 'Réceptions', CompteType::CLASSE_6, false, true],
            ['626000', 'Frais postaux et de télécommunications', CompteType::CLASSE_6, false, true],
            ['627000', 'Services bancaires', CompteType::CLASSE_6, false, true],
            ['635100', 'Impôts et taxes sur rémunérations', CompteType::CLASSE_6, false, true],
            ['635500', 'Autres taxes', CompteType::CLASSE_6, false, true],
            ['641000', 'Salaires bruts', CompteType::CLASSE_6, false, true],
            ['645000', 'Charges de sécurité sociale', CompteType::CLASSE_6, false, true],
            ['661000', 'Charges d\'intérêts', CompteType::CLASSE_6, false, true],
            ['681000', 'Dotations aux amortissements', CompteType::CLASSE_6, false, true],

            // ─── CLASSE 7 : Produits ──────────────────────────────────────────
            ['706000', 'Prestations de services', CompteType::CLASSE_7, false, true],
            ['707000', 'Ventes de marchandises', CompteType::CLASSE_7, false, true],
            ['708000', 'Produits des activités annexes', CompteType::CLASSE_7, false, true],
            ['771000', 'Produits exceptionnels sur opérations de gestion', CompteType::CLASSE_7, false, true],
        ];

        foreach ($comptes as $compte) {
            PlanComptable::updateOrCreate(['numero' => $compte[0]], [
                'libelle' => $compte[1],
                'type' => $compte[2],
                'actif' => true,
                'lettrable' => $compte[3] ?? false,
                'analytique' => $compte[4] ?? false,
            ]);
        }

        $this->command->info('Plan comptable BTP initialisé : '.count($comptes).' comptes créés.');
    }
}
