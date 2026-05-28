<?php

namespace Modules\Shared\Services\ImportExport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;
use Modules\Shared\Services\ImportExport\Concerns\HandlesExportStorage;
use Modules\Shared\Services\ImportExport\Concerns\HandlesHeaderMapping;
use Modules\Shared\Services\ImportExport\Concerns\HandlesImportReport;
use Modules\Shared\Services\ImportExport\Concerns\NormalizesImportRows;

abstract class BaseImportExportService
{
    use HandlesImportReport;
    use HandlesHeaderMapping;
    use NormalizesImportRows;
    use HandlesExportStorage;

    protected string $defaultSheetName = 'data';

    protected array $requiredHeaders = [];

    protected array $rules = [];

    protected array $uniqueBy = [];

    protected string $mode = 'update_or_create';

    abstract protected function modelClass(): string;

    protected function exportRows(array $filters = []): Collection
    {
        return $this->modelClass()::query()->latest()->get();
    }

    protected function normalizeRow(array $row): array
    {
        return $row;
    }

    protected function beforePersist(array $data, array $row, int $rowNumber, string $sheet): array
    {
        return $data;
    }

    public function import(string $filePath, array $options = []): array
    {
        $this->resetReport();

        $mode = $options['mode'] ?? $this->mode;
        $dryRun = (bool) ($options['dry_run'] ?? false);

        $this->addDebug('mode', $mode);
        $this->addDebug('dry_run', $dryRun);
        $this->addDebug('file', $filePath);

        try {
            $this->validateImportFile($filePath);

            $rows = (new FastExcel())->import($filePath);

            $this->addDebug('sheets', [$this->defaultSheetName]);
            $this->addDebug('sheet_counts', [
                $this->defaultSheetName => $rows->count(),
            ]);

            if (! $dryRun) {
                DB::beginTransaction();
            }

            foreach ($rows as $index => $rawRow) {
                $rowNumber = $index + 2;
                $this->totalRows++;

                $row = $this->normalizeRowHeaders((array) $rawRow);

                if (! $this->hasRequiredHeaders($row)) {
                    \Log::debug('Import headers check', [
                        'headers' => array_keys($row),
                        'requiredHeaders' => $this->requiredHeaders ?? null,
                        'row' => $row,
                    ]);

                    $this->addError(
                        $this->defaultSheetName,
                        $rowNumber,
                        null,
                        'File thiếu cột bắt buộc.'
                    );

                    continue;
                }

                $row = $this->normalizeRow($row);

                $validator = Validator::make($row, $this->rules);

                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $column => $messages) {
                        foreach ($messages as $message) {
                            $this->addError(
                                $this->defaultSheetName,
                                $rowNumber,
                                $column,
                                $message,
                                $row[$column] ?? null
                            );
                        }
                    }

                    continue;
                }

                if ($dryRun) {
                    $this->successRows++;
                    continue;
                }

                $data = $this->beforePersist(
                    $row,
                    $row,
                    $rowNumber,
                    $this->defaultSheetName
                );

                $this->persistRow($data, $mode);

                $this->successRows++;
            }

            if (! $dryRun) {
                DB::commit();
            }

            return $this->report(empty($this->errors));
        } catch (\Throwable $exception) {
            if (! $dryRun && DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error('Import failed', [
                'service' => static::class,
                'file' => $filePath,
                'message' => $exception->getMessage(),
            ]);

            $this->addError(
                $this->defaultSheetName,
                null,
                null,
                'Lỗi hệ thống khi import. Vui lòng kiểm tra log.'
            );

            $this->addDebug('exception', $exception->getMessage());

            return $this->report(false);
        }
    }

    public function export(array $filters = []): string
    {
        $path = $this->makeExportPath(class_basename($this->modelClass()));

        $rows = $this->exportRows($filters)
            ->map(fn($item) => $this->mapExportRow($item));

        (new FastExcel($rows))->export(storage_path('app/public/' . $path));

        return $path;
    }

    public function exportTemplate(): string
    {
        $path = $this->makeExportPath(class_basename($this->modelClass()) . '-template');

        $rows = collect([
            $this->templateSampleRow(),
        ]);

        (new FastExcel($rows))->export(storage_path('app/public/' . $path));

        return $path;
    }

    protected function validateImportFile(string $filePath): void
    {
        if (! file_exists($filePath)) {
            throw new \RuntimeException('File import không tồn tại.');
        }

        if (! is_readable($filePath)) {
            throw new \RuntimeException('File import không đọc được.');
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (! in_array($extension, ['xlsx', 'csv'], true)) {
            throw new \RuntimeException('Định dạng file không hợp lệ.');
        }
    }

    protected function hasRequiredHeaders(array $row): bool
    {
        foreach ($this->requiredHeaders as $header) {
            if (! array_key_exists($header, $row)) {
                return false;
            }
        }

        return true;
    }

    protected function persistRow(array $data, string $mode): Model
    {
        $modelClass = $this->modelClass();

        return match ($mode) {
            'create_only' => $modelClass::query()->create($data),

            'skip_duplicate' => $this->persistSkipDuplicate($modelClass, $data),

            'update_or_create' => $modelClass::query()->updateOrCreate(
                $this->uniquePayload($data),
                $data
            ),

            default => throw new \InvalidArgumentException("Import mode không hợp lệ: {$mode}"),
        };
    }

    protected function persistSkipDuplicate(string $modelClass, array $data): Model
    {
        $exists = $modelClass::query()
            ->where($this->uniquePayload($data))
            ->first();

        if ($exists) {
            $this->skippedRows++;

            return $exists;
        }

        return $modelClass::query()->create($data);
    }

    protected function uniquePayload(array $data): array
    {
        $payload = [];

        foreach ($this->uniqueBy as $field) {
            $payload[$field] = $data[$field] ?? null;
        }

        if (empty($payload)) {
            throw new \RuntimeException('Chưa khai báo uniqueBy cho import.');
        }

        return $payload;
    }

    protected function mapExportRow(Model $model): array
    {
        return $model->toArray();
    }

    protected function templateSampleRow(): array
    {
        $row = [];

        foreach ($this->requiredHeaders as $header) {
            $row[$header] = null;
        }

        return $row;
    }
}
