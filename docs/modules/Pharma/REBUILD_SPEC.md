# Pharma Rebuild Specification

Assessment date: 2026-06-18

Scope: `Modules/Pharma` only. This specification is for rebuilding/refactoring the Pharma module. The prompt mentions Category, but the requested module name and source documents are Pharma.

Source references:

- `docs/modules/Pharma/ANALYSIS.md`
- `docs/modules/Pharma/REFACTOR_PLAN.md`
- `docs/AI_PROJECT_CONTEXT.md`
- `docs/CODEX_BOOTSTRAP.md`
- `ROADMAP.md`

## 1. Goal

The rebuilt/refactored Pharma module must provide a secure, consistent, Laravel 12 and Livewire 3 admin workflow for:

- Medicine master records in `pharma_medicines`.
- Drug bid award records in `pharma_drug_bid_awards`.
- Supplier tracking records in `pharma_supplier_trackings`.
- Import/export through the shared import/export foundation.

Design decisions:

- Keep Pharma as the canonical domain owner for medicine, bid award, and supplier tracking behavior. Reference: `ANALYSIS.md` section 1; `REFACTOR_PLAN.md` Executive Summary.
- Enforce named authorization beyond `auth:admin` on every page and mutating Livewire action. Reference: `ANALYSIS.md` sections 10 and 16 P0; `REFACTOR_PLAN.md` P0-02 and P0-04.
- Remove broken/public route surfaces before feature refactors. Reference: `ANALYSIS.md` sections 2 and 15; `REFACTOR_PLAN.md` P0-01 and P0-03.
- Consolidate duplicated import/export behavior onto `Modules/Shared/Services/ImportExport`. Reference: `ANALYSIS.md` sections 9 and 14; `REFACTOR_PLAN.md` P1-01 and P1-02.
- Keep business rules, queries, transactions, imports, exports, and derived calculations in services/import-export classes, not in controllers, blades, or Livewire. Reference: `ANALYSIS.md` sections 5, 7, 12, and 13; `REFACTOR_PLAN.md` P1-04, P1-05, P1-06, P1-10, and P1-14.

## 2. Target Architecture

Target flow:

```text
Route
→ Controller
→ Page Blade
→ Livewire PHP
→ Livewire Blade
→ Shared Components
→ Service
→ Import
→ Export
→ Model
→ Migration
```

Layer design:

- Route: `Modules/Pharma/routes/web.php` must define only URL, route name, middleware, and controller action. It must not point to missing methods. Reference: `ANALYSIS.md` sections 2 and 15; `REFACTOR_PLAN.md` P0-03.
- API route: `Modules/Pharma/routes/api.php` must be removed or protected and implemented. Needs confirmation before coding: whether Pharma needs an API. Reference: `ANALYSIS.md` section 2; `REFACTOR_PLAN.md` P0-01.
- Controller: `Modules/Pharma/Http/Controllers/*Controller.php` must remain thin and only return page blades or pass scalar IDs. Reference: `ANALYSIS.md` section 3; `REFACTOR_PLAN.md` P0-02.
- Page Blade: `Modules/Pharma/resources/views/pages/**/*.blade.php` must extend `Admin::layouts.master`, use the active Tailwind admin page container, and mount Livewire only. Reference: `ANALYSIS.md` section 4; `REFACTOR_PLAN.md` P1-13.
- Livewire PHP: components under `Modules/Pharma/Livewire/**` own UI state, validation, events, pagination, and service calls only. Reference: `ANALYSIS.md` section 5; `REFACTOR_PLAN.md` P1-04 and P1-05.
- Livewire Blade: views under `Modules/Pharma/resources/views/livewire/**` render forms/tables/actions and must not query models or perform business logic. Reference: `ANALYSIS.md` section 6; `REFACTOR_PLAN.md` P1-12 and P2-03.
- Shared Components: use `x-select-search` for large medicine selectors and `shared.import-export.panel` for import/export. Reference: `ANALYSIS.md` sections 6 and 9; `REFACTOR_PLAN.md` P1-01, P1-02, and P1-04.
- Service: `Modules/Pharma/Services/*Service.php` own queries, validation invariants, transactions, derived calculations, pagination, search/filter/sort, and bulk operations. Reference: `ANALYSIS.md` sections 7, 12, and 13; `REFACTOR_PLAN.md` P1-06, P1-10, and P1-14.
- Import: use `Modules/Pharma/Services/ImportExport.php` as the shared entry point and add `Modules/Pharma/Import/*` only when medicine/bid/supplier mapping responsibilities grow beyond a simple service. Reference: `ANALYSIS.md` section 9; `REFACTOR_PLAN.md` P1-01 and P1-08.
- Export: use shared storage/reporting and add `Modules/Pharma/Export/*` only when query/mapping/template responsibilities need separation. Reference: `ANALYSIS.md` sections 9 and 13; `REFACTOR_PLAN.md` P1-01 and P1-10.
- Model: models under `Modules/Pharma/Models` define table, fillable, casts, relationships, and export exclusions only. Reference: `ANALYSIS.md` section 8; `REFACTOR_PLAN.md` P1-11.
- Migration: migrations under `Modules/Pharma/database/migrations` define schema, indexes, foreign keys, constraints, and comments. Reference: `ANALYSIS.md` section 8; `REFACTOR_PLAN.md` P1-11 and P2-05.

