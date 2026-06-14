# Modules/Pharma — Analysis

Generated: 2026-06-14

Summary: detailed slice-level analysis of `Modules/Pharma` following the requested flow.

**Module purpose**
- Provide administration UIs and import/export for Pharma domain: medicines catalogue, drug bid awards, and supplier tracking.
- Key folder: Modules/Pharma

---

**Route list**
- Web routes (admin, auth:admin middleware):
  - [Modules/Pharma/routes/web.php](Modules/Pharma/routes/web.php)
    - GET `admin/pharma/hssp/` -> name: `admin.pharma.hssp.index` -> Controller: `Modules\\Pharma\\Http\\Controllers\\PharmaController@index` ([file](Modules/Pharma/Http/Controllers/PharmaController.php))
    - GET `admin/pharma/hssp/create` -> name: `admin.pharma.hssp.create` -> `PharmaController@create`
    - GET `admin/pharma/hssp/{id}/edit` -> name: `admin.pharma.hssp.edit` -> `PharmaController@edit`
    - GET `admin/pharma/drug-bid-awards/` -> `admin.pharma.drug-bid-awards.index` -> `DrugBidAwardController@index` ([file](Modules/Pharma/Http/Controllers/DrugBidAwardController.php))
    - GET `admin/pharma/drug-bid-awards/create` -> `admin.pharma.drug-bid-awards.create` -> `DrugBidAwardController@create`
    - GET `admin/pharma/drug-bid-awards/{id}/edit` -> `admin.pharma.drug-bid-awards.edit` -> `DrugBidAwardController@edit`
    - GET `admin/pharma/supplier-trackings/` -> `admin.pharma.supplier-trackings.index` -> `SupplierTrackingController@index` ([file](Modules/Pharma/Http/Controllers/SupplierTrackingController.php))
    - GET `admin/pharma/supplier-trackings/create` -> `admin.pharma.supplier-trackings.create` -> `SupplierTrackingController@create`
    - GET `admin/pharma/supplier-trackings/{id}/edit` -> `admin.pharma.supplier-trackings.edit` -> `SupplierTrackingController@edit`
    - GET `admin/pharma/supplier-trackings/{id}/show` -> `SupplierTrackingController@show`
    - GET `admin/pharma/supplier-trackings/import-export` -> `admin.pharma.supplier-trackings.import-export` -> `SupplierTrackingController@importExport` (route exists in file)
- API routes:
  - [Modules/Pharma/routes/api.php](Modules/Pharma/routes/api.php)
    - GET `/pharma` -> `Modules\\Pharma\\Http\\Controllers\\Api\\PharmaController@index` (note: controller is empty) ([file](Modules/Pharma/Http/Controllers/Api/PharmaController.php))

---

**Controllers**
- [Modules/Pharma/Http/Controllers/PharmaController.php](Modules/Pharma/Http/Controllers/PharmaController.php)
  - Methods: `index()`, `create()`, `edit(int $id)` — only return pages (no business logic). No authorization calls (commented middleware present but disabled).
- [Modules/Pharma/Http/Controllers/DrugBidAwardController.php](Modules/Pharma/Http/Controllers/DrugBidAwardController.php)
  - Methods: `index()`, `create()`, `edit(int $id)` — return blade pages.
- [Modules/Pharma/Http/Controllers/SupplierTrackingController.php](Modules/Pharma/Http/Controllers/SupplierTrackingController.php)
  - Methods: `index()`, `create()`, `edit(int $id)`, `show(int $id)` — return blade pages.
- [Modules/Pharma/Http/Controllers/Api/PharmaController.php](Modules/Pharma/Http/Controllers/Api/PharmaController.php)
  - Empty stub.

Notes: controllers are presentation-only — Livewire components and services hold business logic.

---

