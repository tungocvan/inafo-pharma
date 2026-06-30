<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inafo_footer_columns', function (Blueprint $table) {
            $table->id()->comment('Khoa chinh');
            $table->string('title')->comment('Tieu de cot footer');
            $table->unsignedInteger('position')->default(0)->index()->comment('Thu tu sap xep cot footer');
            $table->boolean('is_active')->default(true)->index()->comment('Trang thai kich hoat cot footer');
            $table->timestamps();
        });

        $this->commentTable('inafo_footer_columns', 'Cac cot link footer cua storefront Inafo');
    }

    public function down(): void
    {
        Schema::dropIfExists('inafo_footer_columns');
    }

    private function commentTable(string $table, string $comment): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` COMMENT = '{$comment}'");
        }
    }
};
