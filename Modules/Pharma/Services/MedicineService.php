<?php

namespace Modules\Pharma\Services;

use Modules\Pharma\Models\Medicine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class MedicineService
{
    public function getPaginatedMedicines(
        ?string $search = null,
        int $perPage = 10,
        int $page = 1,
        ?string $circularGroup = null,
        ?string $specialControl = null
    ): LengthAwarePaginator {
        return Medicine::query()
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('active_ingredients', 'like', '%' . $search . '%');
                });
            })
            ->when($circularGroup, function ($query, $circularGroup) {
                return $query->where('circular_group', $circularGroup);
            })
            ->when($specialControl, function ($query, $specialControl) {
                $isSpecial = $specialControl === 'yes';
                return $query->where('is_special_control', $isSpecial);
            })
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getUniqueCircularGroups(): array
    {
        return Medicine::query()
            ->whereNotNull('circular_group')
            ->where('circular_group', '!=', '')
            ->distinct()
            ->pluck('circular_group')
            ->toArray();
    }

    public function findOrFail(int $id): Medicine
    {
        return Medicine::findOrFail($id);
    }

    public function store(array $data): Medicine
    {
        return DB::transaction(function () use ($data) {
            return Medicine::create($data);
        });
    }

    public function update(int $id, array $data): Medicine
    {
        return DB::transaction(function () use ($id, $data) {
            $medicine = $this->findOrFail($id);
            $medicine->update($data);
            return $medicine;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $medicine = $this->findOrFail($id);
            return $medicine->delete();
        });
    }

    public function importFromCsv(string $filePath): int
    {
        $rowCount = 0;

        if (($handle = fopen($filePath, 'r')) !== false) {
            fgetcsv($handle, 1000, ','); // Skip header

            DB::beginTransaction();
            try {
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (empty($data[5])) {
                        continue;
                    }

                    Medicine::updateOrCreate(
                        ['registration_number' => trim($data[10])],
                        [
                            'circular_order_number'   => trim($data[1]) ?: null,
                            'circular_group'          => trim($data[2]) ?: null,
                            'active_ingredients'      => trim($data[3]),
                            'concentration'           => trim($data[4]),
                            'name'                    => trim($data[5]),
                            'dosage_form'             => trim($data[6]),
                            'route_of_administration' => trim($data[7]),
                            'unit'                    => trim($data[8]),
                            'packaging_specification' => trim($data[9]),
                            'shelf_life'              => trim($data[11]),
                            'registered_company'      => trim($data[12]),
                            'manufacturing_company'   => trim($data[13]),
                            'manufacturing_country'   => trim($data[14]),
                            'visa_validity_date'      => !empty($data[15]) ? date('Y-m-d', strtotime(trim($data[15]))) : null,
                            'gmp_certification_date'  => !empty($data[16]) ? date('Y-m-d', strtotime(trim($data[16]))) : null,
                            'declared_price'          => !empty($data[17]) ? (float)str_replace([',', '.'], '', trim($data[17])) : null,
                            'profile_link'            => trim($data[18]) ?: null,
                            'is_special_control'      => filter_var(trim($data[19]), FILTER_VALIDATE_BOOLEAN),
                            'notes'                   => trim($data[20]) ?: null,
                        ]
                    );
                    $rowCount++;
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                fclose($handle);
                throw $e;
            }
            fclose($handle);
        }
        return $rowCount;
    }

    public function exportToCsv(?string $search = null, ?string $circularGroup = null, ?string $specialControl = null): string
    {
        $query = Medicine::query()
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('active_ingredients', 'like', '%' . $search . '%');
                });
            })
            ->when($circularGroup, function ($query, $circularGroup) {
                return $query->where('circular_group', $circularGroup);
            })
            ->when($specialControl, function ($query, $specialControl) {
                $isSpecial = $specialControl === 'yes';
                return $query->where('is_special_control', $isSpecial);
            })
            ->latest();

        $filename = 'export_medicines_' . time() . '.csv';
        $path = storage_path('app/public/' . $filename);

        $file = fopen($path, 'w');
        fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

        fputcsv($file, [
            'STT', 'Tên thuốc', 'Số đăng ký', 'Hoạt chất', 'Hàm lượng',
            'Dạng bào chế', 'Đường dùng', 'Đơn vị tính', 'Quy cách', 'Phân nhóm', 'Nước sản xuất'
        ]);

        $stt = 1;
        $query->chunk(100, function ($medicines) use ($file, &$stt) {
            foreach ($medicines as $medicine) {
                fputcsv($file, [
                    $stt++,
                    $medicine->name,
                    $medicine->registration_number,
                    $medicine->active_ingredients,
                    $medicine->concentration,
                    $medicine->dosage_form,
                    $medicine->route_of_administration,
                    $medicine->unit,
                    $medicine->packaging_specification,
                    $medicine->circular_group,
                    $medicine->manufacturing_country
                ]);
            }
        });

        fclose($file);
        return $path;
    }
}