## 3. Database Design

### Tables

#### `pharma_medicines`

Current columns to preserve:

- `id`
- `circular_order_number`
- `circular_group`
- `active_ingredients`
- `concentration`
- `name`
- `dosage_form`
- `route_of_administration`
- `unit`
- `packaging_specification`
- `registration_number`
- `shelf_life`
- `registered_company`
- `manufacturing_company`
- `manufacturing_country`
- `visa_validity_date`
- `gmp_certification_date`
- `declared_price`
- `is_special_control`
- `profile_link`
- `notes`
- `created_at`
- `updated_at`

Design decisions:

- Keep the existing unique business key `registration_number + packaging_specification`. Reference: `ANALYSIS.md` section 8; `REFACTOR_PLAN.md` P1-07.
- Add or confirm indexes for `name`, `active_ingredients`, `circular_group`, and `is_special_control` based on real search/filter patterns. Reference: `ANALYSIS.md` sections 8 and 13; `REFACTOR_PLAN.md` P1-10 and P1-11.
- Add meaningful comments to important medicine columns. Reference: `ANALYSIS.md` section 8; `REFACTOR_PLAN.md` P2-05.
- Needs confirmation before coding: edit existing migration only if not deployed; otherwise create a forward-only migration. Reference: `REFACTOR_PLAN.md` Risk Control.

#### `pharma_drug_bid_awards`

Current columns to preserve:

- `id`
- `medicine_id`
- `medicine_name`
- `packaging_specification`
- `quantity`
- `unit_price`
- `bidding_notice_code`
- `investor_name`
- `decision_number`
- `decision_date`
- `contract_duration_months`
- `winning_company_name`
- `decision_document_url`
- `created_at`
- `updated_at`

Design decisions:

- Keep nullable `medicine_id` foreign key to `pharma_medicines` with `onDelete set null` unless business rules say bid award history should be deleted with medicines. Reference: `ANALYSIS.md` section 8; `REFACTOR_PLAN.md` P1-11.
- Keep unique key `bidding_notice_code + medicine_name + winning_company_name` and validate it before save/import. Reference: `ANALYSIS.md` sections 8 and 11; `REFACTOR_PLAN.md` P1-07.
- Keep indexes on `investor_name` and `winning_company_name`; review whether `bidding_notice_code`, `medicine_name`, and `decision_date` need additional indexes based on filters. Reference: `ANALYSIS.md` sections 8 and 13; `REFACTOR_PLAN.md` P1-10 and P1-11.

#### `pharma_supplier_trackings`

Current columns to preserve:

- `id`
- `medicine_id`
- `working_date`
- `supplier_name`
- `supplier_representative`
- `area`
- `import_price`
- `selling_price`
- `invoice_price`
- `invoice_difference_amount`
- `invoice_difference_percent`
- `invoice_difference_fee`
- `cost_price`
- `gross_profit_percent`
- `committed_quantity`
- `unit`
- `deposit_amount`
- `start_date`
- `end_date`
- `contract_url`
- `status`
- `note`
- `created_at`
- `updated_at`

Design decisions:

- Keep formula fields persisted and recalculate them in `SupplierTrackingService`; imports must not trust spreadsheet formula values. Reference: `ANALYSIS.md` sections 7 and 9; `REFACTOR_PLAN.md` P1-01 and P1-08.
- Align status values across UI, services, import, export, and schema comments/constraints. Needs confirmation before coding: final allowed statuses. Reference: `ANALYSIS.md` sections 9 and 11; `REFACTOR_PLAN.md` P1-03.
- Add or confirm unique key for `medicine_id + supplier_name + working_date` if `Modules/Pharma/Services/ImportExport.php` keeps that `uniqueBy`. Needs confirmation before coding: business uniqueness of supplier tracking. Reference: `ANALYSIS.md` section 8; `REFACTOR_PLAN.md` P1-11 and Risk Control.
- Review `cascadeOnDelete()` on `medicine_id`. Needs confirmation before coding: whether supplier history must survive medicine deletion. Reference: `ANALYSIS.md` section 8; `REFACTOR_PLAN.md` P1-11 and Risk Control.
- Keep indexes on `medicine_id + supplier_name` and `status`; add indexes only for confirmed query patterns. Reference: `ANALYSIS.md` sections 8 and 13; `REFACTOR_PLAN.md` P1-10 and P1-11.

### Migration Notes

- Prefer forward-only migrations if current migrations have been applied. Reference: `REFACTOR_PLAN.md` Risk Control.
- Add migration smoke tests for constraints, foreign keys, decimals, and fresh install. Reference: `ROADMAP.md` P1-08 and P1-10; `REFACTOR_PLAN.md` P1-11.
- Do not introduce destructive migration changes until production data impact is confirmed. Reference: `REFACTOR_PLAN.md` Risk Control.

