# Pharma Module Analysis

Assessment date: 2026-06-18

Scope: `Modules/Pharma` only.

Mandatory context read:

- `ROADMAP.md`
- `docs/AI_PROJECT_CONTEXT.md`
- `docs/CODEX_BOOTSTRAP.md`

## 1. Module Purpose

`Modules/Pharma` is a domain module for managing pharmaceutical records and related commercial tracking:

- Medicine master records in `pharma_medicines`.
- Drug bid award records in `pharma_drug_bid_awards`.
- Supplier tracking, pricing, invoice difference, cost, profit, contract, and import/export records in `pharma_supplier_trackings`.

The module currently mixes legacy custom CSV/FastExcel import/export flows with the shared import/export foundation.

## 2. Route List

### Web Routes

File: `Modules/Pharma/routes/web.php`

All web routes are grouped under `admin/pharma`, route name prefix `admin.pharma.`, middleware `web` and `auth:admin`.

| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `admin/pharma/hssp` | `admin.pharma.hssp.index` | `Modules\Pharma\Http\Controllers\PharmaController@index` |
| GET | `admin/pharma/hssp/create` | `admin.pharma.hssp.create` | `Modules\Pharma\Http\Controllers\PharmaController@create` |
| GET | `admin/pharma/hssp/{id}/edit` | `admin.pharma.hssp.edit` | `Modules\Pharma\Http\Controllers\PharmaController@edit` |
| GET | `admin/pharma/drug-bid-awards` | `admin.pharma.drug-bid-awards.index` | `Modules\Pharma\Http\Controllers\DrugBidAwardController@index` |
| GET | `admin/pharma/drug-bid-awards/create` | `admin.pharma.drug-bid-awards.create` | `Modules\Pharma\Http\Controllers\DrugBidAwardController@create` |
| GET | `admin/pharma/drug-bid-awards/{id}/edit` | `admin.pharma.drug-bid-awards.edit` | `Modules\Pharma\Http\Controllers\DrugBidAwardController@edit` |
| GET | `admin/pharma/supplier-trackings` | `admin.pharma.supplier-trackings.index` | `Modules\Pharma\Http\Controllers\SupplierTrackingController@index` |
| GET | `admin/pharma/supplier-trackings/create` | `admin.pharma.supplier-trackings.create` | `Modules\Pharma\Http\Controllers\SupplierTrackingController@create` |
| GET | `admin/pharma/supplier-trackings/{id}/edit` | `admin.pharma.supplier-trackings.edit` | `Modules\Pharma\Http\Controllers\SupplierTrackingController@edit` |
| GET | `admin/pharma/supplier-trackings/import-export` | `admin.pharma.supplier-trackings.import-export` | `Modules\Pharma\Http\Controllers\SupplierTrackingController@importExport` |

Route problem:

- P0: `Modules/Pharma/routes/web.php` defines `supplier-trackings/import-export`, but `Modules/Pharma/Http/Controllers/SupplierTrackingController.php` has no `importExport()` method. This route will fail at runtime.

### API Routes

File: `Modules/Pharma/routes/api.php`

| Method | URI | Controller |
|---|---|---|
| GET | `pharma` | `Modules\Pharma\Http\Controllers\Api\PharmaController@index` |

Route problem:

- P0: `Modules/Pharma/routes/api.php` exposes `GET /pharma` without authentication, while the only controller class `Modules/Pharma/Http/Controllers/Api/PharmaController.php` has no `index()` method. It is both publicly exposed and broken.

## 3. Controllers

- `Modules/Pharma/Http/Controllers/PharmaController.php`
  - Thin page controller for medicine index/create/edit.
  - Has commented permission middleware in `__construct()`.
- `Modules/Pharma/Http/Controllers/DrugBidAwardController.php`
  - Thin page controller for bid award index/create/edit.
