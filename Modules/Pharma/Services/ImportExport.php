<?php

namespace Modules\Pharma\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Modules\Pharma\Models\Medicine;
use Modules\Pharma\Models\SupplierTracking;
use Modules\Shared\Services\ImportExport\BaseImportExportService;

class ImportExport extends BaseImportExportService
{
    protected array $uniqueBy = [
        'medicine_id',
        'supplier_name',
        'working_date',
    ];

    protected array $requiredHeaders = [
        'medicine_name',
        'supplier_name',
    ];

    public function headerAliases(): array
    {
        return [
            'ngày làm việc' => 'working_date',
            'ngay lam viec' => 'working_date',

            'tên thuốc' => 'medicine_name',
            'ten thuoc' => 'medicine_name',
            'ten_thuoc' => 'medicine_name',

            'số đăng ký' => 'registration_number',
            'so dang ky' => 'registration_number',
            'so_dang_ky' => 'registration_number',
            'sdk' => 'registration_number',

            'nhà cung cấp' => 'supplier_name',
            'nha cung cap' => 'supplier_name',

            'người đại diện' => 'supplier_representative',
            'nguoi dai dien' => 'supplier_representative',

            'khu vực' => 'area',
            'khu vuc' => 'area',

            'giá nhập' => 'import_price',
            'gia nhap' => 'import_price',

            'giá bán' => 'selling_price',
            'gia ban' => 'selling_price',

            'giá hóa đơn' => 'invoice_price',
            'gia hoa don' => 'invoice_price',

            '% phí chênh lệch' => 'invoice_difference_percent',
            '% phi chenh lech' => 'invoice_difference_percent',

            'số lượng cam kết' => 'committed_quantity',
            'so luong cam ket' => 'committed_quantity',

            'đơn vị' => 'unit',
            'don vi' => 'unit',

            'tiền cọc' => 'deposit_amount',
            'tien coc' => 'deposit_amount',

            'ngày bắt đầu' => 'start_date',
            'ngay bat dau' => 'start_date',

            'ngày kết thúc' => 'end_date',
            'ngay ket thuc' => 'end_date',

            'url hợp đồng' => 'contract_url',
            'url hop dong' => 'contract_url',

            'trạng thái' => 'status',
            'trang thai' => 'status',

            'ghi chú' => 'note',
            'ghi chu' => 'note',
        ];
    }


    public function modelClass(): string
    {
        return SupplierTracking::class;
    }

