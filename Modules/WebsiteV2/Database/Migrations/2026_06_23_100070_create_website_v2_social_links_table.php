<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_v2_social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->string('name');
            $table->string('url');
            $table->string('icon_class')->nullable();
            $table->string('bg_color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_v2_social_links');
    }
};
