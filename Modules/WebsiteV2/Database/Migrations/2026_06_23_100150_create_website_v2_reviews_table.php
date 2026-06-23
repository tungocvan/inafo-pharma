<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_v2_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('wp_products')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('comment')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_verified_purchase')->default(false);
            $table->integer('likes')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_v2_reviews');
    }
};