- `Modules/Pharma/Http/Controllers/SupplierTrackingController.php`
  - Thin page controller for supplier tracking index/create/edit/show.
  - Missing `importExport()` despite route.
- `Modules/Pharma/Http/Controllers/Api/PharmaController.php`
  - Empty scaffold controller.
  - Missing `index()` despite API route.

Controller issues:

- P0: `Modules/Pharma/Http/Controllers/PharmaController.php` comments out permission middleware, and no Pharma controller enforces capability-level authorization beyond `auth:admin`.
- P1: `Modules/Pharma/Http/Controllers/SupplierTrackingController.php` contains a `show()` method for a page view that exists, but no route points to it.
- P2: `Modules/Pharma/Http/Controllers/Api/PharmaController.php` is unused/broken scaffold code unless a real API endpoint is planned.

## 4. Page Blade Files

- `Modules/Pharma/resources/views/pages/index.blade.php`
  - Mounts `pharma.medicine.index`.
- `Modules/Pharma/resources/views/pages/create.blade.php`
  - Mounts `pharma.medicine.form`.
- `Modules/Pharma/resources/views/pages/edit.blade.php`
  - Mounts `pharma.medicine.form` with `id`.
- `Modules/Pharma/resources/views/pages/drug-bid-award/index.blade.php`
  - Mounts `pharma.drug-bid-award.index`.
- `Modules/Pharma/resources/views/pages/drug-bid-award/create.blade.php`
  - Mounts `pharma.drug-bid-award.form`.
- `Modules/Pharma/resources/views/pages/drug-bid-award/edit.blade.php`
  - Mounts `pharma.drug-bid-award.form` with `id`.
- `Modules/Pharma/resources/views/pages/supplier-trackings/index.blade.php`
  - Mounts `pharma.supplier-trackings.index`.
- `Modules/Pharma/resources/views/pages/supplier-trackings/create.blade.php`
  - Mounts `pharma.supplier-trackings.form`.
- `Modules/Pharma/resources/views/pages/supplier-trackings/edit.blade.php`
  - Mounts `pharma.supplier-trackings.form` with `id`.
- `Modules/Pharma/resources/views/pages/supplier-trackings/show.blade.php`
  - Empty shell with commented Livewire mount.
- `Modules/Pharma/resources/views/pharma.blade.php`
  - Scaffold placeholder page.

Page Blade issues:

- P1: `Modules/Pharma/resources/views/pages/*.blade.php` and nested page blades use `container-fluid`, which conflicts with the active Tailwind/Admin UI standard.
- P2: `Modules/Pharma/resources/views/pages/supplier-trackings/show.blade.php` appears unused and empty.
- P2: `Modules/Pharma/resources/views/pharma.blade.php` appears to be scaffold placeholder output, not linked by module routes.

## 5. Livewire PHP Classes

- `Modules/Pharma/Livewire/Medicine/Index.php`
  - Search, filters, manual pagination state, import CSV/XLSX upload, export CSV, single delete, bulk delete.
- `Modules/Pharma/Livewire/Medicine/Form.php`
  - Medicine create/edit form and validation.
- `Modules/Pharma/Livewire/DrugBidAward/Index.php`
  - Search, filters, pagination, import CSV/TXT, export CSV, single delete, bulk delete.
- `Modules/Pharma/Livewire/DrugBidAward/Form.php`
  - Drug bid award create/edit form and validation.
- `Modules/Pharma/Livewire/SupplierTrackings/Index.php`
  - Search, status filter, pagination, custom FastExcel import/export, shared import/export panel, single delete, bulk delete.
- `Modules/Pharma/Livewire/SupplierTrackings/Form.php`
  - Supplier tracking create/edit form, calculation preview, validation.

Livewire issues:

