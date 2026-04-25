<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tiers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('civility')->nullable();
            $table->string('name');
            $table->string('typology');
            $table->string('category');
            $table->string('siren')->nullable();
            $table->string('naf')->nullable();
            $table->string('num_tva')->nullable();
            $table->boolean('dgpd_concilient')->default(true);
            $table->string('status')->default(\App\Enums\Tiers\TiersStatus::ACTIF->value);
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiers');
    }
};