## 4. Model Design

### `Modules\Pharma\Models\Medicine`

Design:

- Table: `pharma_medicines`.
- Fillable fields: all business columns except `id`, `created_at`, and `updated_at`.
- Casts: `visa_validity_date` date, `gmp_certification_date` date, `is_special_control` boolean, `declared_price` decimal:2.
- Relationships: `drugBidAwards()` has many `DrugBidAward`; `supplierTrackings()` has many `SupplierTracking`.
- Scopes: optional `search($term)`, `circularGroup($group)`, `specialControl($value)` only if service code benefits from readable reuse.
- Accessors/mutators: none required initially; keep money/date formatting out of the model.

References: `ANALYSIS.md` section 8; `REFACTOR_PLAN.md` P1-04, P1-10, and P1-11.

### `Modules\Pharma\Models\DrugBidAward`

Design:

- Table: `pharma_drug_bid_awards`.
- Fillable fields: existing fields in the current model.
- Casts: `medicine_id` integer, `quantity` integer, `unit_price` decimal:2, `decision_date` date, `contract_duration_months` integer.
- Relationships: `medicine()` belongs to `Medicine`.
- Scopes: optional `search($term)`, `investor($value)`, `winningCompany($value)` if reused by service query builder.
- Accessors/mutators: none required; date formatting belongs in Blade/helpers.

References: `ANALYSIS.md` sections 6 and 8; `REFACTOR_PLAN.md` P1-07, P1-10, P1-11, and P1-12.

### `Modules\Pharma\Models\SupplierTracking`

Design:

- Table: `pharma_supplier_trackings`.
- Fillable fields: existing business fields.
- Casts: date fields to date; money/percent/quantity fields to decimal:2.
- Relationships: `medicine()` belongs to `Medicine`.
- Export exclusions: replace public `$exceptExport` with a safe accessor such as `getExceptExport()` or the shared foundation's expected export-exclusion contract.
- Scopes: optional `search($term)`, `status($status)`, `forMedicine($medicineId)` if reused by service query builder.
- Accessors/mutators: avoid formatting accessors for money; presentation formatting belongs in Blade/shared helper.

References: `ANALYSIS.md` section 8; `REFACTOR_PLAN.md` P1-03, P1-11, and P1-14.

### Scaffold Model

- Remove `Modules/Pharma/Models/Pharma.php` only after tests prove it is unused. Reference: `ANALYSIS.md` section 15; `REFACTOR_PLAN.md` P2-01.

## 5. Service Design

### `Modules\Pharma\Services\MedicineService`

Public methods to target:

- `paginate(array $filters, int|string $perPage): LengthAwarePaginator`
- `findOrFail(int $id): Medicine`
- `create(array $data): Medicine`
- `update(int $id, array $data): Medicine`
- `delete(int $id): bool`
- `deleteMany(array $ids): int`
- `options(array $filters = [], int $limit = 50): Collection`
- `validateBusinessRules(array $data, ?int $ignoreId = null): array`

Responsibilities:

- Medicine search/filter/pagination.
- Composite unique validation for `registration_number + packaging_specification`.
- Bounded medicine options for selectors.
- Transactions for create/update/delete/bulk delete.
- No ad hoc import/export once shared import/export is confirmed.

References: `ANALYSIS.md` sections 7, 11, 12, 13, and 14; `REFACTOR_PLAN.md` P1-06, P1-07, P1-08, P1-10, and P1-11.

### `Modules\Pharma\Services\DrugBidAwardService`

Public methods to target:

- `paginate(array $filters, int|string $perPage): LengthAwarePaginator`
- `findOrFail(int $id): DrugBidAward`
- `create(array $data): DrugBidAward`
- `update(int $id, array $data): DrugBidAward`
- `delete(int $id): bool`
- `deleteMany(array $ids): int`
- `filterOptions(): array`
- `validateBusinessRules(array $data, ?int $ignoreId = null): array`

Responsibilities:

- Bid award search/filter/pagination with `medicine` eager loading.
- Composite unique validation for `bidding_notice_code + medicine_name + winning_company_name`.
- Transactions for create/update/delete/bulk delete.
- No direct model query from Livewire.

References: `ANALYSIS.md` sections 5, 7, 11, 12, and 13; `REFACTOR_PLAN.md` P1-04, P1-06, P1-07, and P1-10.

### `Modules\Pharma\Services\SupplierTrackingService`

Public methods to target:

- `paginate(array $filters, int|string $perPage): LengthAwarePaginator`
- `findOrFail(int $id): SupplierTracking`
- `create(array $data): SupplierTracking`
- `update(int $id, array $data): SupplierTracking`
- `delete(int $id): void`
- `deleteMany(array $ids): int`
- `previewCalculate(array $data): array`
- `calculate(array $data): array`
- `queryForFilters(array $filters): Builder`
- `statuses(): array`
- `medicineOptions(array $filters = [], int $limit = 50): Collection`