- P0: `Modules/Pharma/Livewire/*/Index.php` and form classes expose mutating actions such as create/update/delete/import/export without visible permission checks.
- P1: `Modules/Pharma/Livewire/DrugBidAward/Form.php` directly queries `Modules\Pharma\Models\Medicine` in `render()`, bypassing the service layer and loading all medicines.
- P1: `Modules/Pharma/Livewire/Medicine/Index.php` uses manual `$page` state instead of `WithPagination`, increasing pagination consistency risk.
- P1: `Modules/Pharma/Livewire/SupplierTrackings/Index.php` performs FastExcel import/export orchestration and filesystem directory creation inside Livewire instead of delegating to the shared import/export service.
- P1: `Modules/Pharma/Livewire/SupplierTrackings/Form.php` calls `app(SupplierTrackingService::class)` inside `recalculate()` instead of using a consistent injected service boundary.
- P1: `Modules/Pharma/Livewire/SupplierTrackings/Index.php` keeps formatting methods `money()` and `percent()` in Livewire; similar formatting exists in `Form.php`, creating duplicate UI logic.

## 6. Livewire Blade Views

- `Modules/Pharma/resources/views/livewire/medicine/index.blade.php`
- `Modules/Pharma/resources/views/livewire/medicine/form.blade.php`
- `Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php`
- `Modules/Pharma/resources/views/livewire/drug-bid-award/form.blade.php`
- `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php`
- `Modules/Pharma/resources/views/livewire/supplier-trackings/form.blade.php`
- `Modules/Pharma/resources/views/livewire/placeholder.blade.php`

Livewire Blade issues:

- P1: `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php` renders both custom import/export controls and `shared.import-export.panel`, so users can trigger two different import/export implementations for the same data.
- P1: `Modules/Pharma/resources/views/livewire/medicine/index.blade.php` and `Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php` expose `All` pagination that maps to `999999` records in Livewire/service code.
- P1: `Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php` formats dates with `date('d/m/Y', strtotime($award->decision_date))` even though the model casts `decision_date` to a date object.
- P1: `Modules/Pharma/resources/views/livewire/medicine/index.blade.php`, `drug-bid-award/index.blade.php`, and `supplier-trackings/index.blade.php` render external URLs directly as links; validation allows URLs but no allowlist or download/ownership checks exist.
- P2: `Modules/Pharma/resources/views/livewire/placeholder.blade.php` appears unused placeholder output.

## 7. Services and Public Methods

### `Modules/Pharma/Services/MedicineService.php`

Public methods:

- `getPaginatedMedicines(?string $search = null, int $perPage = 10, int $page = 1, ?string $circularGroup = null, ?string $specialControl = null): LengthAwarePaginator`
- `getUniqueCircularGroups(): array`
- `findOrFail(int $id): Medicine`
- `store(array $data): Medicine`
- `update(int $id, array $data): Medicine`
- `delete(int $id): bool`
- `importFromCsv(string $filePath): int`
- `exportToCsv(?string $search = null, ?string $circularGroup = null, ?string $specialControl = null): string`

### `Modules/Pharma/Services/MedicineImportService.php`

Public methods:

- `importFromCsv(string $filePath): int`

### `Modules/Pharma/Services/DrugBidAwardService.php`

Public methods:

- `getPaginated(?string $search = null, ?string $investor = null, ?string $company = null, int $perPage = 10): LengthAwarePaginator`
- `findOrFail(int $id)`
- `store(array $data)`
- `update(int $id, array $data)`
- `delete(int $id): bool`
- `getUniqueInvestors(): array`
- `getUniqueCompanies(): array`
- `importFromCsv(string $filePath): int`
- `exportToCsv(?string $search = null, ?string $investor = null, ?string $company = null): string`

### `Modules/Pharma/Services/SupplierTrackingService.php`

Public methods:

- `paginate(array $filters = [], int $perPage = 15)`
- `medicinesForSelect(): Collection`
- `find(int $id): SupplierTracking`
- `create(array $data): SupplierTracking`
- `update(int $id, array $data): SupplierTracking`
- `delete(int $id): void`
- `deleteMany(array $ids): void`
- `getFilteredIds(array $filters = []): Collection`
- `previewCalculate(array $data): array`
- `exportRows(array $filters = []): Collection`
- `importRows(Collection $rows): array`

