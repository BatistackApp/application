<?php

use App\Models\Article\Article;
use App\Models\Chantier\Chantier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chantier_budget_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Chantier::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(Article::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('type')->comment('ChantierBudgetType');
            $table->string('designation');
            $table->decimal('quantite', 12, 3)->default(1);
            $table->string('unite')->nullable();
            $table->decimal('cout_unitaire', 15, 2)->default(0);

            // cout_total = quantite * cout_unitaire (colonne virtuelle)
            $table->decimal('cout_total', 15, 2)
                ->virtualAs('ROUND(quantite * cout_unitaire, 2)');

            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantier_budget_lines');
    }
};