Responsibilities:

- Supplier tracking search/filter/pagination.
- Formula calculation for invoice difference, fee, cost price, and gross profit.
- Status vocabulary.
- Bounded medicine selector options.
- Transactions for create/update/delete/bulk delete.

References: `ANALYSIS.md` sections 7, 11, 12, 13, and 14; `REFACTOR_PLAN.md` P1-03, P1-05, P1-06, P1-10, and P1-14.

### `Modules\Pharma\Services\ImportExport`

Public methods to target:

- `modelClass(): string`
- `rules(): array`
- `columnMapping(): array`
- `headerAliases(): array` if header-based mappings are confirmed.
- `normalizeRow(array $row): array`
- `exportRows(array $filters = [])`
- `mapExportRow($row): array`
- `templateSampleRow(): array`

Responsibilities:

- Supplier tracking shared import/export entry point first.
- Use shared report, storage, normalization, mapping, and dry-run behavior.
- Delegate to `SupplierTrackingService` for formula calculations and persistence rules.
- Split into `Modules/Pharma/Import/*` or `Modules/Pharma/Export/*` when the class becomes too large.

References: `ANALYSIS.md` sections 7, 9, 13, and 14; `REFACTOR_PLAN.md` P1-01, P1-02, P1-08, and P1-10.

Transaction boundaries:

- Create/update/delete/bulk delete must be transactional. Reference: `ANALYSIS.md` section 12; `REFACTOR_PLAN.md` P1-06.
- Import persistence must be transactional according to confirmed all-or-nothing or partial success rules. Needs confirmation before coding. Reference: `ANALYSIS.md` section 12; `REFACTOR_PLAN.md` P1-08 and Risk Control.
- Export should not use DB transactions; it must use bounded reads. Reference: `ANALYSIS.md` section 13; `REFACTOR_PLAN.md` P1-10.

## 6. Livewire Design

### Component List

- `Modules/Pharma/Livewire/Medicine/Index.php`
- `Modules/Pharma/Livewire/Medicine/Form.php`
- `Modules/Pharma/Livewire/DrugBidAward/Index.php`
- `Modules/Pharma/Livewire/DrugBidAward/Form.php`
- `Modules/Pharma/Livewire/SupplierTrackings/Index.php`
- `Modules/Pharma/Livewire/SupplierTrackings/Form.php`
- Optional `Modules/Pharma/Livewire/SupplierTrackings/Show.php`: Needs confirmation before coding. Reference: `ANALYSIS.md` sections 3 and 15; `REFACTOR_PLAN.md` P2-02.

### State Properties

Medicine index:

- `search`, `filterCircularGroup`, `filterSpecialControl`, `perPage`, `selectedIds`, `selectAll`.
- Use `WithPagination`; remove manual `$page`. Reference: `ANALYSIS.md` section 5; `REFACTOR_PLAN.md` P1-05.

Medicine form:

- `medicineId`, `isEditMode`, fields matching `Medicine::$fillable`.
- Use scalar `?int $id` mount. Reference: `ANALYSIS.md` section 5; `REFACTOR_PLAN.md` P1-07.

Drug bid award index:

- `search`, `filterInvestor`, `filterCompany`, `perPage`, `selectedIds`, `selectAll`.
- Use `WithPagination`. Reference: `ANALYSIS.md` section 5; `REFACTOR_PLAN.md` P1-05.

Drug bid award form:

- `awardId`, `isEditMode`, bid fields, `medicine_id`.
- Medicine selector options must come from a service, not `Medicine::query()`. Reference: `ANALYSIS.md` section 5; `REFACTOR_PLAN.md` P1-04.

Supplier tracking index:

- `search`, `status`, `perPage`, `selected`, `selectAll`.
- Remove custom import/export state once shared panel is canonical. Reference: `ANALYSIS.md` sections 5 and 6; `REFACTOR_PLAN.md` P1-02.

Supplier tracking form:

- `trackingId`, `medicine_id`, `form` array.
- Inject service consistently; do not call raw `app()` for repeated actions. Reference: `ANALYSIS.md` section 5; `REFACTOR_PLAN.md` P1-05.

### Validation Rules

- Medicine: required core fields; URL for `profile_link`; composite unique validation for `registration_number + packaging_specification` ignoring current record. Reference: `ANALYSIS.md` section 11; `REFACTOR_PLAN.md` P1-07.
- Drug bid award: required fields; URL for `decision_document_url`; composite unique validation for `bidding_notice_code + medicine_name + winning_company_name` ignoring current record. Reference: `ANALYSIS.md` section 11; `REFACTOR_PLAN.md` P1-07.
- Supplier tracking: required `medicine_id` and `supplier_name`; numeric money/percent fields; URL validation for `contract_url`; status in confirmed allowed list. Needs confirmation before coding: final status list. Reference: `ANALYSIS.md` section 11; `REFACTOR_PLAN.md` P1-03 and P1-07.
- Upload validation belongs in shared panel or Livewire upload state with explicit file type and max size. Reference: `ANALYSIS.md` section 11; `REFACTOR_PLAN.md` P1-07 and P1-08.

