<?php

use App\Models\Article\Article;
use App\Models\Article\Ouvrage;
use App\Models\Commerce\CommercialDocument;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commercial_document_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(CommercialDocument::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(Article::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignIdFor(Ouvrage::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('designation');
            $table->decimal('quantite', 12, 3);
            $table->string('unite')->nullable();

            $table->decimal('prix_unitaire_ht', 15, 2);
            $table->decimal('taux_tva', 5, 2)->default(20);

            // Remise ligne
            $table->decimal('remise_pct', 5, 2)->default(0);
            $table->decimal('remise_montant', 15, 2)->default(0);

            // Colonnes virtuelles calculées
            $table->decimal('total_ht', 15, 2)
                ->virtualAs('ROUND((quantite * prix_unitaire_ht) - remise_montant - ((quantite * prix_unitaire_ht) * remise_pct / 100), 2)');

            $table->decimal('total_tva', 15, 2)
                ->virtualAs('ROUND(((quantite * prix_unitaire_ht) - remise_montant - ((quantite * prix_unitaire_ht) * remise_pct / 100)) * taux_tva / 100, 2)');

            $table->decimal('total_ttc', 15, 2)
                ->virtualAs('ROUND(((quantite * prix_unitaire_ht) - remise_montant - ((quantite * prix_unitaire_ht) * remise_pct / 100)) * (1 + taux_tva / 100), 2)');

            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_document_lines');
    }
};
