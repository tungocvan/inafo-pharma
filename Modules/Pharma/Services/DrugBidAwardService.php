<?php

namespace Modules\Pharma\Services;

use Modules\Pharma\Models\DrugBidAward;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class DrugBidAwardService
{
    public function getPaginated(
        ?string $search = null,
        ?string $investor = null,
        ?string $company = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        return DrugBidAward::query()
            ->with('medicine')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('medicine_name', 'like', '%' . $search . '%')
                        ->orWhere('bidding_notice_code', 'like', '%' . $search . '%')
                        ->orWhere('decision_number', 'like', '%' . $search . '%');
                });
            })
            ->when($investor, function ($query, $investor) {
                return $query->where('investor_name', $investor);
            })
            ->when($company, function ($query, $company) {
                return $query->where('winning_company_name', $company);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id)
    {
        return DrugBidAward::findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(fn() => DrugBidAward::create($data));
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $award = $this->findOrFail($id);
            $award->update($data);
            return $award;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(fn() => $this->findOrFail($id)->delete());
    }

    public function getUniqueInvestors(): array
    {
        return DrugBidAward::query()->whereNotNull('investor_name')->distinct()->pluck('investor_name')->toArray();
    }

    public function getUniqueCompanies(): array
    {
        return DrugBidAward::query()->whereNotNull('winning_company_name')->distinct()->pluck('winning_company_name')->toArray();
    }

    public function importFromCsv(string $filePath): int
    {
        $rowCount = 0;

        if (($handle = fopen($filePath, 'r')) !== false) {
            fgetcsv($handle, 1000, ';');

            DB::beginTransaction();

            try {
                while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                    if (count($data) < 12) {
                        continue;
                    }

                    if (empty(trim($data[1])) || empty(trim($data[5]))) {
                        continue;
                    }

                    DrugBidAward::updateOrCreate(
                        [
                            'bidding_notice_code'  => trim($data[5]),
                            'medicine_name'        => trim($data[1]),
                            'winning_company_name' => trim($data[10]),
                        ],
                        [
                            'packaging_specification'  => trim($data[2]),
                            'quantity'                 => (int) str_replace(['.', ','], '', $data[3]),
                            'unit_price'               => (float) str_replace(['.', ','], '', $data[4]),
                            'investor_name'            => trim($data[6]),
                            'decision_number'          => trim($data[7]),
                            'decision_date'            => \Carbon\Carbon::createFromFormat('d/m/Y', trim($data[8]))->format('Y-m-d'),
                            'contract_duration_months' => (int) filter_var($data[9], FILTER_SANITIZE_NUMBER_INT),
                            'decision_document_url'    => trim($data[11]) ?: null,
                        ]
                    );

                    $rowCount++;
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                fclose($handle);
                throw $e;
            }

            fclose($handle);
        }

        return $rowCount;
    }

    public function exportToCsv(?string $search = null, ?string $investor = null, ?string $company = null): string
    {
        $filename = 'export_bid_awards_' . time() . '.csv';
        $path = storage_path('app/public/' . $filename);
        $file = fopen($path, 'w');
        fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // Thiết lập UTF-8 BOM để không bị lỗi font khi mở bằng Excel

        // 1. Thêm các tiêu đề cột mới vào file CSV
        fputcsv($file, [
            'STT',
            'Tên thuốc',
            'Hoạt chất',          // Cột mới
            'Hàm lượng',          // Cột mới
            'Đơn vị tính',        // Cột mới
            'Phân nhóm thông tư', // Cột mới
            'Quy cách',
            'Số lượng',
            'Đơn giá',
            'Mã mời thầu',
            'Chủ đầu tư',
            'Số quyết định',
            'Ngày ban hành',
            'Thời hạn (tháng)',
            'Công ty trúng thầu'
        ]);

        $stt = 1;

        // 2. Sử dụng with('medicine') để tránh lỗi N+1 query khi lấy thông tin thuốc gốc
        DrugBidAward::query()
            ->with('medicine')
            ->when($search, function ($query, $search) {
                return $query->where('medicine_name', 'like', "%{$search}%");
            })
            ->when($investor, function ($query, $investor) {
                return $query->where('investor_name', $investor);
            })
            ->when($company, function ($query, $company) {
                return $query->where('winning_company_name', $company);
            })
            ->latest()
            ->chunk(100, function ($awards) use ($file, &$stt) {
                foreach ($awards as $award) {
                    // Lấy thông tin an toàn từ bảng liên kết (nếu có liên kết gốc)
                    $activeIngredients = $award->medicine ? $award->medicine->active_ingredients : '';
                    $concentration = $award->medicine ? $award->medicine->concentration : '';
                    $unit = $award->medicine ? $award->medicine->unit : '';
                    $circularGroup = $award->medicine ? $award->medicine->circular_group : '';

                    // 3. Đổ dữ liệu tương ứng theo thứ tự tiêu đề
                    fputcsv($file, [
                        $stt++,
                        $award->medicine_name,
                        $activeIngredients,
                        $concentration,
                        $unit,
                        $circularGroup,
                        $award->packaging_specification,
                        $award->quantity,
                        $award->unit_price,
                        $award->bidding_notice_code,
                        $award->investor_name,
                        $award->decision_number,
                        $award->decision_date ? $award->decision_date->format('d/m/Y') : '',
                        $award->contract_duration_months,
                        $award->winning_company_name
                    ]);
                }
            });

        fclose($file);
        return $path;
    }
}