### Events

- Use Livewire events only for UI-level refresh/reset feedback.
- Do not use events to bypass service calls or authorization.
- `filters-reset` may remain if needed by existing UI, but it should not carry business data. Reference: `ANALYSIS.md` section 5; `REFACTOR_PLAN.md` P1-05.

### Pagination

- Use server-side pagination in services.
- Supported options: `10`, `25`, `50`, `100`, and guarded `All`.
- `All` must be capped, disabled, or warned when dataset size is unsafe. Reference: `ANALYSIS.md` sections 6 and 13; `REFACTOR_PLAN.md` P1-10.

### Search/Filter/Sort Behavior

- Medicine search: name and active ingredients; filters for circular group and special control. Reference: `ANALYSIS.md` sections 7 and 13; `REFACTOR_PLAN.md` P1-10 and P1-11.
- Bid award search: medicine name, bidding notice code, decision number; filters for investor and winning company. Reference: `ANALYSIS.md` sections 7 and 13; `REFACTOR_PLAN.md` P1-10.
- Supplier tracking search: supplier, representative, area, medicine name, registration number; filter by status. Reference: `ANALYSIS.md` sections 7 and 14; `REFACTOR_PLAN.md` P1-14.
- Sort behavior beyond latest-first: Needs confirmation before coding. Reference: `REFACTOR_PLAN.md` Risk Control.

## 7. Blade/UI Design

### Page Blade Files

- `Modules/Pharma/resources/views/pages/index.blade.php`
- `Modules/Pharma/resources/views/pages/create.blade.php`
- `Modules/Pharma/resources/views/pages/edit.blade.php`
- `Modules/Pharma/resources/views/pages/drug-bid-award/index.blade.php`
- `Modules/Pharma/resources/views/pages/drug-bid-award/create.blade.php`
- `Modules/Pharma/resources/views/pages/drug-bid-award/edit.blade.php`
- `Modules/Pharma/resources/views/pages/supplier-trackings/index.blade.php`
- `Modules/Pharma/resources/views/pages/supplier-trackings/create.blade.php`
- `Modules/Pharma/resources/views/pages/supplier-trackings/edit.blade.php`
- Optional `Modules/Pharma/resources/views/pages/supplier-trackings/import-export.blade.php` if route is retained. Needs confirmation before coding. Reference: `REFACTOR_PLAN.md` P0-03.
- Optional show page only if detail workflow is confirmed. Reference: `REFACTOR_PLAN.md` P2-02.

Design decisions:

- Replace `container-fluid` with the active Tailwind admin page container. Reference: `ANALYSIS.md` section 4; `REFACTOR_PLAN.md` P1-13.
- Keep page blades as shells only. Reference: `CODEX_BOOTSTRAP.md` architecture rules; `ANALYSIS.md` section 4.

### Livewire Blade Files

- `Modules/Pharma/resources/views/livewire/medicine/index.blade.php`
- `Modules/Pharma/resources/views/livewire/medicine/form.blade.php`
- `Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php`
- `Modules/Pharma/resources/views/livewire/drug-bid-award/form.blade.php`
- `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php`
- `Modules/Pharma/resources/views/livewire/supplier-trackings/form.blade.php`

Design decisions:

- Add safe `rel="noopener noreferrer"` to external links and validate URLs. Reference: `ANALYSIS.md` sections 6 and 10; `REFACTOR_PLAN.md` P1-12.
- Use cast-aware date display rather than `date(strtotime(...))`. Reference: `ANALYSIS.md` section 6; `REFACTOR_PLAN.md` P1-12.
- Remove duplicate supplier import/export controls once shared panel is canonical. Reference: `ANALYSIS.md` section 6; `REFACTOR_PLAN.md` P1-02.

### Shared Components

- `x-select-search` for medicine selectors and long filter lists. Reference: `ANALYSIS.md` section 13; `REFACTOR_PLAN.md` P1-04 and P1-10.
- `shared.import-export.panel` for import/export. Reference: `ANALYSIS.md` section 9; `REFACTOR_PLAN.md` P1-01 and P1-02.
- Shared money/percent formatting helper or Blade component only if it reduces proven duplication. Reference: `ANALYSIS.md` section 14; `REFACTOR_PLAN.md` P1-14.

### AdminLTE/Bootstrap Layout Rules

- Do not add new Bootstrap or jQuery patterns.
- Existing `container-fluid` wrappers must be replaced with Tailwind admin containers during P1 UI cleanup.
- Existing Admin layout remains `Admin::layouts.master`.

References: `ANALYSIS.md` section 4; `REFACTOR_PLAN.md` P1-13; `AI_PROJECT_CONTEXT.md` Admin UI Standard.

### Table Design

- Responsive `overflow-x-auto`.
- Server-side pagination.
- Empty, loading, disabled, and selected-row states.
- Bulk delete with confirmation and server-side authorization.
- Guarded `All` option only.