### `Modules/Pharma/Services/ImportExport.php`

Public methods:

- `rules(): array`
- `modelClass(): string`
- `columnMapping(): array`
- `normalizeRow(array $row): array`
- `exportRows(array $filters = []): Collection`
- `mapExportRow($row): array`
- `templateSampleRow(): array`

Service issues:

- P1: `Modules/Pharma/Services/MedicineService.php` and `Modules/Pharma/Services/MedicineImportService.php` duplicate medicine CSV import behavior with different unique keys.
- P1: `Modules/Pharma/Services/SupplierTrackingService.php` and `Modules/Pharma/Services/ImportExport.php` duplicate supplier tracking import/export logic with different status vocabularies and report shapes.
- P1: `Modules/Pharma/Services/SupplierTrackingService.php` create/update/delete/deleteMany/importRows are not wrapped in transactions.
- P1: `Modules/Pharma/Services/SupplierTrackingService.php` importRows performs one medicine query per imported row, creating import-time N+1 behavior.
- P1: `Modules/Pharma/Services/SupplierTrackingService.php` exportRows calls `get()` and maps the full filtered dataset in memory.
- P1: `Modules/Pharma/Services/ImportExport.php` exportRows ignores `$filters` and loads all supplier tracking records.
- P1: `Modules/Pharma/Services/MedicineService.php` importFromCsv validates XLSX uploads at the Livewire layer but reads the file with `fgetcsv()`, so XLSX files are accepted but not correctly parsed.
- P1: `Modules/Pharma/Services/DrugBidAwardService.php` importFromCsv requires semicolon-delimited CSV while the UI copy does not clearly enforce delimiter expectations.
- P1: `Modules/Pharma/Services/MedicineService.php`, `DrugBidAwardService.php`, and `MedicineImportService.php` use ad hoc CSV parsing instead of the shared import/export foundation required by the active standard.

## 8. Models and Database Tables

### Models

- `Modules/Pharma/Models/Medicine.php`
  - Table: `pharma_medicines`
  - Uses `$guarded = ['id']`.
  - Casts dates, boolean, and declared price.
- `Modules/Pharma/Models/DrugBidAward.php`
  - Table: `pharma_drug_bid_awards`
  - Fillable fields defined.
  - Belongs to `Medicine`.
- `Modules/Pharma/Models/SupplierTracking.php`
  - Table: `pharma_supplier_trackings`
  - Fillable fields defined.
  - Belongs to `Medicine`.
  - Has public `$exceptExport`.
- `Modules/Pharma/Models/Pharma.php`
  - Empty scaffold model.

Model issues:

- P1: `Modules/Pharma/Models/Medicine.php` uses broad `$guarded` instead of explicit `$fillable`, weakening import/export defaults and mass-assignment clarity.
- P1: `Modules/Pharma/Models/SupplierTracking.php` declares `public array $exceptExport`; active export conventions prefer a safe accessor or clear model-level export exclusion contract.
- P2: `Modules/Pharma/Models/Pharma.php` appears unused scaffold code.

### Database Tables and Migrations

- `Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php`
  - Creates `pharma_medicines`.
  - Unique key: `registration_number + packaging_specification`.
- `Modules/Pharma/database/migrations/2026_05_22_135028_create_drug_bid_awards_table.php`
  - Creates `pharma_drug_bid_awards`.
  - Nullable FK `medicine_id` to `pharma_medicines`, `onDelete set null`.
  - Unique key: `bidding_notice_code + medicine_name + winning_company_name`.
  - Indexes `investor_name`, `winning_company_name`.
- `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php`
  - Creates `pharma_supplier_trackings`.
  - Required FK `medicine_id` to `pharma_medicines`, cascade delete.
  - Indexes `medicine_id + supplier_name`, `status`.

