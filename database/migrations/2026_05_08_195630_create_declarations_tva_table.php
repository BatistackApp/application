<?php

use App\Models\Compta\ExerciceComptable;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('declarations_tva', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(ExerciceComptable::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('periode'); // Format: YYYY-MM ou YYYY-QN
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('regime'); // reel_normal | reel_simplifie

            // TVA collectée
            $table->decimal('base_tva_collectee_20', 15, 2)->default(0);
            $table->decimal('montant_tva_collectee_20', 15, 2)->default(0);
            $table->decimal('base_tva_collectee_10', 15, 2)->default(0);
            $table->decimal('montant_tva_collectee_10', 15, 2)->default(0);
            $table->decimal('base_tva_collectee_55', 15, 2)->default(0);
            $table->decimal('montant_tva_collectee_55', 15, 2)->default(0);
            $table->decimal('total_tva_collectee', 15, 2)->default(0);

            // TVA déductible
            $table->decimal('tva_deductible_immobilisations', 15, 2)->default(0);
            $table->decimal('tva_deductible_biens_services', 15, 2)->default(0);
            $table->decimal('total_tva_deductible', 15, 2)->default(0);

            // Résultat
            $table->decimal('tva_nette', 15, 2)->default(0)->comment('À payer si > 0, crédit si < 0');
            $table->decimal('credit_periode_precedente', 15, 2)->default(0);
            $table->decimal('tva_due', 15, 2)->default(0);

            // Statut
            $table->boolean('validee')->default(false);
            $table->timestamp('validee_at')->nullable();
            $table->foreignIdFor(User::class, 'validee_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('transmise')->default(false);
            $table->timestamp('transmise_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['exercice_comptable_id', 'periode']);
            $table->index(['periode', 'regime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declarations_tva');
    }
};
