<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inafo_partner_logos', function (Blueprint $table) {
            $table->id()->comment('Khoa chinh');
            $table->string('name')->comment('Ten doi tac hien thi trong alt/title');
            $table->string('logo_url')->nullable()->comment('URL logo doi tac');
            $table->string('target_url')->nullable()->comment('Duong dan khi bam vao logo doi tac');
            $table->unsignedInteger('position')->default(0)->index()->comment('Thu tu sap xep tang dan');
            $table->boolean('is_active')->default(true)->index()->comment('Trang thai kich hoat doi tac');
            $table->timestamps();
        });

        $this->commentTable('inafo_partner_logos', 'Logo doi tac hien thi tren trang chu Inafo');
    }

    public function down(): void
    {
        Schema::dropIfExists('inafo_partner_logos');
    }

    private function commentTable(string $table, string $comment): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` COMMENT = '{$comment}'");
        }
    }
};
