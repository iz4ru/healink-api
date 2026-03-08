<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->references('id')->cascadeOnDelete();
            $table->string('product_name');
            $table->string('barcode')->unique()->nullable();
            $table->string('batch_number')->nullable();
            $table->date('exp_date');
            $table->string('unit'); // (strip, botol, box, tablet)
            $table->decimal('buy_price', 10, 2);
            $table->decimal('sell_price', 10, 2);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(10);
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
