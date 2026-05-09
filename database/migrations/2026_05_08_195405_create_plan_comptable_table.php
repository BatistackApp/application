<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_comptable', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->string('libelle');
            $table->string('type'); // Classe 1-8
            $table->boolean('actif')->default(true);
            $table->boolean('lettrable')->default(false)->comment('Compte tiers lettrable');
            $table->boolean('analytique')->default(false)->comment('Ventilation analytique autorisée');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('numero');
            $table->index(['type', 'actif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_comptable');
    }
};
