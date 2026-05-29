<?php

namespace Modules\Pharma\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Modules\Pharma\Models\Medicine;
use Modules\Pharma\Models\SupplierTracking;
use Modules\Shared\Services\ImportExport\BaseImportExportService;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportExport extends BaseImportExportService
{
    protected string $mode = 'update_or_create';

    protected array $requiredHeaders = [];

    protected array $uniqueBy = [
        'medicine_id',
        'supplier_name',
        'working_date',
    ];

    public function rules(): array
    {
        return [
            'medicine_name' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'medicine_id' => ['required', 'integer'],

            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_representative' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],

            'working_date' => ['nullable', 'date'],

            'import_price' => ['nullable', 'numeric'],
            'selling_price' => ['nullable', 'numeric'],
            'invoice_price' => ['nullable', 'numeric'],

            'invoice_difference_percent' => ['nullable', 'numeric'],

            'committed_quantity' => ['nullable', 'numeric'],
            'deposit_amount' => ['nullable', 'numeric'],

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],

            'contract_url' => ['nullable', 'string'],

            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                    'draft',
                    'expired',
                ]),
            ],

            'note' => ['nullable', 'string'],
        ];
    }

    public function modelClass(): string
    {
        return SupplierTracking::class;
    }

    public function columnMapping(): array
    {
        return [
            'A' => 'working_date',
            'B' => 'medicine_name',
            'C' => 'registration_number',
            'D' => 'supplier_name',
            'E' => 'supplier_representative',
            'F' => 'area',
            'G' => 'import_price',
            'H' => 'selling_price',
            'I' => 'invoice_price',
            'J' => 'invoice_difference_amount',
            'K' => 'invoice_difference_percent',
            'L' => 'invoice_difference_fee',
            'M' => 'cost_price',
            'N' => 'gross_profit_percent',
            'O' => 'committed_quantity',
            'P' => 'unit',
            'Q' => 'deposit_amount',
            'R' => 'start_date',
            'S' => 'end_date',
            'T' => 'contract_url',
            'U' => 'status',
            'V' => 'note',
        ];
    }

    public function normalizeRow(array $row): array
    {
        $medicine = $this->findMedicine(
            $row['registration_number'] ?? null,
            $row['medicine_name'] ?? null
        );

        if (! $medicine) {
            throw new \RuntimeException(
                'Không tìm thấy thuốc. Tên thuốc: '
                    . ($row['medicine_name'] ?? 'NULL')
                    . ' | Số đăng ký: '
                    . ($row['registration_number'] ?? 'NULL')
            );
        }

        $importPrice = $this->toDecimal($row['import_price'] ?? 0);
        $sellingPrice = $this->toDecimal($row['selling_price'] ?? 0);
        $invoicePrice = $this->toDecimal($row['invoice_price'] ?? 0);
        $invoiceDifferencePercent = $this->toDecimal($row['invoice_difference_percent'] ?? 0);

        $invoiceDifferenceAmount = $invoicePrice - $importPrice;
        $invoiceDifferenceFee = $invoiceDifferenceAmount * $invoiceDifferencePercent / 100;
        $costPrice = $importPrice + $invoiceDifferenceFee;

        $grossProfitPercent = $sellingPrice > 0
            ? (($sellingPrice - $costPrice) / $sellingPrice) * 100
            : 0;

        return [
            'medicine_id' => $medicine->id,
            'medicine_name' => $this->cleanString($row['medicine_name'] ?? null),
            'registration_number' => $this->cleanString($row['registration_number'] ?? null),

            'working_date' => $this->toDate($row['working_date'] ?? null),

            'supplier_name' => $this->cleanString($row['supplier_name'] ?? null),
            'supplier_representative' => $this->cleanString($row['supplier_representative'] ?? null),
            'area' => $this->cleanString($row['area'] ?? null),

            'import_price' => $importPrice,
            'selling_price' => $sellingPrice,
            'invoice_price' => $invoicePrice,

            'invoice_difference_amount' => round($invoiceDifferenceAmount, 2),
            'invoice_difference_percent' => round($invoiceDifferencePercent, 2),
            'invoice_difference_fee' => round($invoiceDifferenceFee, 2),

            'cost_price' => round($costPrice, 2),
            'gross_profit_percent' => round($grossProfitPercent, 2),

            'committed_quantity' => $this->toNullableDecimal($row['committed_quantity'] ?? null),
            'unit' => $this->cleanString($row['unit'] ?? null),
            'deposit_amount' => $this->toNullableDecimal($row['deposit_amount'] ?? null),

            'start_date' => $this->toDate($row['start_date'] ?? null),
            'end_date' => $this->toDate($row['end_date'] ?? null),

            'contract_url' => $this->cleanString($row['contract_url'] ?? null),
            'status' => $this->normalizeStatus($row['status'] ?? null),
            'note' => $this->cleanString($row['note'] ?? null),
        ];
    }

    public function exportRows(array $filters = []): Collection
    {
        return SupplierTracking::query()
            ->with('medicine')
            ->latest('id')
            ->get();
    }

    public function mapExportRow($row): array
    {
        $data = [
            'working_date' => optional($row->working_date)->format('d/m/Y'),
            'supplier_name' => $row->supplier_name,
            'supplier_representative' => $row->supplier_representative,
            'area' => $row->area,
            'import_price' => $row->import_price,
            'selling_price' => $row->selling_price,
            'invoice_price' => $row->invoice_price,
            'invoice_difference_amount' => $row->invoice_difference_amount,
            'invoice_difference_percent' => $row->invoice_difference_percent,
            'invoice_difference_fee' => $row->invoice_difference_fee,
            'cost_price' => $row->cost_price,
            'gross_profit_percent' => $row->gross_profit_percent,
            'committed_quantity' => $row->committed_quantity,
            'unit' => $row->unit,
            'deposit_amount' => $row->deposit_amount,
            'start_date' => optional($row->start_date)->format('d/m/Y'),
            'end_date' => optional($row->end_date)->format('d/m/Y'),
            'contract_url' => $row->contract_url,
            'status' => $row->status,
            'note' => $row->note,
        ];

        $exceptExport = $row->exceptExport ?? [];

        return collect($data)
            ->except($exceptExport)
            ->all();
    }

    public function templateSampleRow(): array
    {
        return [[
            'Ngày làm việc' => now()->format('d/m/Y'),
            'Tên thuốc' => 'Trosicam 15mg',
            'Số đăng ký' => 'VN-20104-16',
            'Nhà cung cấp' => 'Công ty TNHH Dược Phẩm ABC',
            'Người đại diện' => 'Nguyễn Văn A',
            'Khu vực' => 'Miền Nam',
            'Giá nhập' => 3750,
            'Giá bán' => 7791,
            'Giá hóa đơn' => 7000,
            'Chênh lệch hóa đơn' => 'Hệ thống tự tính',
            '% phí chênh lệch' => 10,
            'Phí chênh lệch' => 'Hệ thống tự tính',
            'Giá vốn' => 'Hệ thống tự tính',
            '% lợi nhuận thực tế' => 'Hệ thống tự tính',
            'Số lượng cam kết' => 500000,
            'Đơn vị' => 'Viên',
            'Tiền cọc' => 50000000,
            'Ngày bắt đầu' => now()->format('d/m/Y'),
            'Ngày kết thúc' => now()->addYear()->format('d/m/Y'),
            'URL hợp đồng' => '',
            'Trạng thái' => 'active',
            'Ghi chú' => '',
        ]];
    }

    protected function findMedicine(?string $registrationNumber, ?string $medicineName): ?Medicine
    {
        $registrationNumber = $this->cleanString($registrationNumber);
        $medicineName = $this->cleanString($medicineName);

        if ($registrationNumber) {
            $medicine = Medicine::query()
                ->whereRaw('LOWER(TRIM(registration_number)) = ?', [mb_strtolower($registrationNumber)])
                ->first();

            if ($medicine) {
                return $medicine;
            }
        }

        if ($medicineName) {
            return Medicine::query()
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($medicineName)])
                ->first();
        }

        return null;
    }

    protected function normalizeStatus(mixed $status): string
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
        if ($value === null || trim((string) $value) === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        $value = str_replace([' ', '₫', 'đ'], '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

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
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            }

            $value = trim((string) $value);

            foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d'] as $format) {
                try {
                    return Carbon::createFromFormat($format, $value)->format('Y-m-d');
                } catch (\Throwable) {
                    //
                }
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            Log::warning('SupplierTracking import date parse failed', [
                'value' => $value,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
