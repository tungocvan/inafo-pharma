<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_v2_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('website_v2_carts')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('wp_products')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total', 10, 2);
            $table->timestamps();

            $table->unique(['cart_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_v2_cart_items');
    }
};
