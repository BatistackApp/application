<?php

use App\Enums\RH\PointageStatus;
use App\Models\RH\Employee;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pointage_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Employee::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->date('semaine_du')
                ->comment('Date du lundi de la semaine');

            $table->string('status')
                ->default(PointageStatus::DRAFT->value)
                ->index();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('imputed_at')->nullable();

            $table->foreignIdFor(User::class, 'validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Un salarié ne peut avoir qu'une session par semaine
            $table->unique(['employee_id', 'semaine_du'], 'session_employee_semaine_unique');
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointage_sessions');
    }
};
