<?php

use App\Models\Commerce\CommercialDocument;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('relances', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(CommercialDocument::class, 'facture_id')
                ->constrained('commercial_documents')
                ->cascadeOnDelete();

            $table->foreignIdFor(User::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->date('date_relance');
            $table->string('type')->comment('email|courrier|appel');
            $table->text('contenu');
            $table->text('reponse_client')->nullable();

            $table->timestamps();

            $table->index(['facture_id', 'date_relance']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relances');
    }
};
