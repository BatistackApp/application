<?php

use App\Enums\Article\SerialNumberStatus;
use App\Models\Article\Article;
use App\Models\Core\Warehouse;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Article::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Warehouse::class)->nullable()->constrained()->nullOnDelete();
            $table->string('serial_number')->unique()->index();
            $table->string('status')->default(SerialNumberStatus::IN_STOCK->value);
            $table->foreignIdFor(User::class, 'assigned_user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('photo_plate_path')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_serial_numbers');
    }
};
