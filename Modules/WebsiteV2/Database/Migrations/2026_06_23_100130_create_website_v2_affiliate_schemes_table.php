<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_v2_affiliate_schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('wp_products')->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('website_v2_affiliate_levels')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('commission_type', ['percentage', 'fixed', 'hybrid'])->default('percentage');
            $table->decimal('percent_value', 5, 2)->default(0);
            $table->decimal('fixed_value', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'level_id', 'user_id'], 'website_v2_scheme_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_v2_affiliate_schemes');
    }
};
