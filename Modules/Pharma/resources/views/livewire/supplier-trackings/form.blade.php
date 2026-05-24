<form wire:submit="save" class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $trackingId ? 'Cập nhật theo dõi nhà cung cấp' : 'Thêm theo dõi nhà cung cấp' }}
        </h1>
        <p class="mt-1 text-sm text-gray-500">Nhập thông tin làm việc với nhà cung cấp.</p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-gray-700">Sản phẩm / thuốc</label>

                <x-select-search id="select-medicine-id" wire:model="medicine_id" placeholder="-- Chọn thuốc --">
                    <option value="">-- Chọn thuốc --</option>

                    @foreach ($medicines as $med)
                        <option value="{{ $med->id }}" data-name="{{ $med->name }}"
                            data-pack="{{ $med->packaging_specification }}" data-unit="{{ $med->unit }}">
                            {{ $med->name }} ({{ $med->registration_number }})
                        </option>
                    @endforeach
                </x-select-search>

                @error('form.medicine_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Ngày làm việc</label>
                <input type="date" wire:model.live="form.working_date"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Nhà cung cấp</label>
                <input type="text" wire:model.live="form.supplier_name"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                @error('form.supplier_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Người đại diện</label>
                <input type="text" wire:model.live="form.supplier_representative"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Khu vực</label>
                <input type="text" wire:model.live="form.area"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Trạng thái</label>
                <select wire:model.live="form.status"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <option value="active">Đang theo dõi</option>
                    <option value="completed">Hoàn tất</option>
                    <option value="paused">Tạm dừng</option>
                    <option value="cancelled">Hủy</option>
                </select>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">Thông tin giá</h2>

        <div class="grid gap-5 md:grid-cols-4">
            @foreach ([
        'import_price' => 'Giá nhập',
        'cost_price' => 'Giá vốn',
        'selling_price' => 'Giá bán',
        'invoice_price' => 'Giá hóa đơn',
        'difference_percent' => '% chia chênh lệch',
        'committed_quantity' => 'Số lượng cam kết',
        'unit' => 'Đơn vị',
        'deposit_amount' => 'Tiền cọc',
    ] as $field => $label)
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ $label }}</label>
                    <input type="{{ $field === 'unit' ? 'text' : 'number' }}" step="0.01"
                        wire:model.live="form.{{ $field }}"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">Hợp đồng & ghi chú</h2>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-gray-700">Ngày bắt đầu</label>
                <input type="date" wire:model.live="form.start_date"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Ngày kết thúc</label>
                <input type="date" wire:model.live="form.end_date"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-700">URL hợp đồng</label>
                <input type="text" wire:model.live="form.contract_url"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-700">Ghi chú</label>
                <textarea wire:model.live="form.note" rows="4"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></textarea>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.pharma.supplier-trackings.index') }}"
            class="inline-flex h-[50px] items-center justify-center rounded-xl border border-gray-300 bg-white px-5 font-semibold text-gray-700 hover:bg-gray-50">
            Hủy
        </a>

        <button type="submit"
            class="inline-flex h-[50px] items-center justify-center rounded-xl bg-indigo-600 px-5 font-semibold text-white shadow-sm hover:bg-indigo-500">
            Lưu dữ liệu
        </button>
    </div>
</form>
