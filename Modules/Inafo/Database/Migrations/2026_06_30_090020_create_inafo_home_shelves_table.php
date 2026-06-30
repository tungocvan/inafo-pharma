<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inafo_home_shelves', function (Blueprint $table) {
            $table->id()->comment('Khoa chinh');
            $table->string('title')->comment('Tieu de ke san pham tren trang chu');
            $table->string('slug')->unique()->comment('Ma dinh danh ke san pham');
            $table->string('type', 60)->default('manual')->index()->comment('Kieu lay san pham: manual, newest, featured, best_seller');
            $table->string('view_more_url')->nullable()->comment('Duong dan xem them cua ke san pham');
            $table->foreignId('banner_id')->nullable()->constrained('inafo_home_banners')->nullOnDelete()->comment('Banner ngang gan voi ke san pham');
            $table->unsignedInteger('product_limit')->default(12)->comment('So san pham toi da hien thi');
            $table->unsignedInteger('position')->default(0)->index()->comment('Thu tu sap xep tang dan');
            $table->boolean('is_active')->default(true)->index()->comment('Trang thai kich hoat ke san pham');
            $table->timestamps();
        });

        $this->commentTable('inafo_home_shelves', 'Cau hinh cac ke san pham trang chu Inafo');
    }

    public function down(): void
    {
        Schema::dropIfExists('inafo_home_shelves');
    }

    private function commentTable(string $table, string $comment): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` COMMENT = '{$comment}'");
        }
    }
};
