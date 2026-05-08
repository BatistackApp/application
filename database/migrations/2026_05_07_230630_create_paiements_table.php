<?php

use App\Enums\Commerce\ModePaiement;
use App\Models\Commerce\CommercialDocument;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(CommercialDocument::class, 'facture_id')
                ->constrained('commercial_documents')
                ->cascadeOnDelete();

            $table->date('date_paiement');
            $table->decimal('montant', 15, 2);
            $table->string('mode_paiement')->default(ModePaiement::VIREMENT->value);
            $table->string('reference_paiement')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['facture_id', 'date_paiement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
