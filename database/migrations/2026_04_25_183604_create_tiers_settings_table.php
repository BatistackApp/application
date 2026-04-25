<?php

use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tiers_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tiers::class)->constrained()->cascadeOnDelete();
            $table->decimal('outstanding')->nullable();
            $table->boolean('followup')->default(false);
            $table->json('followup_terms')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiers_settings');
    }
};
