<?php

use App\Models\Chantier\Chantier;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chantier_couts', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Chantier::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(User::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('type')->comment('ChantierCoutType');
            $table->string('designation');
            $table->decimal('montant_ht', 15, 2);
            $table->date('date_imputation');

            // Source polymorphique
            $table->nullableMorphs('source');

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['chantier_id', 'type']);
            $table->index(['chantier_id', 'date_imputation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantier_couts');
    }
};
