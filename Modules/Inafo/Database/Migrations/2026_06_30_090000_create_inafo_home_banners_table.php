<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inafo_home_banners', function (Blueprint $table) {
            $table->id()->comment('Khoa chinh');
            $table->string('placement', 80)->index()->comment('Vi tri hien thi: hero_main, hero_side, shelf_strip');
            $table->string('title')->comment('Tieu de banner hien thi tren storefront');
            $table->string('subtitle')->nullable()->comment('Mo ta ngan hoac thong diep phu cua banner');
            $table->string('image_desktop_url')->nullable()->comment('URL anh desktop cua banner');
            $table->string('image_mobile_url')->nullable()->comment('URL anh mobile cua banner');
            $table->string('target_url')->nullable()->comment('Duong dan khi nguoi dung bam vao banner');
            $table->string('button_label', 120)->nullable()->comment('Nhan nut hanh dong tren banner');
            $table->unsignedInteger('position')->default(0)->index()->comment('Thu tu sap xep tang dan');
            $table->timestamp('starts_at')->nullable()->index()->comment('Thoi diem bat dau hien thi banner');
            $table->timestamp('ends_at')->nullable()->index()->comment('Thoi diem ket thuc hien thi banner');
            $table->boolean('is_active')->default(true)->index()->comment('Trang thai kich hoat banner');
            $table->timestamps();
        });

        $this->commentTable('inafo_home_banners', 'Banner trang chu cua storefront Inafo');
    }

    public function down(): void
    {
        Schema::dropIfExists('inafo_home_banners');
    }

    private function commentTable(string $table, string $comment): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` COMMENT = '{$comment}'");
        }
    }
};
