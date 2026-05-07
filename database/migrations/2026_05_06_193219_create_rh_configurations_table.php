<?php

use App\Enums\RH\JourSemaine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rh_configurations', function (Blueprint $table) {
            $table->id();

            // Planning
            $table->decimal('heures_matin', 4, 2)->default(3.50);
            $table->decimal('heures_aprem', 4, 2)->default(4.00);
            $table->json('jours_travailles');

            // Trajet
            $table->boolean('prise_en_charge_trajet')->default(false);
            $table->decimal('taux_prise_en_charge_trajet', 5, 2)->default(0);

            // Grand déplacement
            $table->boolean('grand_deplacement_actif')->default(false);
            $table->decimal('grand_deplacement_montant_jour', 8, 2)->default(0);
            $table->decimal('grand_deplacement_montant_repas', 8, 2)->default(0);
            $table->decimal('grand_deplacement_montant_heberg', 8, 2)->default(0);

            // Panier repas
            $table->boolean('panier_repas_actif')->default(false);
            $table->decimal('panier_repas_montant', 8, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_configurations');
    }
};
