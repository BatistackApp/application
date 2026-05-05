<?php

use App\Models\Chantier\ChantierBudgetLine;
use App\Models\Chantier\ChantierTask;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chantier_task_budget_line', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(ChantierTask::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(ChantierBudgetLine::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('allocation_pct', 5, 2)
                ->default(100)
                ->comment('% de la ligne budget alloué à cette tâche');

            $table->timestamps();

            $table->unique(
                ['chantier_task_id', 'chantier_budget_line_id'],
                'task_budget_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantier_task_budget_line');
    }
};