Migration issues:

- P1: `Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php` has no indexes for search/filter fields used by services such as `name`, `active_ingredients`, `circular_group`, and `is_special_control`.
- P1: `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php` has no unique key matching `Modules/Pharma/Services/ImportExport.php` uniqueBy fields `medicine_id`, `supplier_name`, `working_date`.
- P1: `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php` uses `cascadeOnDelete()` for medicine deletion, which can destroy supplier tracking history when a medicine is deleted.
- P1: `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php` stores `status` as a free string with no DB constraint and inconsistent application vocabularies.
- P2: `Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php` has sparse column comments compared with later migrations.

## 9. Import/Export Classes

- `Modules/Pharma/Services/MedicineService.php`
  - Custom CSV import/export for medicines.
- `Modules/Pharma/Services/MedicineImportService.php`
  - Separate CSV import service for medicine console import.
- `Modules/Pharma/Services/DrugBidAwardService.php`
  - Custom CSV import/export for drug bid awards.
- `Modules/Pharma/Services/SupplierTrackingService.php`
  - Custom FastExcel import/export for supplier tracking.
- `Modules/Pharma/Services/ImportExport.php`
  - Shared-foundation implementation for supplier tracking.
- `Modules/Pharma/Console/Commands/ImportMedicineCommand.php`
  - Artisan command `medicine:import {file}` that delegates to `MedicineImportService`.

Import/export issues:

- P1: `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php` presents both the custom supplier import/export and shared import/export panel on the same screen.
- P1: `Modules/Pharma/Services/ImportExport.php` accepts status values `active`, `inactive`, `draft`, `expired`, while `Modules/Pharma/Livewire/SupplierTrackings/Index.php` and `Form.php` use `active`, `completed`, `paused`, `cancelled`.
- P1: `Modules/Pharma/Services/ImportExport.php` maps columns J, L, M, and N to calculated fields, then recalculates them. This is mostly safe, but the template/mapping can confuse users into thinking calculated spreadsheet values are imported.
- P1: `Modules/Pharma/Services/MedicineService.php` and `Modules/Pharma/Services/DrugBidAwardService.php` return raw exception messages to Livewire flash messages during import failures.
- P1: `Modules/Pharma/Console/Commands/ImportMedicineCommand.php` accepts any filesystem path available to the process; appropriate for CLI, but it should be documented as an operator-only command and not wired to web execution.

## 10. Authorization/Security Risks

- P0: `Modules/Pharma/routes/api.php` exposes an unauthenticated API route.
- P0: `Modules/Pharma/routes/web.php` only uses `auth:admin`; no named permissions are enforced for create, edit, delete, bulk delete, import, or export.
- P0: `Modules/Pharma/Livewire/Medicine/Index.php`, `DrugBidAward/Index.php`, and `SupplierTrackings/Index.php` trust client-selected IDs for deletes and bulk deletes without server-side authorization checks.
- P0: `Modules/Pharma/Livewire/*/Form.php` update paths accept route-provided IDs and rely on service `findOrFail()` only, with no record-level permission or ownership check.
- P1: `Modules/Pharma/Livewire/Medicine/Index.php` and `DrugBidAward/Index.php` flash raw exception messages on import/save errors, which can leak internal details.
- P1: `Modules/Pharma/resources/views/livewire/*` opens user-provided document URLs in new tabs without `rel="noopener noreferrer"` and without URL allowlist policy.

## 11. Validation Problems

