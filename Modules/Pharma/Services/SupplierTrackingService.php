<?php

namespace Modules\Pharma\Services;

use Illuminate\Support\Collection;
use Modules\Pharma\Models\Medicine;
use Modules\Pharma\Models\SupplierTracking;

class SupplierTrackingService
{
    public function paginate(array $filters = [], int $perPage = 15)
    {
        return SupplierTracking::query()
            ->with('medicine')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('supplier_name', 'like', "%{$search}%")
                        ->orWhere('supplier_representative', 'like', "%{$search}%")
                        ->orWhere('area', 'like', "%{$search}%")
                        ->orWhereHas('medicine', function ($medicineQuery) use ($search) {
                            $medicineQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('registration_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function medicinesForSelect(): Collection
    {
        return Medicine::query()
            ->select('id', 'name', 'registration_number', 'packaging_specification', 'unit')
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): SupplierTracking
    {
        return SupplierTracking::with('medicine')->findOrFail($id);
    }

    public function create(array $data): SupplierTracking
    {
        return SupplierTracking::create($this->calculate($data));
    }

    public function update(int $id, array $data): SupplierTracking
    {
        $tracking = $this->find($id);

        $tracking->update($this->calculate($data));

        return $tracking;
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function deleteMany(array $ids): void
    {
        SupplierTracking::query()
            ->whereIn('id', $ids)
            ->delete();
    }

    public function getFilteredIds(array $filters = []): Collection
    {
        return SupplierTracking::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('supplier_name', 'like', "%{$search}%")
                        ->orWhere('supplier_representative', 'like', "%{$search}%")
                        ->orWhereHas('medicine', function ($medicineQuery) use ($search) {
                            $medicineQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('registration_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->pluck('id');
    }

    public function previewCalculate(array $data): array
    {
        return $this->calculate($data);
    }

    private function calculate(array $data): array
    {
        $importPrice = $this->toFloat($data['import_price'] ?? 0);
        $sellingPrice = $this->toFloat($data['selling_price'] ?? 0);
        $invoicePrice = $this->toFloat($data['invoice_price'] ?? 0);
        $differencePercent = $this->toFloat($data['invoice_difference_percent'] ?? 0);

        // Công thức: Chênh lệch hóa đơn = Giá hóa đơn - Giá nhập
        $differenceAmount = $invoicePrice - $importPrice;

        // Công thức: Phí chênh lệch = Chênh lệch hóa đơn * % phí chênh lệch
        $differenceFee = $differenceAmount * ($differencePercent / 100);

        // Công thức: Giá vốn = Giá nhập + Phí chênh lệch
        $costPrice = $importPrice + $differenceFee;

        // Công thức: % lợi nhuận thực tế = (Giá bán - Giá vốn) / Giá bán
        $grossProfitPercent = $sellingPrice > 0
            ? (($sellingPrice - $costPrice) / $sellingPrice) * 100
            : 0;

        $data['invoice_difference_amount'] = round($differenceAmount, 2);
        $data['invoice_difference_fee'] = round($differenceFee, 2);
        $data['cost_price'] = round($costPrice, 2);
        $data['gross_profit_percent'] = round($grossProfitPercent, 2);

        return $data;
    }

    private function toFloat(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (float) str_replace(',', '', (string) $value);
    }
    public function exportRows(array $filters = []): Collection
    {
        return SupplierTracking::query()
            ->with('medicine')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('supplier_name', 'like', "%{$search}%")
                        ->orWhere('supplier_representative', 'like', "%{$search}%")
                        ->orWhere('area', 'like', "%{$search}%")
                        ->orWhereHas('medicine', function ($medicineQuery) use ($search) {
                            $medicineQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('registration_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->latest()
            ->get()
            ->map(fn($item) => [
                'Ngày làm việc' => optional($item->working_date)->format('Y-m-d'),
                'Tên thuốc' => $item->medicine?->name,
                'Số đăng ký' => $item->medicine?->registration_number,
                'Nhà cung cấp' => $item->supplier_name,
                'Người đại diện' => $item->supplier_representative,
                'Khu vực' => $item->area,

                'Giá nhập' => $item->import_price,
                'Giá bán' => $item->selling_price,
                'Giá hóa đơn' => $item->invoice_price,

                'Chênh lệch hóa đơn' => $item->invoice_difference_amount,
                '% phí chênh lệch' => $item->invoice_difference_percent,
                'Phí chênh lệch' => $item->invoice_difference_fee,
                'Giá vốn' => $item->cost_price,
                '% lợi nhuận thực tế' => $item->gross_profit_percent,

                'Số lượng cam kết' => $item->committed_quantity,
                'Đơn vị' => $item->unit,
                'Tiền cọc' => $item->deposit_amount,

                'Ngày bắt đầu' => optional($item->start_date)->format('Y-m-d'),
                'Ngày kết thúc' => optional($item->end_date)->format('Y-m-d'),
                'URL hợp đồng' => $item->contract_url,
                'Trạng thái' => $item->status,
                'Ghi chú' => $item->note,
            ]);
    }
    public function importRows(Collection $rows): array
    {
        $success = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $registrationNumber = trim((string) ($row['Số đăng ký'] ?? ''));

                if ($registrationNumber === '') {
                    $skipped++;
                    $errors[] = "Dòng {$rowNumber}: thiếu Số đăng ký.";
                    continue;
                }

                $medicine = Medicine::query()
                    ->where('registration_number', $registrationNumber)
                    ->first();

                if (! $medicine) {
                    $skipped++;
                    $errors[] = "Dòng {$rowNumber}: không tìm thấy thuốc có SĐK {$registrationNumber}.";
                    continue;
                }

                $this->create([
                    'medicine_id' => $medicine->id,
                    'working_date' => $this->parseDate($row['Ngày làm việc'] ?? null),

                    'supplier_name' => $row['Nhà cung cấp'] ?? null,
                    'supplier_representative' => $row['Người đại diện'] ?? null,
                    'area' => $row['Khu vực'] ?? null,

                    'import_price' => $this->parseNumber($row['Giá nhập'] ?? 0),
                    'selling_price' => $this->parseNumber($row['Giá bán'] ?? 0),
                    'invoice_price' => $this->parseNumber($row['Giá hóa đơn'] ?? 0),

                    // Không import tay field công thức
                    // invoice_difference_amount = invoice_price - import_price
                    // invoice_difference_fee = invoice_difference_amount * percent
                    // cost_price = import_price + fee
                    // gross_profit_percent = (selling_price - cost_price) / selling_price

                    'invoice_difference_percent' => $this->parseNumber($row['% phí chênh lệch'] ?? 0),

                    'committed_quantity' => $this->parseNumber($row['Số lượng cam kết'] ?? 0),
                    'unit' => $row['Đơn vị'] ?? $medicine->unit,
                    'deposit_amount' => $this->parseNumber($row['Tiền cọc'] ?? 0),

                    'start_date' => $this->parseDate($row['Ngày bắt đầu'] ?? null),
                    'end_date' => $this->parseDate($row['Ngày kết thúc'] ?? null),

                    'contract_url' => $row['URL hợp đồng'] ?? null,
                    'status' => $row['Trạng thái'] ?? 'active',
                    'note' => $row['Ghi chú'] ?? null,
                ]);

                $success++;
            } catch (\Throwable $e) {
                report($e);

                $skipped++;
                $errors[] = "Dòng {$rowNumber}: lỗi xử lý dữ liệu.";
            }
        }

        return [
            'success' => $success,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }
    private function parseNumber(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $value = str_replace(['.', ',', 'đ', ' '], ['', '.', '', ''], (string) $value);

        return is_numeric($value) ? (float) $value : 0;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