    public function rules(): array
    {
        return [
            'medicine_name' => ['required_without:registration_number', 'nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],

            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_representative' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],

            'working_date' => ['nullable', 'date'],
            'import_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'invoice_price' => ['nullable', 'numeric', 'min:0'],
            'invoice_difference_percent' => ['nullable', 'numeric'],

            'committed_quantity' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:255'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],

            'contract_url' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'draft', 'expired'])],
            'note' => ['nullable', 'string'],
        ];
    }

    public function normalizeRow(array $row): array
    {
        $data = $this->normalizeHeaders($row);

        $medicine = $this->findMedicine(
            registrationNumber: $data['registration_number'] ?? null,
            medicineName: $data['medicine_name'] ?? null,
        );

        if (! $medicine) {
            throw new \RuntimeException(
                'Không tìm thấy thuốc. Tên thuốc: '
                    . ($data['medicine_name'] ?? 'NULL')
                    . ' | Số đăng ký: '
                    . ($data['registration_number'] ?? 'NULL')
            );
        }

        $importPrice = $this->toDecimal($data['import_price'] ?? 0);
        $sellingPrice = $this->toDecimal($data['selling_price'] ?? 0);
        $invoicePrice = $this->toDecimal($data['invoice_price'] ?? 0);
        $invoiceDifferencePercent = $this->toDecimal($data['invoice_difference_percent'] ?? 0);

        $invoiceDifferenceAmount = $invoicePrice - $importPrice;
        $invoiceDifferenceFee = $invoiceDifferenceAmount * $invoiceDifferencePercent / 100;
        $costPrice = $importPrice + $invoiceDifferenceFee;

        $grossProfitPercent = 0;
        if ($sellingPrice > 0) {
            $grossProfitPercent = (($sellingPrice - $costPrice) / $sellingPrice) * 100;
        }

        return [
            'medicine_id' => $medicine->id,

            'working_date' => $this->toDate($data['working_date'] ?? null),

            'supplier_name' => $this->cleanString($data['supplier_name'] ?? null),
            'supplier_representative' => $this->cleanString($data['supplier_representative'] ?? null),
            'area' => $this->cleanString($data['area'] ?? null),

            'import_price' => $importPrice,
            'selling_price' => $sellingPrice,
            'invoice_price' => $invoicePrice,

            'invoice_difference_amount' => round($invoiceDifferenceAmount, 2),
            'invoice_difference_percent' => round($invoiceDifferencePercent, 2),
            'invoice_difference_fee' => round($invoiceDifferenceFee, 2),

            'cost_price' => round($costPrice, 2),
            'gross_profit_percent' => round($grossProfitPercent, 2),

            'committed_quantity' => $this->toNullableDecimal($data['committed_quantity'] ?? null),
            'unit' => $this->cleanString($data['unit'] ?? null),

            'deposit_amount' => $this->toNullableDecimal($data['deposit_amount'] ?? null),

            'start_date' => $this->toDate($data['start_date'] ?? null),
            'end_date' => $this->toDate($data['end_date'] ?? null),

            'contract_url' => $this->cleanString($data['contract_url'] ?? null),
            'status' => $this->normalizeStatus($data['status'] ?? null),
            'note' => $this->cleanString($data['note'] ?? null),
        ];
    }

    public function exportRows(array $filters = []): Collection
    {
        return SupplierTracking::query()
            ->with('medicine')
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->get()
            ->map(fn(SupplierTracking $row) => $this->mapExportRow($row));
    }

    public function mapExportRow($row): array
    {
        return [
            'Ngày làm việc' => optional($row->working_date)->format('d/m/Y'),
            'Tên thuốc' => $row->medicine?->name,
            'Số đăng ký' => $row->medicine?->registration_number,

            'Nhà cung cấp' => $row->supplier_name,
            'Người đại diện' => $row->supplier_representative,
            'Khu vực' => $row->area,

            'Giá nhập' => $row->import_price,
            'Giá bán' => $row->selling_price,
            'Giá hóa đơn' => $row->invoice_price,

            'Chênh lệch hóa đơn' => $row->invoice_difference_amount,
            '% phí chênh lệch' => $row->invoice_difference_percent,
            'Phí chênh lệch' => $row->invoice_difference_fee,

            'Giá vốn' => $row->cost_price,
            '% lợi nhuận thực tế' => $row->gross_profit_percent,

            'Số lượng cam kết' => $row->committed_quantity,
            'Đơn vị' => $row->unit,
            'Tiền cọc' => $row->deposit_amount,

            'Ngày bắt đầu' => optional($row->start_date)->format('d/m/Y'),
            'Ngày kết thúc' => optional($row->end_date)->format('d/m/Y'),

            'URL hợp đồng' => $row->contract_url,
            'Trạng thái' => $row->status,
            'Ghi chú' => $row->note,
        ];
    }

    public function templateSampleRow(): array
    {
        return [[
            'Ngày làm việc' => now()->format('d/m/Y'),
            'Tên thuốc' => 'Tên thuốc mẫu',
            'Số đăng ký' => 'VD-00000-00',

            'Nhà cung cấp' => 'Công ty TNHH Dược phẩm ABC',
            'Người đại diện' => 'Nguyễn Văn A',
            'Khu vực' => 'Miền Nam',

            'Giá nhập' => 100000,
            'Giá bán' => 120000,
            'Giá hóa đơn' => 105000,

            'Chênh lệch hóa đơn' => 'Hệ thống tự tính',
            '% phí chênh lệch' => 10,
            'Phí chênh lệch' => 'Hệ thống tự tính',

            'Giá vốn' => 'Hệ thống tự tính',
            '% lợi nhuận thực tế' => 'Hệ thống tự tính',

            'Số lượng cam kết' => 100,
            'Đơn vị' => 'Hộp',
            'Tiền cọc' => 5000000,

            'Ngày bắt đầu' => now()->format('d/m/Y'),
            'Ngày kết thúc' => now()->addMonth()->format('d/m/Y'),

            'URL hợp đồng' => 'https://example.com/hop-dong.pdf',
            'Trạng thái' => 'active',
            'Ghi chú' => 'Dòng mẫu',
        ]];
    }

    protected function findMedicine(?string $registrationNumber, ?string $medicineName): ?Medicine
    {
        $registrationNumber = $this->cleanString($registrationNumber);
        $medicineName = $this->cleanString($medicineName);

        if ($registrationNumber) {
            $medicine = Medicine::query()
                ->whereRaw('LOWER(TRIM(registration_number)) = ?', [
                    mb_strtolower($registrationNumber),
                ])
                ->first();

            if ($medicine) {
                return $medicine;
            }
        }

        if ($medicineName) {
            return Medicine::query()
                ->whereRaw('LOWER(TRIM(name)) = ?', [
                    mb_strtolower($medicineName),
                ])
                ->first();
        }

        return null;
    }

    protected function normalizeHeaders(array $row): array
    {
        $aliases = $this->headerAliases();
        $normalized = [];

        foreach ($row as $header => $value) {
            $rawKey = trim((string) $header);

            $lowerKey = mb_strtolower($rawKey);

            $snakeKey = str($rawKey)
                ->ascii()
                ->lower()
                ->snake()
                ->toString();

            $mappedKey = $aliases[$lowerKey]
                ?? $aliases[$snakeKey]
                ?? $snakeKey;

            $normalized[$mappedKey] = $value;
        }

        return $normalized;
    }

    protected function normalizeStatus(?string $status): string
    {
        $status = mb_strtolower(trim((string) $status));

        return match ($status) {
            'inactive', 'ngưng', 'ngung', 'không hoạt động', 'khong hoat dong' => 'inactive',
            'draft', 'nháp', 'nhap' => 'draft',
            'expired', 'hết hạn', 'het han' => 'expired',
            default => 'active',
        };
    }

    protected function cleanString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function toDecimal(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $value = str_replace(['.', ','], ['', '.'], (string) $value);

        return is_numeric($value) ? (float) $value : 0;
    }

    protected function toNullableDecimal(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return $this->toDecimal($value);
    }

    protected function toDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse(str_replace('/', '-', (string) $value))->format('Y-m-d');
        } catch (\Throwable $e) {
            Log::warning('SupplierTracking import date parse failed', [
                'value' => $value,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