- P1: `Modules/Pharma/Livewire/Medicine/Form.php` validates `registration_number` but does not enforce uniqueness with ignore-current-record logic matching the database unique key.
- P1: `Modules/Pharma/Livewire/DrugBidAward/Form.php` does not validate uniqueness for `bidding_notice_code + medicine_name + winning_company_name`, so DB exceptions can surface during save.
- P1: `Modules/Pharma/Livewire/SupplierTrackings/Form.php` validates `status` as a string only, not as an allowed enum set.
- P1: `Modules/Pharma/Services/SupplierTrackingService.php` importRows does not validate normalized rows with Laravel validator rules before create.
- P1: `Modules/Pharma/Services/MedicineService.php` importFromCsv uses positional indexes without count checks before accessing indexes up to `20`.
- P1: `Modules/Pharma/Services/DrugBidAwardService.php` importFromCsv only checks `count($data) < 12`, then parses date with a single format that can throw and roll back the whole file.
- P1: `Modules/Pharma/Livewire/SupplierTrackings/Index.php` upload validation has no explicit max file size.
- P2: `Modules/Pharma/Livewire/SupplierTrackings/Form.php` validates `contract_url` as string instead of URL, despite rendering it as an external link.

## 12. Transaction Risks

- P1: `Modules/Pharma/Services/SupplierTrackingService.php` create, update, delete, deleteMany, and importRows are not transactional.
- P1: `Modules/Pharma/Livewire/Medicine/Index.php` and `DrugBidAward/Index.php` bulk delete loops call delete one row at a time; a mid-loop failure can leave partial deletion.
- P1: `Modules/Pharma/Services/SupplierTrackingService.php` importRows creates rows one by one and continues on errors, but the partial-import behavior is not documented or surfaced with the shared report format.
- P1: `Modules/Pharma/Services/MedicineService.php`, `DrugBidAwardService.php`, and `MedicineImportService.php` manually begin/commit/rollback transactions around file handles; `DB::transaction()` plus `finally` file close would reduce resource-leak risk.

## 13. N+1/Query Performance Risks

- P1: `Modules/Pharma/Livewire/DrugBidAward/Form.php` loads all medicines with `Medicine::query()->latest()->get()` every render.
- P1: `Modules/Pharma/Services/SupplierTrackingService.php` `medicinesForSelect()` loads all medicine options; this will become heavy for large medicine catalogs.
- P1: `Modules/Pharma/Services/SupplierTrackingService.php` importRows queries Medicine per row by registration number.
- P1: `Modules/Pharma/Services/SupplierTrackingService.php` exportRows loads all matching rows into memory with `get()`.
- P1: `Modules/Pharma/Services/ImportExport.php` exportRows loads all supplier tracking records and ignores filters.
- P1: `Modules/Pharma/Livewire/Medicine/Index.php` and `DrugBidAward/Index.php` use `999999` as a substitute for `All`, which is unbounded for practical memory and response time.
- P1: `Modules/Pharma/Services/DrugBidAwardService.php` and `MedicineService.php` use `like '%term%'` on unindexed text columns.

## 14. Duplicate Logic

- P1: Medicine import is duplicated in `Modules/Pharma/Services/MedicineService.php` and `Modules/Pharma/Services/MedicineImportService.php`.
- P1: Supplier tracking import/export is duplicated in `Modules/Pharma/Services/SupplierTrackingService.php`, `Modules/Pharma/Services/ImportExport.php`, and `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php`.
- P1: Money and percent formatting are duplicated in `Modules/Pharma/Livewire/SupplierTrackings/Index.php` and `Modules/Pharma/Livewire/SupplierTrackings/Form.php`.
- P1: Supplier tracking search/filter query logic is duplicated across `paginate()`, `getFilteredIds()`, and `exportRows()` in `Modules/Pharma/Services/SupplierTrackingService.php`.
- P1: Medicine and drug bid award list pages duplicate import/upload/table/bulk-delete patterns instead of sharing module or shared components.

## 15. Files That Look Unused

