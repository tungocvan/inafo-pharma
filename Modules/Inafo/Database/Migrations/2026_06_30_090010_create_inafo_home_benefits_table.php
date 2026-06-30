<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inafo_home_benefits', function (Blueprint $table) {
            $table->id()->comment('Khoa chinh');
            $table->string('title')->comment('Tieu de loi ich hien thi tren home');
            $table->string('description')->nullable()->comment('Mo ta ngan cua loi ich');
            $table->string('icon', 80)->nullable()->comment('Ten icon hoac ma icon UI');
            $table->unsignedInteger('position')->default(0)->index()->comment('Thu tu sap xep tang dan');
            $table->boolean('is_active')->default(true)->index()->comment('Trang thai kich hoat loi ich');
            $table->timestamps();
        });

        $this->commentTable('inafo_home_benefits', 'Cac loi ich hien thi tren trang chu Inafo');
    }

    public function down(): void
    {
        Schema::dropIfExists('inafo_home_benefits');
    }

    private function commentTable(string $table, string $comment): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` COMMENT = '{$comment}'");
        }
    }
};
