<?php

use App\Enums\Chantier\ChantierTaskStatus;
use App\Models\Chantier\Chantier;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chantier_tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Chantier::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('parent_task_id')
                ->nullable()
                ->constrained('chantier_tasks')
                ->nullOnDelete();

            $table->foreignId('depends_on_task_id')
                ->nullable()
                ->constrained('chantier_tasks')
                ->nullOnDelete();

            $table->foreignIdFor(User::class, 'assignee_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('designation');
            $table->text('description')->nullable();
            $table->string('status')->default(ChantierTaskStatus::TODO->value)->index();

            $table->date('date_debut');
            $table->date('date_fin');

            $table->unsignedTinyInteger('avancement_pct')
                ->default(0)
                ->comment('0 à 100');

            $table->integer('ordre')->default(0);
            $table->timestamps();

            $table->index(['chantier_id', 'status']);
            $table->index(['chantier_id', 'date_debut', 'date_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantier_tasks');
    }
};
