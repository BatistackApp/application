<?php

use App\Enums\Compta\EcritureStatus;
use App\Models\Compta\ExerciceComptable;
use App\Models\Compta\Journal;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ecritures', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(ExerciceComptable::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(Journal::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('numero_piece')->unique();
            $table->date('date_ecriture');
            $table->string('libelle');
            $table->string('status')->default(EcritureStatus::BROUILLON->value);

            // Source polymorphique (facture, paie, mouvement bancaire...)
            $table->morphs('source');

            // Traçabilité
            $table->foreignIdFor(User::class, 'created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignIdFor(User::class, 'validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('validated_at')->nullable();

            // Extourne
            $table->foreignId('extourne_ecriture_id')
                ->nullable()
                ->constrained('ecritures')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['date_ecriture', 'status']);
            $table->index('numero_piece');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecritures');
    }
};
