<?php

namespace Modules\Pharma\Livewire\SupplierTrackings;


use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Modules\Pharma\Services\SupplierTrackingService;
use Rap2hpoutre\FastExcel\FastExcel;


class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public string $status = '';

    public int $perPage = 15;

    public array $selected = [];
    public bool $selectAll = false;

    public $importFile = null;

    protected string $paginationTheme = 'tailwind';

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll(bool $value): void
    {
        if (! $value) {
            $this->selected = [];
            return;
        }

        $this->selected = app(SupplierTrackingService::class)
            ->getFilteredIds($this->filters())
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'status',
        ]);

        $this->perPage = 15;

        $this->resetPage();
        $this->resetSelection();
    }

    public function delete(int $id, SupplierTrackingService $service): void
    {
        try {
            $service->delete($id);

            $this->resetSelection();

            session()->flash('success', 'Đã xóa dữ liệu theo dõi nhà cung cấp.');
        } catch (\Throwable $e) {
            report($e);

            session()->flash('error', 'Không thể xóa dữ liệu. Vui lòng thử lại.');
        }
    }

    public function deleteSelected(SupplierTrackingService $service): void
    {
        if (empty($this->selected)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một dòng cần xóa.');
            return;
        }

        try {
            $service->deleteMany($this->selected);

            $this->resetSelection();

            session()->flash('success', 'Đã xóa các dòng đã chọn.');
        } catch (\Throwable $e) {
            report($e);

            session()->flash('error', 'Không thể xóa các dòng đã chọn. Vui lòng thử lại.');
        }
    }

    public function import(SupplierTrackingService $service): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ], [
            'importFile.required' => 'Vui lòng chọn file cần import.',
            'importFile.file' => 'File import không hợp lệ.',
            'importFile.mimes' => 'File import phải có định dạng xlsx, xls hoặc csv.',
        ]);

        try {
            $rows = (new FastExcel)->import($this->importFile->getRealPath());

            $result = $service->importRows($rows);

            $this->reset('importFile');
            $this->resetSelection();
            $this->resetPage();

            session()->flash(
                'success',
                "Import hoàn tất. Thành công: {$result['success']}, bỏ qua: {$result['skipped']}."
            );

            if (! empty($result['errors'])) {
                session()->flash('import_errors', $result['errors']);
            }
        } catch (\Throwable $e) {
            report($e);

            session()->flash('error', 'Import thất bại. Vui lòng kiểm tra lại file Excel.');
        }
    }

    public function export(SupplierTrackingService $service)
    {
        try {
            $rows = $service->exportRows($this->filters());

            $fileName = 'theo-doi-nha-cung-cap-' . now()->format('Ymd-His') . '.xlsx';

            $tempPath = storage_path('app/temp/' . $fileName);

            if (! is_dir(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            (new \Rap2hpoutre\FastExcel\FastExcel($rows))->export($tempPath);

            return response()
                ->download($tempPath, $fileName)
                ->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            report($e);

            session()->flash('error', 'Export thất bại. Vui lòng thử lại.');

            return null;
        }
    }

    public function getHasSelectedProperty(): bool
    {
        return count($this->selected) > 0;
    }

    public function getSelectedCountProperty(): int
    {
        return count($this->selected);
    }

    public function money($value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return number_format((float) $value, 0, ',', '.');
    }

    public function percent($value): string
    {
        if ($value === null || $value === '') {
            return '0%';
        }

        return number_format((float) $value, 2, ',', '.') . '%';
    }

    private function filters(): array
    {
        return [
            'search' => trim($this->search),
            'status' => $this->status,
        ];
    }

    private function resetSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function render(SupplierTrackingService $service)
    {
        return view('Pharma::livewire.supplier-trackings.index', [
            'items' => $service->paginate(
                filters: $this->filters(),
                perPage: $this->perPage
            ),
            'statuses' => $this->statuses(),
        ]);
    }

    private function statuses(): array
    {
        return [
            'active' => 'Đang theo dõi',
            'completed' => 'Hoàn tất',
            'paused' => 'Tạm dừng',
            'cancelled' => 'Hủy',
        ];
    }
}