**Page Blade files**
- Pages that mount Livewire components:
  - [Modules/Pharma/resources/views/pages/index.blade.php](Modules/Pharma/resources/views/pages/index.blade.php) -> `@livewire('pharma.medicine.index')`
  - [Modules/Pharma/resources/views/pages/create.blade.php](Modules/Pharma/resources/views/pages/create.blade.php) -> `@livewire('pharma.medicine.form')`
  - [Modules/Pharma/resources/views/pages/edit.blade.php](Modules/Pharma/resources/views/pages/edit.blade.php) -> `@livewire('pharma.medicine.form', ['id' => $id])`
  - [Modules/Pharma/resources/views/pages/drug-bid-award/index.blade.php](Modules/Pharma/resources/views/pages/drug-bid-award/index.blade.php) -> `@livewire('pharma.drug-bid-award.index')`
  - [Modules/Pharma/resources/views/pages/drug-bid-award/create.blade.php](Modules/Pharma/resources/views/pages/drug-bid-award/create.blade.php) -> `@livewire('pharma.drug-bid-award.form')`
  - [Modules/Pharma/resources/views/pages/drug-bid-award/edit.blade.php](Modules/Pharma/resources/views/pages/drug-bid-award/edit.blade.php) -> `@livewire('pharma.drug-bid-award.form', ['id'=>$id])`
  - [Modules/Pharma/resources/views/pages/supplier-trackings/*.blade.php](Modules/Pharma/resources/views/pages/supplier-trackings) -> supplier trackings pages mount Livewire components.

---

**Livewire PHP classes** (component classes)
- [Modules/Pharma/Livewire/Medicine/Index.php](Modules/Pharma/Livewire/Medicine/Index.php)
  - Public: `$search, $page, $perPage, $filterCircularGroup, $filterSpecialControl, $selectedIds, $selectAll, $importFile`
  - Listeners: `refreshComponent`.
  - Actions: `importData()`, `deleteMedicine(int $id)`, `deleteSelected()`, `exportData()`, `resetFilters()`, pagination helpers.
  - Interacts with: `Modules\\Pharma\\Services\\MedicineService` (dependency-injected or via app()).
- [Modules/Pharma/Livewire/Medicine/Form.php](Modules/Pharma/Livewire/Medicine/Form.php)
  - Mounts medicine by id via `MedicineService::findOrFail`.
  - Rules defined in `rules()`.
  - Actions: `save()` -> calls `MedicineService->store` or `update`.
- [Modules/Pharma/Livewire/DrugBidAward/Index.php](Modules/Pharma/Livewire/DrugBidAward/Index.php)
  - Uses `WithPagination`, import/export and batch delete; interacts with `DrugBidAwardService`.
- [Modules/Pharma/Livewire/DrugBidAward/Form.php](Modules/Pharma/Livewire/DrugBidAward/Form.php)
  - Mounts `DrugBidAwardService::findOrFail` and renders a list of `Medicine::latest()->get()`.
  - Rules provided via `rules()`.
- [Modules/Pharma/Livewire/SupplierTrackings/Index.php](Modules/Pharma/Livewire/SupplierTrackings/Index.php)
  - Actions: import/export, delete, deleteSelected; interacts with `SupplierTrackingService` and `Modules\\Pharma\\Services\\ImportExport` via shared import-export panel in blade.
- [Modules/Pharma/Livewire/SupplierTrackings/Form.php](Modules/Pharma/Livewire/SupplierTrackings/Form.php) — (file exists in file list; review expected but not opened; similar pattern to other forms).

Livewire naming: components are registered under `pharma.*` (see page blades using `pharma.medicine.index`, `pharma.drug-bid-award.index`, `pharma.supplier-trackings.index`).

---

**Livewire Blade views**
- [Modules/Pharma/resources/views/livewire/medicine/index.blade.php](Modules/Pharma/resources/views/livewire/medicine/index.blade.php)
- [Modules/Pharma/resources/views/livewire/medicine/form.blade.php](Modules/Pharma/resources/views/livewire/medicine/form.blade.php)
- [Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php](Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php)
- [Modules/Pharma/resources/views/livewire/drug-bid-award/form.blade.php](Modules/Pharma/resources/views/livewire/drug-bid-award/form.blade.php)
- [Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php](Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php)
- [Modules/Pharma/resources/views/livewire/supplier-trackings/form.blade.php](Modules/Pharma/resources/views/livewire/supplier-trackings/form.blade.php)
- Shared/simple placeholders: [Modules/Pharma/resources/views/livewire/placeholder.blade.php](Modules/Pharma/resources/views/livewire/placeholder.blade.php)

---

**Shared Components used (references)**
- Uses `x-select-search` (project-level shared component) from views; not defined in this module. Search path: global components (likely `Modules/Shared` or `resources/views/components`).
- Module-local component: [Modules/Pharma/resources/views/components/placeholder.blade.php](Modules/Pharma/resources/views/components/placeholder.blade.php)

---

**Services and public methods**
All service files located in `Modules/Pharma/Services`.

- [Modules/Pharma/Services/MedicineService.php](Modules/Pharma/Services/MedicineService.php)
  - getPaginatedMedicines(?string $search, int $perPage, int $page, ?string $circularGroup, ?string $specialControl): LengthAwarePaginator
  - getUniqueCircularGroups(): array
  - findOrFail(int $id): Medicine
  - store(array $data): Medicine
  - update(int $id, array $data): Medicine
  - delete(int $id): bool
  - importFromCsv(string $filePath): int
  - exportToCsv(?string $search, ?string $circularGroup, ?string $specialControl): string

- [Modules/Pharma/Services/MedicineImportService.php](Modules/Pharma/Services/MedicineImportService.php)
  - importFromCsv(string $filePath): int
  - Note: overlaps strongly with `MedicineService::importFromCsv` — duplicate import implementations.

- [Modules/Pharma/Services/DrugBidAwardService.php](Modules/Pharma/Services/DrugBidAwardService.php)
  - getPaginated(?string $search, ?string $investor, ?string $company, int $perPage): LengthAwarePaginator
  - findOrFail(int $id)
  - store(array $data)
  - update(int $id, array $data)
  - delete(int $id): bool
  - getUniqueInvestors(): array
  - getUniqueCompanies(): array
  - importFromCsv(string $filePath): int
  - exportToCsv(?string $search, ?string $investor, ?string $company): string

- [Modules/Pharma/Services/SupplierTrackingService.php](Modules/Pharma/Services/SupplierTrackingService.php)
  - paginate(array $filters = [], int $perPage = 15)
  - medicinesForSelect(): Collection
  - find(int $id): SupplierTracking
  - create(array $data): SupplierTracking
  - update(int $id, array $data): SupplierTracking
  - delete(int $id)
  - deleteMany(array $ids)
  - getFilteredIds(array $filters = []): Collection
  - previewCalculate(array $data): array
  - exportRows(array $filters = []): Collection
  - importRows(Collection $rows): array

- [Modules/Pharma/Services/ImportExport.php](Modules/Pharma/Services/ImportExport.php)
  - Extends `Modules\\Shared\\Services\\ImportExport\\BaseImportExportService`
  - modelClass(), rules(), columnMapping(), normalizeRow(), exportRows(), mapExportRow(), templateSampleRow()
  - Purpose: plug into shared import-export panel used in supplier-trackings index view.

- CLI: [Modules/Pharma/Console/Commands/ImportMedicineCommand.php](Modules/Pharma/Console/Commands/ImportMedicineCommand.php)
  - `medicine:import {file}` — delegates to `MedicineImportService`.

---

**Models and database tables**
- [Modules/Pharma/Models/Medicine.php](Modules/Pharma/Models/Medicine.php)
  - Table: `pharma_medicines` (migration: [database/migrations/2026_05_21_145242_create_medicines_table.php](Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php))
  - Important: composite unique index `(registration_number, packaging_specification)` defined in migration.
- [Modules/Pharma/Models/DrugBidAward.php](Modules/Pharma/Models/DrugBidAward.php)
  - Table: `pharma_drug_bid_awards` (migration: [database/migrations/2026_05_22_135028_create_drug_bid_awards_table.php](Modules/Pharma/database/migrations/2026_05_22_135028_create_drug_bid_awards_table.php))
  - Relationship: `medicine()` belongsTo `Medicine` with `medicine_id` nullable.
- [Modules/Pharma/Models/SupplierTracking.php](Modules/Pharma/Models/SupplierTracking.php)
  - Table: `pharma_supplier_trackings` (migration: [database/migrations/2026_05_23_141810_create_supplier_trackings_table.php](Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php))
  - Relationship: `medicine()` belongsTo `Medicine` cascade on delete.
- [Modules/Pharma/Models/Pharma.php](Modules/Pharma/Models/Pharma.php) — placeholder/empty model.

---

**Migrations**
- [Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php](Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php)
- [Modules/Pharma/database/migrations/2026_05_22_135028_create_drug_bid_awards_table.php](Modules/Pharma/database/migrations/2026_05_22_135028_create_drug_bid_awards_table.php)
- [Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php](Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php)

Notes: migrations declare indexes and constraints (including composite unique on bid awards and unique on medicine reg+pack). SupplierTracking uses cascadeOnDelete for medicine.

---

**Import / Export classes**
- `Modules/Pharma/Services/ImportExport.php` — adapter for the shared import-export base service. Handles column mapping, normalization, and export mapping.
- `Modules/Pharma/Services/MedicineImportService.php` — CLI/streaming CSV importer used by `medicine:import` command. Duplicate with `MedicineService::importFromCsv`.
- `Modules/Pharma/Services/MedicineService::importFromCsv()` — web Livewire importer; similar logic to `MedicineImportService`.
- `Modules/Pharma/Console/Commands/ImportMedicineCommand.php` — CLI wrapper.

Risks: two different CSV import implementations (see Duplicate Logic).

---

**Authorization / security risks**
Every risk below lists the exact file path where the risky behavior or missing guard is found.

- Missing action-level authorization on admin routes and Livewire actions: [Modules/Pharma/routes/web.php](Modules/Pharma/routes/web.php) — routes are protected by `auth:admin` only; controllers have commented-out permission middleware ([Modules/Pharma/Http/Controllers/PharmaController.php](Modules/Pharma/Http/Controllers/PharmaController.php)). Priority: P0.

- File imports and downloads accept user-provided files/paths and immediately process them:
  - Livewire import handlers read uploaded files directly: [Modules/Pharma/Livewire/Medicine/Index.php](Modules/Pharma/Livewire/Medicine/Index.php)::importData and [Modules/Pharma/Livewire/DrugBidAward/Index.php](Modules/Pharma/Livewire/DrugBidAward/Index.php)::importData. Priority: P0.

- Export endpoints write to `storage_path('app/public/...')` and return file downloads: [Modules/Pharma/Services/MedicineService.php](Modules/Pharma/Services/MedicineService.php)::exportToCsv and [Modules/Pharma/Services/DrugBidAwardService.php](Modules/Pharma/Services/DrugBidAwardService.php)::exportToCsv — ensure storage permissions and cleanup. Priority: P1.

- CLI command accepts arbitrary file path `medicine:import {file}` -> [Modules/Pharma/Console/Commands/ImportMedicineCommand.php](Modules/Pharma/Console/Commands/ImportMedicineCommand.php). If run in production, could allow reading arbitrary server files if misused. Priority: P0.

- Potential disclosure of exception messages in session flashes: many Livewire components append `$e->getMessage()` to user-visible `session()->flash('error', ...)` — e.g. [Modules/Pharma/Livewire/Medicine/Index.php](Modules/Pharma/Livewire/Medicine/Index.php)::importData and others. This may leak internal data. Priority: P0.

- Api controller stub exists with public GET: [Modules/Pharma/routes/api.php](Modules/Pharma/routes/api.php) -> [Modules/Pharma/Http/Controllers/Api/PharmaController.php](Modules/Pharma/Http/Controllers/Api/PharmaController.php) (empty) — review before enabling. Priority: P1.

---

**Validation problems**
- Generally good Livewire `rules()` present in form components. Noted issues:
  - `MedicineForm::rules()` uses `'profile_link' => 'nullable|url'` — if users paste internal file paths, `url` won't block `file://`; Laravel `url` rule passes `http/https`; confirm acceptance. File: [Modules/Pharma/Livewire/Medicine/Form.php](Modules/Pharma/Livewire/Medicine/Form.php). Priority: P1.

  - `DrugBidAward/Form.php` uses `'medicine_id' => 'nullable|exists:pharma_medicines,id'` — when `medicine_id` is null there is no enforcement that `medicine_name` must match some controlled vocabulary; risk of inconsistent data. File: [Modules/Pharma/Livewire/DrugBidAward/Form.php]. Priority: P2.

  - `SupplierTrackingService::importRows()` expects specific headers and performs `create()` calls without validating final payload against service rules (validation occurs in `ImportExport` class when using shared panel). Two import paths (FastExcel in Livewire and BaseImportExportService) may accept different column sets; mismatch risk. Files: [Modules/Pharma/Livewire/SupplierTrackings/Index.php], [Modules/Pharma/Services/ImportExport.php]. Priority: P1.

  - CSV import implementations use `fgetcsv` without explicit encoding checks or robust header mapping; malformed CSVs may shift columns. Files: [Modules/Pharma/Services/MedicineService.php], [Modules/Pharma/Services/MedicineImportService.php], [Modules/Pharma/Services/DrugBidAwardService.php]. Priority: P1.

---

**Transaction risks**
- Many write operations use DB transactions (`store`, `update`, `delete` in `MedicineService`, `DrugBidAwardService`). Good.
  - [Modules/Pharma/Services/MedicineService.php] — uses DB::transaction for store/update/delete and uses DB::beginTransaction in importFromCsv and properly rollbacks on error. Good.
  - [Modules/Pharma/Services/DrugBidAwardService.php] — uses transactions for store/update/delete and for import. Good.
- Problems:
  - `SupplierTrackingService::create()` and `update()` do not wrap operations in a DB transaction; they call Eloquent `create()` and `update()` directly and rely on simple operations. If importRows loops call `create()` many times, partial failures may leave partial state. File: [Modules/Pharma/Services/SupplierTrackingService.php]. Priority: P1.
  - Batch deletion in `SupplierTrackingService::deleteMany()` uses direct `whereIn('id', $ids)->delete()` without transaction or soft-delete semantics; if downstream invariants exist, this may be risky. Priority: P1.
  - Livewire batch delete loops call service->delete per id (each inside transaction for Medicine/DrugBidAward) — fine but slow; bulk delete could be optimized with transaction/wrapper. Files: livewire components. Priority: P2.

---

**N+1 / query performance risks**
- DrugBidAwardService uses `with('medicine')` in both list and export to avoid N+1: good. ([Modules/Pharma/Services/DrugBidAwardService.php](Modules/Pharma/Services/DrugBidAwardService.php))
- SupplierTrackingService uses `with('medicine')` in paginate and exportRows: good.
- MedicineService list queries do not eager-load relations (no relation used); fine.
- Livewire components sometimes fetch `Medicine::latest()->get()` in `DrugBidAward/Form.php` -> this loads all medicines into memory; potential heavy load for large datasets. File: [Modules/Pharma/Livewire/DrugBidAward/Form.php]. Priority: P1.
- `Index` components sometimes use `perPage === 'All' ? 999999 : (int)$perPage` — this may request all records and return large datasets to memory; used in `Medicine/Index` and `DrugBidAward/Index`. Files: [Modules/Pharma/Livewire/Medicine/Index.php], [Modules/Pharma/Livewire/DrugBidAward/Index.php]. Priority: P1.

---

**Duplicate logic**
- Two CSV import implementations for medicines:
  - [Modules/Pharma/Services/MedicineService.php::importFromCsv]()
  - [Modules/Pharma/Services/MedicineImportService.php::importFromCsv]()
  Both parse CSV, map columns and call `Medicine::updateOrCreate`. This duplication increases maintenance and bug risk. Priority: P1.

- Two import mechanisms for supplier-trackings:
  - `Livewire/SupplierTrackings/Index.php` uses `FastExcel` and `SupplierTrackingService::importRows()`
  - `Services/ImportExport.php` integrates with `Modules\\Shared\\Services\\ImportExport\\BaseImportExportService` and is used by shared import-export panel. Duplicate import code paths and slightly different validation/normalization. Priority: P1.

- Some monetary parsing logic repeated across `SupplierTrackingService::parseNumber`, `ImportExport::toDecimal`, and others — candidate to centralize. Priority: P2.

---

**Files that look unused / suspicious**
- [Modules/Pharma/Http/Controllers/Api/PharmaController.php](Modules/Pharma/Http/Controllers/Api/PharmaController.php) — empty stub. Priority: P2 (remove or implement).
- [Modules/Pharma/Models/Pharma.php](Modules/Pharma/Models/Pharma.php) — placeholder class with no usage. Priority: P2.
- [Modules/Pharma/Services/ImportExport.php] may duplicate shared service behavior but is intentionally an adapter; confirm reuse. Priority: P2.
- [Modules/Pharma/resources/views/components/placeholder.blade.php](Modules/Pharma/resources/views/components/placeholder.blade.php) and [Modules/Pharma/resources/views/livewire/placeholder.blade.php](Modules/Pharma/resources/views/livewire/placeholder.blade.php) — small utilities; confirm usage. Priority: P2.

---

**Refactor plan (with priorities)**
P0 Critical (must fix before broad refactor / before production use):
- P0-01: Add capability/permission checks to all admin routes and Livewire actions that mutate data. Files: [Modules/Pharma/routes/web.php], Livewire components in [Modules/Pharma/Livewire/**]. Priority: P0.
- P0-02: Remove or restrict CLI import command from production or require path whitelisting and RBAC for `medicine:import` ([Modules/Pharma/Console/Commands/ImportMedicineCommand.php]). Priority: P0.
- P0-03: Stop returning raw exception messages to users. Replace `session()->flash('error', $e->getMessage())` uses with sanitized messages and record full exception in logs. Files: e.g. [Modules/Pharma/Livewire/Medicine/Index.php], [Modules/Pharma/Livewire/DrugBidAward/Index.php], etc. Priority: P0.

P1 Important (improve correctness, performance, maintainability)
- P1-01: Consolidate import logic: unify `MedicineImportService` and `MedicineService::importFromCsv` into a single service used by both CLI and Livewire. Files: [Modules/Pharma/Services/MedicineImportService.php], [Modules/Pharma/Services/MedicineService.php]. Priority: P1.
- P1-02: Standardize supplier-tracking import path: choose either `FastExcel`+`SupplierTrackingService::importRows` or the shared `ImportExport` adapter and migrate code to single contract. Files: [Modules/Pharma/Services/ImportExport.php], [Modules/Pharma/Livewire/SupplierTrackings/Index.php], [Modules/Pharma/Services/SupplierTrackingService.php]. Priority: P1.
- P1-03: Wrap multi-row create/update operations in transactions where partial writes are unacceptable (e.g., `SupplierTrackingService::importRows()` and `create()`/`update()` flows). Files: [Modules/Pharma/Services/SupplierTrackingService.php]. Priority: P1.
- P1-04: Avoid large uncontrolled `perPage === 'All' ? 999999` patterns — implement chunked/queued exports and server-side caps. Files: [Modules/Pharma/Livewire/**/Index.php]. Priority: P1.
- P1-05: Replace `Model::latest()->get()` in form mounts with paginated or search-based select for medicines to prevent loading full table into memory. File: [Modules/Pharma/Livewire/DrugBidAward/Form.php]. Priority: P1.
- P1-06: Harden CSV parsing: verify headers, normalize encodings, and add strict column mapping with error reporting instead of silent skips. Files: [Modules/Pharma/Services/MedicineService.php], [Modules/Pharma/Services/MedicineImportService.php], [Modules/Pharma/Services/DrugBidAwardService.php]. Priority: P1.

P2 Nice to have (cleanup, developer experience)
- P2-01: Remove unused/placeholder files (`Modules/Pharma/Models/Pharma.php`, Api controller stub) or implement them if needed. Priority: P2.
- P2-02: Centralize numeric parsing and date parsing utilities used by SupplierTrackingService and ImportExport to `Modules/Shared` helper to reduce duplication. Files: [Modules/Pharma/Services/SupplierTrackingService.php], [Modules/Pharma/Services/ImportExport.php]. Priority: P2.
- P2-03: Add unit/integration tests for imports, exports, and composite unique constraints to prevent silent duplicate creation (migration+service tests). Files to add tests referencing migrations and services. Priority: P2.
- P2-04: Improve export cleanup: store exports in temp folder, use storage disks and cleanup older files. Files: [Modules/Pharma/Services/*::exportToCsv]. Priority: P2.

---

If you'd like I can next:
- produce a prioritized patch set implementing P0 fixes (authorization checks, safe error messages, CLI restrictions), or
- create a unit-test scaffold for import/export and the composite unique cases.

Which next step do you prefer?