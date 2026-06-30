<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inafo_home_shelf_products', function (Blueprint $table) {
            $table->id()->comment('Khoa chinh');
            $table->foreignId('shelf_id')->constrained('inafo_home_shelves')->cascadeOnDelete()->comment('Ke san pham so huu ban ghi');
            $table->unsignedBigInteger('product_id')->index()->comment('ID san pham tu Product module');
            $table->unsignedInteger('position')->default(0)->index()->comment('Thu tu sap xep san pham trong ke');
            $table->timestamps();

            $table->unique(['shelf_id', 'product_id'], 'inafo_shelf_product_unique');
        });

        $this->commentTable('inafo_home_shelf_products', 'Danh sach san pham thu cong trong ke trang chu Inafo');
    }

    public function down(): void
    {
        Schema::dropIfExists('inafo_home_shelf_products');
    }

    private function commentTable(string $table, string $comment): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` COMMENT = '{$comment}'");
        }
    }
};