- P2: `Modules/Pharma/Models/Pharma.php` is an empty scaffold model and appears unused.
- P2: `Modules/Pharma/resources/views/pharma.blade.php` is a scaffold placeholder and appears unused by routes.
- P2: `Modules/Pharma/resources/views/components/placeholder.blade.php` appears only used by the unused scaffold page.
- P2: `Modules/Pharma/resources/views/livewire/placeholder.blade.php` appears unused.
- P2: `Modules/Pharma/resources/views/pages/supplier-trackings/show.blade.php` has no route and no mounted component.
- P2: `Modules/Pharma/readme.md` contains scaffold command notes, not module documentation.
- P1: `Modules/Pharma/routes/web.php` references `SupplierTrackingController@importExport`, but the method and likely page are missing.
- P1: `Modules/Pharma/routes/api.php` references `Api\PharmaController@index`, but the method is missing.

## 16. Refactor Plan

### P0 Critical

- P0: Remove, protect, or implement the unauthenticated broken API route in `Modules/Pharma/routes/api.php` and `Modules/Pharma/Http/Controllers/Api/PharmaController.php`.
- P0: Add named permission checks for all Pharma admin pages and Livewire mutating actions in `Modules/Pharma/routes/web.php`, `Modules/Pharma/Http/Controllers/*.php`, and `Modules/Pharma/Livewire/**/*.php`.
- P0: Fix the broken `admin.pharma.supplier-trackings.import-export` route by adding a real controller/page flow or removing the route from `Modules/Pharma/routes/web.php`.
- P0: Add server-side authorization checks for single and bulk delete IDs in `Modules/Pharma/Livewire/Medicine/Index.php`, `Modules/Pharma/Livewire/DrugBidAward/Index.php`, and `Modules/Pharma/Livewire/SupplierTrackings/Index.php`.

### P1 Important

- P1: Consolidate import/export onto the shared `Modules/Shared/Services/ImportExport` architecture, starting with supplier tracking and then medicines and drug bid awards.
- P1: Remove duplicate supplier tracking import/export controls from `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php` after the shared panel behavior is confirmed.
- P1: Replace direct model queries in `Modules/Pharma/Livewire/DrugBidAward/Form.php` with service methods and searchable/bounded medicine selection.
- P1: Add transaction boundaries to `Modules/Pharma/Services/SupplierTrackingService.php` create/update/delete/deleteMany/importRows.
- P1: Add bulk-delete service methods for medicine and bid award records so Livewire does not loop row by row.
- P1: Align supplier tracking status values across `Modules/Pharma/Services/ImportExport.php`, `Modules/Pharma/Livewire/SupplierTrackings/*.php`, and `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php`.
- P1: Replace `All => 999999` behavior in `Modules/Pharma/Livewire/Medicine/Index.php` and `Modules/Pharma/Livewire/DrugBidAward/Index.php` with capped or guarded behavior.
- P1: Add validation for database unique keys in `Modules/Pharma/Livewire/Medicine/Form.php` and `Modules/Pharma/Livewire/DrugBidAward/Form.php`.
- P1: Add explicit `$fillable` to `Modules/Pharma/Models/Medicine.php`.
- P1: Review cascade delete in `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php` and decide whether supplier history should be retained when medicine records are deleted.
- P1: Add indexes that match actual searches and filters in `Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php`.
- P1: Normalize safe error handling so Livewire does not flash raw exception messages from import/save failures.

### P2 Nice To Have

- P2: Remove confirmed scaffold placeholders: `Modules/Pharma/Models/Pharma.php`, `Modules/Pharma/resources/views/pharma.blade.php`, `Modules/Pharma/resources/views/components/placeholder.blade.php`, and `Modules/Pharma/resources/views/livewire/placeholder.blade.php`.
- P2: Replace page blade `container-fluid` wrappers with the active Tailwind Admin UI page container.
- P2: Convert manual inline SVGs to shared/icon components where the project standard supports it.
- P2: Add real module documentation to replace scaffold notes in `Modules/Pharma/readme.md`.
- P2: Extract shared formatting helpers for money and percent display in supplier tracking views.
- P2: Add `rel="noopener noreferrer"` to external document links in Pharma Livewire blades.
