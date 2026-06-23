<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_v2_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('website_v2_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('wp_products')->nullOnDelete();
            $table->string('product_name');
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('total', 15, 2)->default(0);
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_v2_order_items');
    }
};
