<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inafo_footer_links', function (Blueprint $table) {
            $table->id()->comment('Khoa chinh');
            $table->foreignId('column_id')->constrained('inafo_footer_columns')->cascadeOnDelete()->comment('Cot footer so huu link');
            $table->string('label')->comment('Nhan hien thi cua link footer');
            $table->string('url')->comment('Duong dan cua link footer');
            $table->unsignedInteger('position')->default(0)->index()->comment('Thu tu sap xep link trong cot');
            $table->boolean('is_active')->default(true)->index()->comment('Trang thai kich hoat link footer');
            $table->timestamps();
        });

        $this->commentTable('inafo_footer_links', 'Link footer cua storefront Inafo');
    }

    public function down(): void
    {
        Schema::dropIfExists('inafo_footer_links');
    }

    private function commentTable(string $table, string $comment): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` COMMENT = '{$comment}'");
        }
    }
};
