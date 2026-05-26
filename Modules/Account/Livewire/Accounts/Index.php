<?php

namespace Modules\Account\Livewire\Accounts;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Modules\Account\Services\AccountService;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';

    public string $accountType = '';

    public string $isActive = '';

    public string|int $perPage = 10;

    public array $perPageOptions = [10, 25, 50, 100, 'All'];

    public array $selectedIds = [];

    public bool $selectAll = false;

    public ?TemporaryUploadedFile $importFile = null;

    protected AccountService $accountService;

    public function boot(AccountService $accountService): void
    {
        $this->accountService = $accountService;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAccountType(): void
    {
        $this->resetPage();
    }

    public function updatedIsActive(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        if (! $value) {
            $this->selectedIds = [];

            return;
        }

        $accounts = $this->accountService->paginate(
            filters: $this->filters(),
            perPage: $this->perPage
        );

        $this->selectedIds = collect($accounts instanceof \Illuminate\Pagination\AbstractPaginator ? $accounts->items() : $accounts)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    public function delete(int $id): void
    {
        $this->accountService->delete($id);

        $this->selectedIds = array_values(array_diff($this->selectedIds, [(string) $id]));

        session()->flash('success', 'Đã xóa tài khoản thành công.');
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một tài khoản.');

            return;
        }

        $this->accountService->bulkDelete($this->selectedIds);

        $this->selectedIds = [];
        $this->selectAll = false;

        session()->flash('success', 'Đã xóa các tài khoản đã chọn.');
    }

    public function toggleActive(int $id): void
    {
        $this->accountService->toggleActive($id);

        session()->flash('success', 'Đã cập nhật trạng thái tài khoản.');
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'account_type' => $this->accountType,
            'is_active' => $this->isActive,
        ];
    }

    public function import(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,csv'],
        ], [
            'importFile.required' => 'Vui lòng chọn file import.',
            'importFile.mimes' => 'File import phải là xlsx hoặc csv.',
        ]);

        $count = $this->accountService->importFromExcel(
            $this->importFile->getRealPath()
        );

        $this->importFile = null;
        $this->resetPage();

        session()->flash('success', "Đã import {$count} tài khoản.");
    }

    public function export()
    {
        $filePath = $this->accountService->exportToExcel($this->filters());

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('Account::livewire.accounts.index', [
            'accounts' => $this->accountService->paginate(
                filters: $this->filters(),
                perPage: $this->perPage
            ),
        ]);
    }
}
