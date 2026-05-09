<?php

use App\Models\Chantier\Chantier;
use App\Models\Compta\Ecriture;
use App\Models\Compta\PlanComptable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lignes_ecriture', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Ecriture::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(PlanComptable::class, 'compte_id')
                ->constrained('plan_comptable')
                ->cascadeOnDelete();

            $table->string('sens'); // debit | credit
            $table->decimal('montant', 15, 2);
            $table->string('libelle');

            // Analytique (optionnel)
            $table->foreignIdFor(Chantier::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Lettrage (pour comptes tiers)
            $table->string('lettrage', 10)->nullable();
            $table->date('date_lettrage')->nullable();

            $table->integer('ordre')->default(0);
            $table->timestamps();

            $table->index(['compte_id', 'sens']);
            $table->index('lettrage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_ecriture');
    }
};