References: `ANALYSIS.md` sections 6, 10, 12, and 13; `REFACTOR_PLAN.md` P0-04, P1-05, P1-06, and P1-10.

### Form Design

- Field-level validation messages.
- Consistent Tailwind input classes.
- Searchable relationship selector for medicines.
- URL fields validated and displayed safely.
- Derived supplier tracking fields read-only in UI and recalculated by service.

References: `ANALYSIS.md` sections 5, 6, 7, and 11; `REFACTOR_PLAN.md` P1-04, P1-07, P1-12, and P2-03.

## 8. Import Design

### Import Classes

Target:

- `Modules/Pharma/Services/ImportExport.php` for supplier tracking first.
- Add `Modules/Pharma/Import/SupplierTrackingImport.php`, `RowMapper.php`, `RowNormalizer.php`, or `RowValidator.php` only if service responsibilities become too large.
- Medicine and drug bid award import classes: Needs confirmation before coding, because sample files and mapping rules are not confirmed.

References: `ANALYSIS.md` sections 9 and 14; `REFACTOR_PLAN.md` P1-01 and P1-08.

### Header Mapping

- Supplier tracking may support Vietnamese headers such as `Ngày làm việc`, `Tên thuốc`, `Số đăng ký`, `Nhà cung cấp`, and price/formula fields.
- Header aliases must be explicit and tested.
- Medicine and bid award headers: Needs confirmation before coding with sample files.

References: `ANALYSIS.md` sections 9 and 11; `REFACTOR_PLAN.md` P1-08 and Risk Control.

### Column Mapping

- Supplier tracking existing mapping uses A-V in `Modules/Pharma/Services/ImportExport.php`; keep only if the file format is confirmed.
- Calculated columns may appear in templates but must not be trusted for persistence.
- Medicine and bid award positional mapping: Needs confirmation before coding.

References: `ANALYSIS.md` section 9; `REFACTOR_PLAN.md` P1-08.

### Row Normalization

- Trim strings and convert empty strings to null where safe.
- Normalize money using locale-aware decimal parsing.
- Normalize dates from `d/m/Y`, `d-m-Y`, `Y-m-d`, `Y/m/d`, and Excel serials where confirmed.
- Normalize status through the confirmed supplier status list.
- Lookup medicine by registration number first, then name only if confirmed safe.

References: `ANALYSIS.md` sections 7, 9, 11, and 13; `REFACTOR_PLAN.md` P1-03, P1-08, and P1-10.

### Row Validation

- Validate after mapping and normalization.
- Validate required fields, types, status, foreign key medicine match, URL, numeric ranges, and unique business key.
- Return row-level errors with sheet, row, column, value, and reason.

References: `ANALYSIS.md` section 11; `REFACTOR_PLAN.md` P1-07 and P1-08.

### Duplicate Handling

- Supplier tracking target mode may be `update_or_create` using `medicine_id + supplier_name + working_date`, but this needs confirmation before coding.
- Medicine target unique key should match `registration_number + packaging_specification`, but import null-overwrite behavior needs confirmation before coding.
- Bid award target unique key should match `bidding_notice_code + medicine_name + winning_company_name`, but duplicate handling needs confirmation before coding.
- Do not use `replace` mode without explicit confirmation.

References: `ANALYSIS.md` sections 8, 9, and 11; `REFACTOR_PLAN.md` P1-01, P1-08, P1-11, and Risk Control.

### Error Reporting

- Use shared report shape with totals, success rows, skipped rows, error rows, row-level errors, and debug metadata.
- Do not flash raw exception text.
- Log internal failures safely.

References: `ANALYSIS.md` sections 9 and 10; `REFACTOR_PLAN.md` P1-01 and P1-09.

## 9. Export Design

### Export Classes

Target:

- `Modules/Pharma/Services/ImportExport.php` for supplier tracking shared export.
- Add `Modules/Pharma/Export/ExportQuery.php`, `ExportMapper.php`, or `TemplateBuilder.php` when export logic needs separation.
- Medicine and bid award export services/classes: Needs confirmation before coding when import/export consolidation reaches those features.

References: `ANALYSIS.md` sections 9 and 13; `REFACTOR_PLAN.md` P1-01 and P1-10.

### Query Design

- Export queries must accept active filters.
- Supplier export must not ignore `$filters`.
- Use eager loading for `medicine` on supplier tracking and bid awards.
- Use chunk/lazy iteration for large exports.

References: `ANALYSIS.md` sections 7 and 13; `REFACTOR_PLAN.md` P1-10.

### Export Mapping

- Export defaults to model `$fillable` minus export exclusions.
- Supplier tracking should include medicine name and registration number through eager-loaded relation.
- Derived fields may be exported but must be system-calculated values.
- Exclude sensitive/internal fields such as contract URL/status/note only if business confirms they should be excluded. Needs confirmation before coding for final export columns.

References: `ANALYSIS.md` sections 8 and 9; `REFACTOR_PLAN.md` P1-01 and P1-11.

### Template Generation

