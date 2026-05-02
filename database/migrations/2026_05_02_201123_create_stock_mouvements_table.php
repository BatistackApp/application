<?php

use App\Enums\Article\StockMouvementType;
use App\Models\Article\Article;
use App\Models\Article\ArticleSerialNumber;
use App\Models\Article\Ouvrage;
use App\Models\Core\Warehouse;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Article::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Warehouse::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Warehouse::class, 'target_warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(ArticleSerialNumber::class, 'serial_number_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Ouvrage::class)->nullable()->constrained()->nullOnDelete();

            $table->string('type')->default(StockMouvementType::ADJUSTEMENT->value);
            $table->string('adjustement_type')->nullable();
            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('unit_cost_ht', 15, 2)->nullable();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mouvements');
    }
};
