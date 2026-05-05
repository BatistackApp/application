<?php

use App\Enums\Chantier\ChantierStatus;
use App\Models\Tiers\Tiers;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chantiers', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->comment('Ex: CH-2026-001');
            $table->string('nom');
            $table->text('description')->nullable();

            $table->foreignIdFor(Tiers::class, 'client_id')
                ->constrained('tiers')
                ->cascadeOnDelete();

            $table->foreignIdFor(User::class, 'responsable_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status')->default(ChantierStatus::DRAFT->value)->index();

            // Localisation
            $table->text('adresse')->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->string('ville')->nullable();
            $table->string('pays')->default('France');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Planification
            $table->date('date_debut_prevue')->nullable();
            $table->date('date_fin_prevue')->nullable();
            $table->date('date_debut_reelle')->nullable();
            $table->date('date_fin_reelle')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantiers');
    }
};
