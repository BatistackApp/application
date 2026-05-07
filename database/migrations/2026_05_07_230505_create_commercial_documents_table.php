<?php

use App\Enums\Commerce\DocumentStatus;
use App\Models\Chantier\Chantier;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commercial_documents', function (Blueprint $table) {
            $table->id();

            $table->string('type')->index();
            $table->string('reference')->unique();
            $table->string('status')->default(DocumentStatus::DRAFT->value)->index();

            $table->foreignIdFor(Tiers::class, 'client_id')
                ->constrained('tiers')
                ->cascadeOnDelete();

            $table->foreignIdFor(Chantier::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Dates
            $table->date('date_document');
            $table->date('date_validite')->nullable()->comment('Pour devis uniquement');
            $table->date('date_echeance')->nullable()->comment('Pour factures uniquement');

            // Montants
            $table->decimal('total_ht', 15, 2)->default(0);
            $table->decimal('total_tva', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2)->default(0);

            // Remise globale (mutuellement exclusive avec remise par ligne)
            $table->decimal('remise_globale_pct', 5, 2)->default(0);
            $table->decimal('remise_globale_montant', 15, 2)->default(0);

            // Facturation par avancement (chantiers uniquement)
            $table->decimal('avancement_pct', 5, 2)->nullable()
                ->comment('% avancement chantier si facturation par avancement');

            // Textes
            $table->text('notes')->nullable();
            $table->text('conditions_reglement')->nullable();

            // Conversion
            $table->foreignId('parent_document_id')
                ->nullable()
                ->constrained('commercial_documents')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index(['client_id', 'type']);
            $table->index('date_document');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_documents');
    }
};
