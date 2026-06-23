<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_v2_flash_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_sale_id')->constrained('website_v2_flash_sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('wp_products')->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->integer('quantity')->default(0);
            $table->integer('sold')->default(0);

            $table->unique(['flash_sale_id', 'product_id'], 'website_v2_flash_sale_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_v2_flash_sale_items');
    }
};
