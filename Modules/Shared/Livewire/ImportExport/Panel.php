<?php

namespace Modules\Shared\Livewire\ImportExport;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Panel extends Component
{
    use WithFileUploads;

    public string $serviceClass;

    public string $title = 'Import / Export dữ liệu';

    public string $description = 'Tải file mẫu, import dữ liệu hoặc export dữ liệu hiện tại.';

    public mixed $file = null;

    public string $mode = 'update_or_create';

    public bool $dryRun = false;

    public ?array $report = null;

    public array $filters = [];

    public function mount(
        string $serviceClass,
        string $title = 'Import / Export dữ liệu',
        string $description = 'Tải file mẫu, import dữ liệu hoặc export dữ liệu hiện tại.',
        array $filters = []
    ): void {
        $this->serviceClass = $serviceClass;
        $this->title = $title;
        $this->description = $description;
        $this->filters = $filters;
    }

    protected function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,csv', 'max:10240'],
            'mode' => ['required', 'in:create_only,update_or_create,skip_duplicate,replace'],
            'dryRun' => ['boolean'],
        ];
    }

    public function import(): void
    {
        $this->validate();

        $service = app($this->serviceClass);

        $this->report = $service->import($this->file->getRealPath(), [
            'mode' => $this->mode,
            'dry_run' => $this->dryRun,
        ]);

        $this->file = null;

        if (($this->report['success'] ?? false) === true) {
            session()->flash('success', 'Import hoàn tất.');
        } else {
            session()->flash('error', 'Import có lỗi, vui lòng kiểm tra bảng lỗi.');
        }
    }

    public function export()
    {
        $service = app($this->serviceClass);

        $path = $service->export($this->filters);

        return Storage::disk('public')->download($path);
    }

    public function exportTemplate()
    {
        $service = app($this->serviceClass);

        $path = $service->exportTemplate();

        return Storage::disk('public')->download($path);
    }

    public function render(): View
    {
        return view('Shared::livewire.import-export.panel');
    }
}