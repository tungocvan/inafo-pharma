<?php

namespace Modules\Pharma\Console\Commands;

use Illuminate\Console\Command;
use Modules\Pharma\Services\MedicineImportService;
use Exception;

class ImportMedicineCommand extends Command
{
    /**
     * The name and signature of the console command.
     * Cú pháp chạy: php artisan medicine:import "path/to/file.csv"
     */
    protected $signature = 'medicine:import {file : Đường dẫn tới file CSV hồ sơ sản phẩm}';

    protected $description = 'Import dữ liệu danh mục thuốc từ file CSV vào Database';

    protected MedicineImportService $medicineImportService;

    // Dependency Injection BẮT BUỘC
    public function __construct(MedicineImportService $medicineImportService)
    {
        parent::__construct();
        $this->medicineImportService = $medicineImportService;
    }

    public function handle()
    {
        $filePath = $this->argument('file');

        $this->info("Đang kiểm tra file: {$filePath}");

        try {
            // Uỷ quyền toàn bộ logic cho Service
            $count = $this->medicineImportService->importFromCsv($filePath);

            $this->info("✅ Hoàn tất! Đã import/cập nhật thành công {$count} bản ghi thuốc.");
            return 0;

        } catch (Exception $e) {
            $this->error("❌ Lỗi trong quá trình Import: " . $e->getMessage());
            return 1;
        }
    }
}
