<?php

use App\Models\Article\Article;
use App\Models\Stock\InventorySession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(InventorySession::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->constrained()->cascadeOnDelete();
            $table->decimal('theoretical_quantity', 15, 3);
            $table->decimal('counted_quantity', 15, 3)->nullable();
            $table->decimal('difference')->virtualAs('counted_quantity - theoretical_quantity');
            $table->timestamps();

            $table->unique(['inventory_session_id', 'article_id'], 'inventory_lines_session_article_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_lines');
        Schema::table('inventory_lines', function (Blueprint $table) {
            $table->dropUnique('inventory_lines_session_article_unique');
        });
    }
};