- Supplier tracking template may include sample rows and mark formula columns as system-calculated.
- Medicine and bid award templates need confirmed headers and sample data before coding.

References: `ANALYSIS.md` section 9; `REFACTOR_PLAN.md` P1-08.

### Large Export Strategy

- Use bounded iteration and shared export storage.
- Do not call `get()` for production-sized exports.
- Queue exports only after authorization context, progress reporting, retry/idempotency, and failure reporting are defined. Needs confirmation before coding.

References: `ANALYSIS.md` section 13; `REFACTOR_PLAN.md` P1-10 and Risk Control.

## 10. Permissions and Authorization

### Required Permissions

Target permissions:

- `view_pharma`
- `create_pharma`
- `edit_pharma`
- `delete_pharma`
- `import_pharma`
- `export_pharma`

Needs confirmation before coding: whether permissions should be feature-specific, such as `view_pharma_medicine`, `import_pharma_supplier_tracking`, etc.

References: `ANALYSIS.md` sections 3, 10, and 16; `REFACTOR_PLAN.md` P0-02.

### Policy/Gate Checks

- Add route/controller checks for page access.
- Add Livewire action checks for create, update, delete, bulk delete, import, and export.
- Add record-level checks for route IDs and selected IDs.
- Bulk operations must fail closed if any selected ID is unauthorized unless a partial authorized-only behavior is explicitly confirmed.

References: `ANALYSIS.md` section 10; `REFACTOR_PLAN.md` P0-02 and P0-04.

### Livewire Action Protection

- Protect `save`, `delete`, `deleteSelected`, `import`, `importData`, `export`, and `exportData`.
- Do not rely on hidden buttons or disabled UI state.

References: `ANALYSIS.md` sections 5 and 10; `REFACTOR_PLAN.md` P0-02 and P0-04.

### Route Middleware

- Web routes keep `web` and `auth:admin`.
- Add named permission middleware where route-level page permission is clear.
- API route must be removed or protected with the approved API guard. Needs confirmation before coding.

References: `ANALYSIS.md` sections 2 and 10; `REFACTOR_PLAN.md` P0-01 and P0-02.

## 11. Transactions and Data Integrity

Actions requiring DB transactions:

- Medicine create/update/delete/bulk delete. Reference: `ANALYSIS.md` section 12; `REFACTOR_PLAN.md` P1-06.
- Drug bid award create/update/delete/bulk delete. Reference: `ANALYSIS.md` section 12; `REFACTOR_PLAN.md` P1-06.
- Supplier tracking create/update/delete/bulk delete. Reference: `ANALYSIS.md` section 12; `REFACTOR_PLAN.md` P1-06.
- Import persistence for supplier tracking, medicine, and bid awards. Reference: `ANALYSIS.md` section 12; `REFACTOR_PLAN.md` P1-08.

Rollback conditions:

- Validation failure before write should prevent persistence.
- Unauthorized record or selected ID should prevent persistence.
- Unexpected exceptions inside create/update/delete/bulk delete should roll back.
- Import rollback strategy is Needs confirmation before coding: all-or-nothing versus partial row success.

References: `ANALYSIS.md` sections 10, 11, and 12; `REFACTOR_PLAN.md` P0-04, P1-06, P1-08, and Risk Control.

Idempotency concerns:

- `update_or_create` imports must use confirmed unique keys.
- Retried imports must not duplicate records.
- Bulk delete retries must safely handle records already deleted.
- Queued imports/exports are Needs confirmation before coding.

References: `ANALYSIS.md` sections 8, 9, and 12; `REFACTOR_PLAN.md` P1-08, P1-10, and Risk Control.

## 12. Performance Strategy

### Eager Loading

- Bid award lists and exports eager load `medicine` when medicine fields are displayed/exported.
- Supplier tracking lists and exports eager load `medicine`.

References: `ANALYSIS.md` sections 7 and 13; `REFACTOR_PLAN.md` P1-10.

### Query Optimization

- Move all search/filter query building into services.
- Extract supplier tracking `queryForFilters()` to avoid duplicated search logic.
- Add indexes only for confirmed search/filter/sort fields.
- Avoid `LIKE '%term%'` as the only strategy on large datasets; Needs confirmation before coding if full-text/search indexing is required.

References: `ANALYSIS.md` sections 13 and 14; `REFACTOR_PLAN.md` P1-10, P1-11, and P1-14.

### Pagination

- Default server-side pagination.
- Guard `All` with max row cap, warning, or disablement.
- Reset page on filter/search/per-page changes.

References: `ANALYSIS.md` sections 5, 6, and 13; `REFACTOR_PLAN.md` P1-05 and P1-10.

### Caching

- No cache by default.
- Cache stable option lists only after explicit invalidation rules are defined.
- Needs confirmation before coding for cache duration and invalidation.

References: `AI_PROJECT_CONTEXT.md` Performance Standard; `REFACTOR_PLAN.md` Risk Control.

## 13. Test Strategy

### Route Tests

- Route boot test for `Modules/Pharma/routes/web.php`.
- Assert no route points to missing controller methods.
- Assert API route removed or protected.

