<?php

namespace Modules\Pharma\Services;

use Illuminate\Support\Collection;
use Modules\Pharma\Models\Medicine;
use Rap2hpoutre\FastExcel\FastExcel;

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

    private function calculate(array $data): array
    {
        $importPrice = (float) ($data['import_price'] ?? 0);
        $invoicePrice = (float) ($data['invoice_price'] ?? 0);
        $costPrice = (float) ($data['cost_price'] ?? 0);

        $priceDifference = $invoicePrice - $importPrice;

        $data['price_difference'] = $priceDifference;

        $data['profit_percent'] = $invoicePrice > 0
            ? round(($priceDifference / $invoicePrice) * 100, 2)
            : null;

        $data['cost_invoice_percent'] = $invoicePrice > 0
            ? round(($costPrice / $invoicePrice) * 100, 2)
            : null;

        return $data;
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

    public function deleteMany(array $ids): void
    {
        SupplierTracking::query()
            ->whereIn('id', $ids)
            ->delete();
    }
    public function exportRows(array $filters = []): Collection
    {
        return SupplierTracking::query()
            ->with('medicine')
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
                'Giá vốn' => $item->cost_price,
                'Giá bán' => $item->selling_price,
                'Giá hóa đơn' => $item->invoice_price,
                'Chênh lệch' => $item->price_difference,
                '% chia chênh lệch' => $item->difference_percent,
                '% lợi nhuận' => $item->profit_percent,
                '% giá vốn / hóa đơn' => $item->cost_invoice_percent,
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
    public function importRows(Collection $rows): void
    {
        foreach ($rows as $row) {
            $registrationNumber = trim((string) ($row['Số đăng ký'] ?? ''));

            if ($registrationNumber === '') {
                continue;
            }

            $medicine = Medicine::query()
                ->where('registration_number', $registrationNumber)
                ->first();

            if (! $medicine) {
                continue;
            }

            $this->create([
                'medicine_id' => $medicine->id,
                'working_date' => $row['Ngày làm việc'] ?? null,
                'supplier_name' => $row['Nhà cung cấp'] ?? null,
                'supplier_representative' => $row['Người đại diện'] ?? null,
                'area' => $row['Khu vực'] ?? null,
                'import_price' => $row['Giá nhập'] ?? null,
                'cost_price' => $row['Giá vốn'] ?? null,
                'selling_price' => $row['Giá bán'] ?? null,
                'invoice_price' => $row['Giá hóa đơn'] ?? null,
                'difference_percent' => $row['% chia chênh lệch'] ?? null,
                'committed_quantity' => $row['Số lượng cam kết'] ?? null,
                'unit' => $row['Đơn vị'] ?? $medicine->unit,
                'deposit_amount' => $row['Tiền cọc'] ?? null,
                'start_date' => $row['Ngày bắt đầu'] ?? null,
                'end_date' => $row['Ngày kết thúc'] ?? null,
                'contract_url' => $row['URL hợp đồng'] ?? null,
                'status' => $row['Trạng thái'] ?? 'active',
                'note' => $row['Ghi chú'] ?? null,
            ]);
        }
    }
}
