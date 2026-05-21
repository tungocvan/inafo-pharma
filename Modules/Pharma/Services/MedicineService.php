<?php

namespace Modules\Pharma\Services;

use Modules\Pharma\Models\Medicine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class MedicineService
{
    public function getPaginatedMedicines(?string $search = null, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return Medicine::query()
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('active_ingredients', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findOrFail(int $id): Medicine
    {
        return Medicine::findOrFail($id);
    }

    public function store(array $data): Medicine
    {
        return DB::transaction(function () use ($data) {
            return Medicine::create($data);
        });
    }

    public function update(int $id, array $data): Medicine
    {
        return DB::transaction(function () use ($id, $data) {
            $medicine = $this->findOrFail($id);
            $medicine->update($data);
            return $medicine;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $medicine = $this->findOrFail($id);
            return $medicine->delete();
        });
    }
}