References: `ANALYSIS.md` sections 2 and 15; `REFACTOR_PLAN.md` P0-01 and P0-03.

### Livewire Tests

- Medicine, bid award, supplier index filters and pagination.
- Form validation for required fields, URLs, statuses, and composite unique keys.
- Delete and bulk delete confirmation/action behavior.
- Denied action behavior for unauthorized admins.

References: `ANALYSIS.md` sections 5, 10, 11, and 13; `REFACTOR_PLAN.md` P0-02, P0-04, P1-05, P1-07, and P1-10.

### Service Tests

- Search/filter/pagination query behavior.
- Formula calculation in `SupplierTrackingService`.
- Transactions and rollback for create/update/delete/bulk delete.
- Business invariant validation.

References: `ANALYSIS.md` sections 7, 12, 13, and 14; `REFACTOR_PLAN.md` P1-06, P1-10, and P1-14.

### Import Tests

- Header/column mapping.
- Normalization for dates, money, booleans/statuses, and empty strings.
- Duplicate handling by confirmed unique key.
- Dry-run behavior.
- Row-level error reporting.
- All-or-nothing or partial success behavior after confirmation.

References: `ANALYSIS.md` sections 9, 11, and 12; `REFACTOR_PLAN.md` P1-01 and P1-08.

### Export Tests

- Filters are applied to export queries.
- Large export uses bounded iteration.
- Mapping uses `$fillable` and export exclusions.
- Templates include formula warnings.

References: `ANALYSIS.md` sections 8, 9, and 13; `REFACTOR_PLAN.md` P1-01 and P1-10.

### Authorization Tests

- Denied page access.
- Denied create/edit/delete/import/export.
- Tampered route IDs and selected IDs.
- Bulk delete unauthorized IDs.

References: `ANALYSIS.md` section 10; `REFACTOR_PLAN.md` P0-02 and P0-04.

## 14. Implementation Checklist

### P0

- [ ] Remove/protect `Modules/Pharma/routes/api.php` and fix/remove `Modules/Pharma/Http/Controllers/Api/PharmaController.php`. Reference: `REFACTOR_PLAN.md` P0-01.
- [ ] Fix/remove `admin.pharma.supplier-trackings.import-export` route in `Modules/Pharma/routes/web.php`. Reference: `REFACTOR_PLAN.md` P0-03.
- [ ] Add route/controller/page permission enforcement for Pharma admin pages. Reference: `REFACTOR_PLAN.md` P0-02.
- [ ] Add Livewire authorization to every mutating action. Reference: `REFACTOR_PLAN.md` P0-02.
- [ ] Add record-level authorization for route IDs and selected IDs. Reference: `REFACTOR_PLAN.md` P0-04.
- [ ] Add route and authorization regression tests. Reference: `ROADMAP.md` P0-06; `REFACTOR_PLAN.md` P0-02 and P0-04.

### P1

- [ ] Confirm supplier tracking status vocabulary before coding. Reference: `REFACTOR_PLAN.md` P1-03.
- [ ] Add composite unique validation for medicines and bid awards. Reference: `REFACTOR_PLAN.md` P1-07.
- [ ] Replace direct model query in `Modules/Pharma/Livewire/DrugBidAward/Form.php`. Reference: `REFACTOR_PLAN.md` P1-04.
- [ ] Standardize Livewire pagination/service injection. Reference: `REFACTOR_PLAN.md` P1-05.
- [ ] Add transaction boundaries and service-owned bulk delete. Reference: `REFACTOR_PLAN.md` P1-06.
- [ ] Consolidate supplier tracking import/export through `Modules/Pharma/Services/ImportExport.php`. Reference: `REFACTOR_PLAN.md` P1-01 and P1-02.
- [ ] Confirm sample files and mappings before replacing medicine/bid award import/export. Reference: `REFACTOR_PLAN.md` P1-08 and Risk Control.
- [ ] Bound `All` pagination, selectors, imports, and exports. Reference: `REFACTOR_PLAN.md` P1-10.
- [ ] Update model metadata and schema constraints through safe migrations. Reference: `REFACTOR_PLAN.md` P1-11.
- [ ] Normalize safe error handling. Reference: `REFACTOR_PLAN.md` P1-09.
- [ ] Replace `container-fluid` page wrappers with active Tailwind layout. Reference: `REFACTOR_PLAN.md` P1-13.

### P2

- [ ] Remove confirmed unused scaffold files after tests prove no references. Reference: `REFACTOR_PLAN.md` P2-01.
- [ ] Confirm and complete or remove supplier tracking show flow. Reference: `REFACTOR_PLAN.md` P2-02.
- [ ] Improve repeated UI/icon markup after functional refactors. Reference: `REFACTOR_PLAN.md` P2-03.
- [ ] Replace scaffold notes in `Modules/Pharma/readme.md` with useful module documentation. Reference: `REFACTOR_PLAN.md` P2-04.
- [ ] Add medicine migration comments safely. Reference: `REFACTOR_PLAN.md` P2-05.
