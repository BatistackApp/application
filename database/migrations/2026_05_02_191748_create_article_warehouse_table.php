<?php

use App\Models\Article\Article;
use App\Models\Core\Warehouse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_warehouse', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Article::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Warehouse::class)->constrained()->cascadeOnDelete();
            $table->decimal('min_stock', 15, 3)->default(0);
            $table->decimal('max_stock', 15, 3)->default(0);
            $table->decimal('alert_stock', 15, 3)->default(0);
            $table->decimal('actual_stock', 15, 3)->default(0);
            $table->string('bin_location')->nullable()->comment('Emplacement précis (allée, étagère)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_warehouse');
    }
};
