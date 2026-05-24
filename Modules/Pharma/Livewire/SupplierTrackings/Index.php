<?php

namespace Modules\Pharma\Livewire\SupplierTrackings;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Modules\Pharma\Services\SupplierTrackingService;
use Rap2hpoutre\FastExcel\FastExcel;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;
    public array $selected = [];
    public bool $selectAll = false;

    public $importFile;

    public string $search = '';
    public string $status = '';

    public function updatedSearch(): void
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
            ->getFilteredIds([
                'search' => $this->search,
                'status' => $this->status,
            ])
            ->toArray();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

     public function delete(int $id, SupplierTrackingService $service): void
    {
        $service->delete($id);

        $this->resetSelection();

        session()->flash('success', 'Đã xóa thông tin theo dõi nhà cung cấp.');
    }
      public function deleteSelected(SupplierTrackingService $service): void
    {
        if (empty($this->selected)) {
            session()->flash('error', 'Vui lòng chọn dữ liệu cần xóa.');
            return;
        }

        $service->deleteMany($this->selected);

        $this->resetSelection();

        session()->flash('success', 'Đã xóa các dòng đã chọn.');
    }
      private function resetSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }
    public function import(SupplierTrackingService $service): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $path = $this->importFile->getRealPath();

        $rows = (new FastExcel)->import($path);

        $service->importRows($rows);

        $this->reset('importFile');

        session()->flash('success', 'Import dữ liệu thành công.');
    }

    public function export(SupplierTrackingService $service)
    {
        $rows = $service->exportRows([
            'search' => $this->search,
            'status' => $this->status,
        ]);

        return response()->streamDownload(function () use ($rows) {
            echo (new FastExcel($rows))->export('php://output');
        }, 'theo-doi-nha-cung-cap.xlsx');
    }

    public function render(SupplierTrackingService $service)
    {
        return view('Pharma::livewire.supplier-trackings.index', [
            'items' => $service->paginate([
                'search' => $this->search,
                'status' => $this->status,
            ]),
        ]);
    }
}