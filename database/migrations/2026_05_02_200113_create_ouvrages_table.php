<?php

use App\Models\Article\Article;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ouvrages', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->comment("Référence technique de l\'ouvrage");
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->default(\App\Enums\UnitOfMesure::UNIT->value);
            $table->boolean('is_active')->default(true);
            $table->foreignIdFor(Article::class)->nullable()->constrained()->nullOnDelete();
            $table->decimal('cump_ht', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ouvrages');
    }
};
