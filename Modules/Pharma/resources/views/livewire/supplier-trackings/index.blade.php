<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Theo dõi nhà cung cấp</h1>
            <p class="mt-1 text-sm text-gray-500">Quản lý thông tin làm việc, giá và hợp đồng với nhà cung cấp.</p>
        </div>

        <a href="{{ route('admin.pharma.supplier-trackings.create') }}"
            class="inline-flex h-[50px] items-center justify-center rounded-xl bg-indigo-600 px-5 font-semibold text-white shadow-sm hover:bg-indigo-500">
            Thêm mới
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
            <input type="text" wire:model.live="search" placeholder="Tìm thuốc, nhà cung cấp, đại diện..."
                class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">

            <select wire:model.live="status"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                <option value="">Tất cả trạng thái</option>
                <option value="active">Đang theo dõi</option>
                <option value="completed">Hoàn tất</option>
                <option value="paused">Tạm dừng</option>
                <option value="cancelled">Hủy</option>
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div
            class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap gap-3">
                <button type="button" wire:click="deleteSelected"
                    wire:confirm="Bạn chắc chắn muốn xóa các dòng đã chọn?"
                    class="inline-flex h-[50px] items-center justify-center rounded-xl bg-red-600 px-5 font-semibold text-white shadow-sm hover:bg-red-500">
                    Xóa đã chọn
                </button>

                <button type="button" wire:click="export"
                    class="inline-flex h-[50px] items-center justify-center rounded-xl bg-emerald-600 px-5 font-semibold text-white shadow-sm hover:bg-emerald-500">
                    Export Excel
                </button>
            </div>

            <form wire:submit="import" class="flex flex-col gap-3 md:flex-row md:items-center">
                <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 md:w-72">

                <button type="submit"
                    class="inline-flex h-[50px] items-center justify-center rounded-xl bg-indigo-600 px-5 font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Import
                </button>
            </form>
        </div>

        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" wire:model.live="selectAll"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Thuốc</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nhà cung cấp</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Giá nhập</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Giá HĐ</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Chênh lệch</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Trạng thái</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Thao tác</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($items as $item)
                    <tr>
                        <td class="px-4 py-4">
                            <input type="checkbox" wire:model.live="selected" value="{{ $item->id }}"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-medium text-gray-900">{{ $item->medicine?->name }}</div>
                            <div class="text-sm text-gray-500">{{ $item->medicine?->registration_number }}</div>
                        </td>

                        <td class="px-4 py-4">
                            <div class="font-medium text-gray-900">{{ $item->supplier_name }}</div>
                            <div class="text-sm text-gray-500">{{ $item->supplier_representative }}</div>
                        </td>

                        <td class="px-4 py-4 text-right">{{ number_format($item->import_price) }}</td>
                        <td class="px-4 py-4 text-right">{{ number_format($item->invoice_price) }}</td>
                        <td class="px-4 py-4 text-right">{{ number_format($item->price_difference) }}</td>

                        <td class="px-4 py-4 text-center">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                {{ $item->status }}
                            </span>
                        </td>

                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('admin.pharma.supplier-trackings.edit', $item->id) }}"
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                                Sửa
                            </a>

                            <button wire:click="delete({{ $item->id }})" wire:confirm="Bạn chắc chắn muốn xóa?"
                                class="ml-3 text-sm font-semibold text-red-600 hover:text-red-500">
                                Xóa
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                            Chưa có dữ liệu theo dõi nhà cung cấp.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="border-t border-gray-200 p-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
