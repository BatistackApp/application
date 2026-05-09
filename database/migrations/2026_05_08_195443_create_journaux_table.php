<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('journaux', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('libelle');
            $table->string('type'); // VE, AC, BQ, OD...
            $table->boolean('actif')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['code', 'actif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journaux');
    }
};
