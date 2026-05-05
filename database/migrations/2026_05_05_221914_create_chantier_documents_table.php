<?php

use App\Models\Chantier\Chantier;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chantier_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Chantier::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(User::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('nom');
            $table->string('type')->default('autre')
                ->comment('plan|contrat|photo|rapport|autre');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantier_documents');
    }
};
