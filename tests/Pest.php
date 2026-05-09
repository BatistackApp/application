<?php

use App\Enums\Compta\CompteType;
use App\Enums\Compta\JournalType;
use App\Models\Compta\ExerciceComptable;
use App\Models\Compta\Journal;
use App\Models\Compta\PlanComptable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// Helpers globaux pour les tests compta
function createExerciceEnCours(): ExerciceComptable
{
    return ExerciceComptable::factory()->create([
        'libelle' => 'Exercice '.now()->year,
        'date_debut' => now()->startOfYear(),
        'date_fin' => now()->endOfYear(),
        'cloture' => false,
    ]);
}

function createJournalVentes(): Journal
{
    return Journal::firstOrCreate(
        ['code' => 'VE'],
        [
            'libelle' => 'Ventes',
            'type' => JournalType::VENTES,
            'actif' => true,
        ]
    );
}

function createComptesStandard(): array
{
    return [
        'client' => PlanComptable::firstOrCreate(
            ['numero' => '411000'],
            [
                'libelle' => 'Clients',
                'type' => CompteType::CLASSE_4,
                'actif' => true,
                'lettrable' => true,
            ]
        ),
        'produit' => PlanComptable::firstOrCreate(
            ['numero' => '707000'],
            [
                'libelle' => 'Ventes de marchandises',
                'type' => CompteType::CLASSE_7,
                'actif' => true,
                'analytique' => true,
            ]
        ),
        'tva_collectee' => PlanComptable::firstOrCreate(
            ['numero' => '445710'],
            [
                'libelle' => 'TVA collectée',
                'type' => CompteType::CLASSE_4,
                'actif' => true,
            ]
        ),
        'banque' => PlanComptable::firstOrCreate(
            ['numero' => '512000'],
            [
                'libelle' => 'Banque',
                'type' => CompteType::CLASSE_5,
                'actif' => true,
            ]
        ),
    ];
}

function something()
{
    // ..
}
