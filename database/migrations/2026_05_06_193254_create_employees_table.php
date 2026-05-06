<?php

use App\Enums\RH\TypeContrat;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('matricule')->unique();
            $table->string('type_contrat')->default(TypeContrat::CDI->value);
            $table->decimal('taux_horaire', 8, 2);
            $table->date('date_embauche');
            $table->date('date_fin_contrat')->nullable();

            // Surcharge planning individuel
            $table->json('jours_travailles')->nullable()
                ->comment('Null = hérite de rh_configurations');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
