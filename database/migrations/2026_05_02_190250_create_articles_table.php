<?php

use App\Enums\Article\TrackingType;
use App\Enums\UnitOfMesure;
use App\Models\Article\ArticleCategory;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ArticleCategory::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Tiers::class, 'default_supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->comment('Référence interne')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->default(UnitOfMesure::UNIT->value);
            $table->string('tracking_type')->default(TrackingType::QUANTITY->value);
            $table->string('barcode')->nullable()->index()->comment('EAN/UPC');
            $table->string('qr_code_base')->nullable()->unique()->comment('Etiquette QR Code interne');
            $table->decimal('poids', 12, 3)->nullable();
            $table->decimal('volume', 12, 3)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
