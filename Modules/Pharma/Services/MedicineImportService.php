<?php

namespace Modules\Pharma\Services;

use Modules\Pharma\Models\Medicine;
use Illuminate\Support\Facades\DB;
use Exception;

class MedicineImportService
{
    /**
     * Xử lý import dữ liệu từ file CSV
     * * @param string $filePath Đường dẫn tới file CSV
     * @return int Số lượng bản ghi đã import thành công
     * @throws Exception
     */
    public function importFromCsv(string $filePath): int
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("File không tồn tại hoặc hệ thống không có quyền đọc.");
        }

        $recordsImported = 0;
        $header = null;

        // Mở file đọc từng dòng (Chống quá tải RAM khi file quá lớn - Performance First)
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception("Không thể mở file CSV.");
        }

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 2000, ',')) !== false) {
                // Bỏ qua dòng tiêu đề
                if (!$header) {
                    $header = $row;
                    continue;
                }

                // Dòng rỗng hoặc thiếu 2 key quan trọng nhất thì bỏ qua
                if (!isset($row[10]) || !isset($row[9]) || trim($row[10]) === '' || trim($row[9]) === '') {
                    continue;
                }

                $registrationNumber = trim($row[10]);
                $packagingSpec = trim($row[9]);

                // Xử lý logic Derived Fields / Mapping
                $isSpecialControl = (isset($row[19]) && strtolower(trim($row[19])) === 'true') ? true : false;
                $price = !empty($row[17]) ? (float) str_replace([',', '.'], '', $row[17]) : null;
                $visaDate = !empty($row[15]) ? trim($row[15]) : null;
                $gmpDate = !empty($row[16]) ? trim($row[16]) : null;

                // Chuẩn bị payload data
                $medicineData = [
                    'circular_order_number'   => trim($row[1] ?? ''),
                    'circular_group'          => trim($row[2] ?? ''),
                    'active_ingredients'      => trim($row[3] ?? ''),
                    'concentration'           => trim($row[4] ?? ''),
                    'name'                    => trim($row[5] ?? ''),
                    'dosage_form'             => trim($row[6] ?? ''),
                    'route_of_administration' => trim($row[7] ?? ''),
                    'unit'                    => trim($row[8] ?? ''),
                    'shelf_life'              => trim($row[11] ?? ''),
                    'registered_company'      => trim($row[12] ?? ''),
                    'manufacturing_company'   => trim($row[13] ?? ''),
                    'manufacturing_country'   => trim($row[14] ?? ''),
                    'visa_validity_date'      => $visaDate,
                    'gmp_certification_date'  => $gmpDate,
                    'declared_price'          => $price,
                    'profile_link'            => trim($row[18] ?? ''),
                    'is_special_control'      => $isSpecialControl,
                    'notes'                   => trim($row[20] ?? ''),
                ];

                // Insert mới hoặc Update nếu trùng khóa kép (Giấy phép & Quy cách)
                Medicine::updateOrCreate(
                    [
                        'registration_number'     => $registrationNumber,
                        'packaging_specification' => $packagingSpec,
                    ],
                    $medicineData
                );

                $recordsImported++;
            }

            fclose($handle);
            DB::commit();

            return $recordsImported;

        } catch (Exception $e) {
            DB::rollBack();
            if (is_resource($handle)) {
                fclose($handle);
            }
            throw $e;
        }
    }
}
