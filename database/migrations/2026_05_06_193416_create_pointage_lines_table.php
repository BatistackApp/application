<?php

use App\Enums\RH\Periode;
use App\Enums\RH\TypeHeure;
use App\Models\Chantier\Chantier;
use App\Models\RH\PointageSession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pointage_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(PointageSession::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(Chantier::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->date('date');
            $table->string('periode')->default(Periode::MATIN->value);
            $table->string('type_heure')->default(TypeHeure::NORMALE->value);
            $table->decimal('heures', 5, 2)->default(0);

            // Trajet
            $table->decimal('heures_trajet', 5, 2)
                ->default(0)
                ->comment('Heures de trajet aller-retour');

            // Indemnités
            $table->boolean('panier_repas')->default(false);
            $table->boolean('grand_deplacement')->default(false);

            $table->text('note')->nullable();
            $table->timestamps();

            // Un salarié ne peut avoir qu'une ligne par date/période
            $table->unique(
                ['pointage_session_id', 'date', 'periode'],
                'pointage_line_session_date_periode_unique'
            );

            $table->index(['pointage_session_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointage_lines');
    }
};
