<?php

namespace Modules\Pharma\Livewire\SupplierTrackings;

use Livewire\Component;
use Modules\Pharma\Models\Medicine;
use Modules\Pharma\Services\SupplierTrackingService;

class Form extends Component
{
    public ?int $trackingId = null;
    public ?int $medicine_id = null;

    public array $form = [
        'medicine_id' => '',
        'working_date' => '',
        'supplier_name' => '',
        'supplier_representative' => '',
        'area' => '',
        'import_price' => '',
        'cost_price' => '',
        'selling_price' => '',
        'invoice_price' => '',
        'difference_percent' => '',
        'committed_quantity' => '',
        'unit' => '',
        'deposit_amount' => '',
        'start_date' => '',
        'end_date' => '',
        'contract_url' => '',
        'status' => 'active',
        'note' => '',
    ];

    public function mount(SupplierTrackingService $service, $id = null): void
    {
        $this->trackingId = $id;

        if ($id) {
            $tracking = $service->find($id);         
            $this->form = array_merge($this->form, $tracking->only(array_keys($this->form)));
            $this->form['working_date'] = $this->form['working_date']?->format('Y-m-d') ?? null;
            $this->form['start_date'] = $this->form['start_date']->format('Y-m-d');
            $this->form['end_date'] = $this->form['end_date']->format('Y-m-d');
           // dd($this->form);
            $this->medicine_id = $tracking->medicine_id;
        }
    }

    public function save(SupplierTrackingService $service)
    {
        $this->form['medicine_id'] = $this->medicine_id;
        $data = $this->validate([
            'form.medicine_id' => ['required', 'exists:pharma_medicines,id'],
            'form.working_date' => ['nullable', 'date'],
            'form.supplier_name' => ['required', 'string', 'max:255'],
            'form.supplier_representative' => ['nullable', 'string', 'max:255'],
            'form.area' => ['nullable', 'string', 'max:255'],
            'form.import_price' => ['nullable', 'numeric'],
            'form.cost_price' => ['nullable', 'numeric'],
            'form.selling_price' => ['nullable', 'numeric'],
            'form.invoice_price' => ['nullable', 'numeric'],
            'form.difference_percent' => ['nullable', 'numeric'],
            'form.committed_quantity' => ['nullable', 'numeric'],
            'form.unit' => ['nullable', 'string', 'max:50'],
            'form.deposit_amount' => ['nullable', 'numeric'],
            'form.start_date' => ['nullable', 'date'],
            'form.end_date' => ['nullable', 'date'],
            'form.contract_url' => ['nullable', 'string'],
            'form.status' => ['required', 'string'],
            'form.note' => ['nullable', 'string'],
        ])['form'];

        if ($this->trackingId) {
            $service->update($this->trackingId, $data);
            session()->flash('success', 'Đã cập nhật thông tin theo dõi nhà cung cấp.');
        } else {
            $service->create($data);
            session()->flash('success', 'Đã thêm thông tin theo dõi nhà cung cấp.');
        }

        return redirect()->route('admin.pharma.supplier-trackings.index');
    }

    public function render()
    {
        return view('Pharma::livewire.supplier-trackings.form', [
            'medicines' => Medicine::query()
                ->select('id', 'name', 'registration_number', 'packaging_specification', 'unit')
                ->orderBy('name')
                ->limit(1000)
                ->get(),
        ]);
    }
}
