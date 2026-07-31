@extends('Admin::layouts.master')
@section('title', 'Quản lý Module')
@section('content')
    @livewire('system.settings.modules-form')

    <div class="mt-6 space-y-4" id="module-route-manager">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-semibold text-gray-900">Route GET của Modules</h2>
                        <span class="bg-emerald-100 text-emerald-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                            {{ $routes->count() }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        Danh sách được quét từ các route đã đăng ký có controller hoặc file route nằm trong Modules.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 lg:w-2/5">
                    <label>
                        <span class="sr-only">Tìm route</span>
                        <input
                            id="route-search"
                            type="search"
                            placeholder="Tìm URI, tên hoặc action..."
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </label>
                    <label>
                        <span class="sr-only">Lọc theo module</span>
                        <select
                            id="route-module-filter"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Tất cả module</option>
                            @foreach ($routeModules as $module)
                                <option value="{{ Str::lower($module) }}">{{ $module }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên route</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Middleware</th>
                        </tr>
                    </thead>
                    <tbody id="route-table-body" class="bg-white divide-y divide-gray-200">
                        @forelse ($routes as $route)
                            @php
                                $searchValue = Str::lower(implode(' ', [
                                    $route['module'],
                                    $route['uri'],
                                    $route['name'] ?? '',
                                    $route['action'],
                                    implode(' ', $route['middleware']),
                                ]));
                            @endphp
                            <tr
                                class="route-row hover:bg-gray-50"
                                data-module="{{ Str::lower($route['module']) }}"
                                data-search="{{ $searchValue }}"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $route['module'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-mono whitespace-nowrap">{{ $route['uri'] }}</div>
                                    @if ($route['domain'])
                                        <div class="mt-1 text-xs text-gray-500">{{ $route['domain'] }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $route['name'] ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <span class="font-mono text-xs break-all">{{ $route['action'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($route['middleware'] as $middleware)
                                            <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-xs">
                                                {{ $middleware }}
                                            </span>
                                        @empty
                                            <span class="text-gray-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                    Không tìm thấy route GET nào thuộc Modules.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="route-empty-filter" class="hidden px-6 py-10 text-center text-sm text-gray-500">
                Không có route phù hợp với bộ lọc.
            </div>
            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 text-sm text-gray-600">
                Đang hiển thị <span id="route-visible-count" class="font-medium">{{ $routes->count() }}</span>
                / {{ $routes->count() }} route
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('route-search');
            const moduleFilter = document.getElementById('route-module-filter');
            const rows = Array.from(document.querySelectorAll('.route-row'));
            const visibleCount = document.getElementById('route-visible-count');
            const emptyFilter = document.getElementById('route-empty-filter');
            const tableBody = document.getElementById('route-table-body');

            if (!searchInput || !moduleFilter) return;

            const filterRoutes = () => {
                const keyword = searchInput.value.trim().toLocaleLowerCase('vi');
                const module = moduleFilter.value;
                let count = 0;

                rows.forEach((row) => {
                    const matchesKeyword = !keyword || row.dataset.search.includes(keyword);
                    const matchesModule = !module || row.dataset.module === module;
                    const visible = matchesKeyword && matchesModule;

                    row.classList.toggle('hidden', !visible);
                    if (visible) count++;
                });

                visibleCount.textContent = count;
                emptyFilter.classList.toggle('hidden', count !== 0 || rows.length === 0);
                tableBody.classList.toggle('hidden', count === 0 && rows.length > 0);
            };

            searchInput.addEventListener('input', filterRoutes);
            moduleFilter.addEventListener('change', filterRoutes);
        });
    </script>
@endpush
