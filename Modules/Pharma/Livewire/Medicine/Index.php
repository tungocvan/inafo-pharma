<?php

namespace Modules\Pharma\Livewire\Medicine;

use Livewire\Component;
use Modules\Pharma\Services\MedicineService;

class Index extends Component
{
    public $search = '';
    public $page = 1;
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->page = 1;
    }

    public function gotoPage($page)
    {
        $this->page = $page;
    }

    public function deleteMedicine(MedicineService $medicineService, int $id)
    {
        try {
            $medicineService->delete($id);
            session()->flash('success', 'Đã xóa hồ sơ thuốc ra khỏi hệ thống.');
        } catch (\Exception $e) {
            session()->flash('error', 'Không thể xóa bản ghi này.');
        }
    }

    public function render(MedicineService $medicineService)
    {
        $medicines = $medicineService->getPaginatedMedicines($this->search, $this->perPage, $this->page);

        return view('Pharma::livewire.medicine.index', [
            'medicines' => $medicines
        ]);
    }
}
