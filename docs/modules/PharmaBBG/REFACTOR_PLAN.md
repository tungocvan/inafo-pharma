# Pharma Refactor Plan

Assessment date: 2026-06-18

Scope: `Modules/Pharma` only. This is a plan document; no implementation code is included.

Source documents read:

- `docs/modules/Pharma/ANALYSIS.md`
- `docs/AI_PROJECT_CONTEXT.md`
- `docs/CODEX_BOOTSTRAP.md`
- `ROADMAP.md`

## 1. Executive Summary

The Pharma module owns medicine master data, drug bid award data, and supplier tracking data. The module is functional in shape, but it has four urgent risk themes:

- Security: public/broken API route, missing named permissions, and mutating Livewire actions that trust route IDs or selected IDs.
- Correctness: broken route/controller contracts, weak unique validation, inconsistent supplier tracking statuses, and mixed import/export behavior.
- Architecture: direct model queries from Livewire, duplicated import/export paths, business and filesystem work inside Livewire, and scaffold files still present.
- Performance/data safety: unbounded `All` pagination, full-dataset exports, per-row import queries, missing transactions, and schema/index gaps.

Recommended direction: stabilize security and broken routes first, then consolidate import/export around `Modules/Shared/Services/ImportExport`, move all business/query work into services, add transactions and validation, and defer UI cleanup plus scaffold deletion until route and behavior tests exist.

## 2. P0 Critical Fixes

### P0-01: Remove or protect the broken public API route

* Issue: `Modules/Pharma/routes/api.php` exposes `GET /pharma` without authentication and points to missing `Modules\Pharma\Http\Controllers\Api\PharmaController@index`. `Modules/Pharma/Http/Controllers/Api/PharmaController.php` is an empty scaffold.
* Root Cause: Scaffold API route was left enabled while its controller action was never implemented or protected.
* Business Impact: Public unauthenticated surface can expose future Pharma data accidentally and currently creates runtime failures.
* Technical Impact: Route boot or request handling can fail because the target action does not exist.
* Proposed Solution: Decide whether Pharma needs an API. If not, remove or comment the route in `Modules/Pharma/routes/api.php` and mark the API controller as removable. If yes, add `auth:sanctum` or the project-approved API guard, implement a thin action in `Modules/Pharma/Http/Controllers/Api/PharmaController.php`, and route all data access through a Pharma service.
* Files To Change: `Modules/Pharma/routes/api.php`, `Modules/Pharma/Http/Controllers/Api/PharmaController.php`, relevant route/security tests under `tests/Feature/Pharma/`.
* Risk Level: Critical.
* Complexity: Low if removed; Medium if implemented.
* Estimated Effort: 0.5 day to remove and test; 1-2 days to implement safely.
* Acceptance Criteria: No unauthenticated Pharma API route exists unless explicitly required; `GET /pharma` either returns 404 or requires the approved API guard; route boot tests pass; no controller action references a missing method.

### P0-02: Add named authorization to Pharma routes and Livewire actions

* Issue: `Modules/Pharma/routes/web.php` only uses `auth:admin`; `Modules/Pharma/Http/Controllers/PharmaController.php` has commented permission middleware; `Modules/Pharma/Http/Controllers/DrugBidAwardController.php`, `Modules/Pharma/Http/Controllers/SupplierTrackingController.php`, `Modules/Pharma/Livewire/Medicine/Index.php`, `Modules/Pharma/Livewire/Medicine/Form.php`, `Modules/Pharma/Livewire/DrugBidAward/Index.php`, `Modules/Pharma/Livewire/DrugBidAward/Form.php`, `Modules/Pharma/Livewire/SupplierTrackings/Index.php`, and `Modules/Pharma/Livewire/SupplierTrackings/Form.php` expose create, update, delete, import, and export behavior without visible permission checks.
* Root Cause: Authentication was treated as sufficient authorization; scaffold permissions in `Modules/Pharma/config/module.php` are not enforced consistently.
* Business Impact: Any authenticated admin may alter or export sensitive commercial and pharmaceutical data.
* Technical Impact: Authorization cannot be tested or reasoned about at action level.
* Proposed Solution: Define capability-level permissions such as `view_pharma`, `create_pharma`, `edit_pharma`, `delete_pharma`, `import_pharma`, and `export_pharma`; enforce them at routes/controllers for page access and inside every Livewire mutating method before service calls.
* Files To Change: `Modules/Pharma/config/module.php`, `Modules/Pharma/routes/web.php`, `Modules/Pharma/Http/Controllers/PharmaController.php`, `Modules/Pharma/Http/Controllers/DrugBidAwardController.php`, `Modules/Pharma/Http/Controllers/SupplierTrackingController.php`, `Modules/Pharma/Livewire/Medicine/Index.php`, `Modules/Pharma/Livewire/Medicine/Form.php`, `Modules/Pharma/Livewire/DrugBidAward/Index.php`, `Modules/Pharma/Livewire/DrugBidAward/Form.php`, `Modules/Pharma/Livewire/SupplierTrackings/Index.php`, `Modules/Pharma/Livewire/SupplierTrackings/Form.php`, tests under `tests/Feature/Pharma/`.
* Risk Level: Critical.
* Complexity: Medium.
* Estimated Effort: 2-3 days including negative tests.
* Acceptance Criteria: Unauthorized admins are denied for view/create/edit/delete/import/export; authorized admins can perform allowed actions; every mutating Livewire method has a server-side permission check; tests cover allowed and denied paths.

### P0-03: Fix the broken supplier tracking import/export route

* Issue: `Modules/Pharma/routes/web.php` defines `admin.pharma.supplier-trackings.import-export`, but `Modules/Pharma/Http/Controllers/SupplierTrackingController.php` has no `importExport()` method and there is no matching page blade.
* Root Cause: Route was added ahead of the controller/page implementation or after the UI moved into `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php`.
* Business Impact: Users following the route hit a server error instead of a controlled import/export experience.
* Technical Impact: Route boot/request tests will fail; duplicate import/export UI decisions remain unclear.
* Proposed Solution: Prefer one import/export entry point. Either remove this route if import/export remains on the index page, or add a thin `importExport()` action and dedicated page blade that mounts only `shared.import-export.panel`.
* Files To Change: `Modules/Pharma/routes/web.php`, `Modules/Pharma/Http/Controllers/SupplierTrackingController.php`, optional `Modules/Pharma/resources/views/pages/supplier-trackings/import-export.blade.php`, `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php`, route tests under `tests/Feature/Pharma/`.
* Risk Level: Critical.
* Complexity: Low.
* Estimated Effort: 0.5-1 day.
* Acceptance Criteria: No Pharma route points to a missing controller method; the supplier tracking import/export route either does not exist or returns a valid authenticated page; route list/route tests pass.

### P0-04: Authorize selected IDs and route IDs server-side

* Issue: `Modules/Pharma/Livewire/Medicine/Index.php`, `Modules/Pharma/Livewire/DrugBidAward/Index.php`, and `Modules/Pharma/Livewire/SupplierTrackings/Index.php` trust client-selected IDs for single and bulk deletes. `Modules/Pharma/Livewire/Medicine/Form.php`, `Modules/Pharma/Livewire/DrugBidAward/Form.php`, and `Modules/Pharma/Livewire/SupplierTrackings/Form.php` accept route-provided IDs and only call `findOrFail()`.
* Root Cause: UI state and route parameters are treated as trusted after admin authentication.
* Business Impact: A user can potentially delete or edit records outside their allowed scope by changing client state or URLs.
* Technical Impact: Authorization behavior is scattered and cannot guarantee ownership/record-level checks.
* Proposed Solution: Add service-level authorized query helpers or policies/gates that validate each ID before read/update/delete/export. Bulk operations should filter authorized IDs and fail closed if any submitted ID is unauthorized.
* Files To Change: `Modules/Pharma/Livewire/Medicine/Index.php`, `Modules/Pharma/Livewire/Medicine/Form.php`, `Modules/Pharma/Livewire/DrugBidAward/Index.php`, `Modules/Pharma/Livewire/DrugBidAward/Form.php`, `Modules/Pharma/Livewire/SupplierTrackings/Index.php`, `Modules/Pharma/Livewire/SupplierTrackings/Form.php`, `Modules/Pharma/Services/MedicineService.php`, `Modules/Pharma/Services/DrugBidAwardService.php`, `Modules/Pharma/Services/SupplierTrackingService.php`, tests under `tests/Feature/Pharma/`.
* Risk Level: Critical.
* Complexity: Medium.
* Estimated Effort: 2-4 days depending on existing permission conventions.
* Acceptance Criteria: Tampered IDs are denied; bulk delete fails closed or deletes only explicitly authorized records according to confirmed business rule; denied attempts do not mutate data; tests cover single and bulk unauthorized cases.

## 3. P1 Important Refactors

### P1-01: Consolidate Pharma import/export on the shared foundation

* Issue: Import/export logic is duplicated across `Modules/Pharma/Services/MedicineService.php`, `Modules/Pharma/Services/MedicineImportService.php`, `Modules/Pharma/Services/DrugBidAwardService.php`, `Modules/Pharma/Services/SupplierTrackingService.php`, `Modules/Pharma/Services/ImportExport.php`, `Modules/Pharma/Console/Commands/ImportMedicineCommand.php`, and `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php`.
* Root Cause: Legacy CSV/FastExcel implementations were added before or alongside `Modules/Shared/Services/ImportExport`.
* Business Impact: The same data can be imported/exported with different validation, statuses, unique keys, and error reports.
* Technical Impact: High maintenance cost; inconsistent dry-run/report behavior; difficult to test safely.
* Proposed Solution: Make `Modules/Pharma/Services/ImportExport.php` the shared entry point for supplier tracking first. Plan follow-up shared services or split import/export classes for medicines and drug bid awards after sample files and mapping rules are confirmed. Keep `Modules/Pharma/Console/Commands/ImportMedicineCommand.php` only if it delegates to the same canonical import service.
* Files To Change: `Modules/Pharma/Services/ImportExport.php`, `Modules/Pharma/Services/SupplierTrackingService.php`, `Modules/Pharma/Services/MedicineService.php`, `Modules/Pharma/Services/MedicineImportService.php`, `Modules/Pharma/Services/DrugBidAwardService.php`, `Modules/Pharma/Console/Commands/ImportMedicineCommand.php`, `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php`, possible new `Modules/Pharma/Import/*`, possible new `Modules/Pharma/Export/*`, tests under `tests/Unit/Pharma/` and `tests/Feature/Pharma/`.
* Risk Level: High.
* Complexity: High.
* Estimated Effort: 1-2 weeks, staged by feature.
* Acceptance Criteria: Each Pharma data type has one canonical import/export path; shared report shape is used; dry-run behavior is available where required; duplicate UI controls are removed; old paths are kept only as thin adapters or are deleted after tests prove no callers remain.

### P1-02: Remove duplicate supplier tracking import/export controls

* Issue: `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php` renders custom file import/export controls and also mounts `shared.import-export.panel` using `Modules\Pharma\Services\ImportExport`.
* Root Cause: New shared panel was added without removing the old Livewire/FastExcel controls.
* Business Impact: Users may choose two workflows that produce different data outcomes.
* Technical Impact: Duplicate Livewire actions in `Modules/Pharma/Livewire/SupplierTrackings/Index.php` remain live and unbounded.
* Proposed Solution: After confirming the canonical supplier tracking import/export behavior, remove the custom controls and make the page use only `shared.import-export.panel` or move the panel to a dedicated route/page.
* Files To Change: `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php`, `Modules/Pharma/Livewire/SupplierTrackings/Index.php`, `Modules/Pharma/Services/ImportExport.php`, optional `Modules/Pharma/resources/views/pages/supplier-trackings/import-export.blade.php`.
* Risk Level: High.
* Complexity: Medium.
* Estimated Effort: 1-2 days after import/export behavior is confirmed.
* Acceptance Criteria: Supplier tracking page exposes only one import/export workflow; removed Livewire actions are no longer callable; tests cover the remaining workflow.

### P1-03: Align supplier tracking status vocabulary

* Issue: `Modules/Pharma/Services/ImportExport.php` accepts `active`, `inactive`, `draft`, `expired`, while `Modules/Pharma/Livewire/SupplierTrackings/Index.php` and `Modules/Pharma/Livewire/SupplierTrackings/Form.php` use `active`, `completed`, `paused`, `cancelled`; `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php` stores a free string.
* Root Cause: Import/export status rules and UI status rules evolved separately.
* Business Impact: Imported records may disappear from expected filters or show unexpected labels.
* Technical Impact: Validation, filtering, and exports disagree on allowed state values.
* Proposed Solution: Confirm the business status set, then centralize it in `SupplierTrackingService` or a module enum-like constant class without introducing DTOs. Apply the same set in Livewire validation, import validation, normalization, display labels, and migration comments or constraints.
* Files To Change: `Modules/Pharma/Services/ImportExport.php`, `Modules/Pharma/Services/SupplierTrackingService.php`, `Modules/Pharma/Livewire/SupplierTrackings/Index.php`, `Modules/Pharma/Livewire/SupplierTrackings/Form.php`, `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php`, tests under `tests/Unit/Pharma/` and `tests/Feature/Pharma/`.
* Risk Level: High.
* Complexity: Medium.
* Estimated Effort: 1-2 days plus data migration review if production data exists.
* Acceptance Criteria: One status list is used everywhere; invalid statuses fail validation; existing data migration/backfill plan is documented before schema changes.

### P1-04: Move direct model queries out of Livewire

* Issue: `Modules/Pharma/Livewire/DrugBidAward/Form.php` imports `Modules\Pharma\Models\Medicine` and calls `Medicine::query()->latest()->get()` in `render()`.
* Root Cause: Relationship option loading was implemented directly in the component instead of through a service.
* Business Impact: Large medicine catalogs can make the form slow or fail.
* Technical Impact: Violates the required Route -> Controller -> Page Blade -> Livewire -> Service -> Model flow.
* Proposed Solution: Add a bounded/searchable medicine lookup method to `Modules/Pharma/Services/MedicineService.php` or `Modules/Pharma/Services/DrugBidAwardService.php`, then inject/use the service from the Livewire component. Prefer `x-select-search` with server-side search when the list is large.
* Files To Change: `Modules/Pharma/Livewire/DrugBidAward/Form.php`, `Modules/Pharma/Services/MedicineService.php`, `Modules/Pharma/Services/DrugBidAwardService.php`, `Modules/Pharma/resources/views/livewire/drug-bid-award/form.blade.php`, tests under `tests/Feature/Pharma/`.
* Risk Level: Medium.
* Complexity: Medium.
* Estimated Effort: 1-2 days.
* Acceptance Criteria: No model query remains in the Livewire component; medicine selector is bounded/searchable; form still fills medicine name and packaging as before.

### P1-05: Standardize Livewire service injection and pagination

* Issue: `Modules/Pharma/Livewire/Medicine/Index.php` uses manual `$page` state instead of `WithPagination`; `Modules/Pharma/Livewire/SupplierTrackings/Form.php` calls `app(SupplierTrackingService::class)` in `recalculate()`; multiple components resolve services inconsistently through method injection or `app()`.
* Root Cause: Components were built incrementally without one Livewire 3 pattern.
* Business Impact: Pagination and service calls behave inconsistently across feature screens.
* Technical Impact: Harder to test; increased risk of stale state and service bypasses.
* Proposed Solution: Use Livewire `WithPagination` for list components, `resetPage()` for filter changes, and `boot()` service injection where repeated service use is needed.
* Files To Change: `Modules/Pharma/Livewire/Medicine/Index.php`, `Modules/Pharma/Livewire/DrugBidAward/Index.php`, `Modules/Pharma/Livewire/SupplierTrackings/Index.php`, `Modules/Pharma/Livewire/SupplierTrackings/Form.php`, related Livewire blades under `Modules/Pharma/resources/views/livewire/`.
* Risk Level: Medium.
* Complexity: Medium.
* Estimated Effort: 2-3 days.
* Acceptance Criteria: List pagination uses Livewire 3 conventions; filters reset pages consistently; repeated service calls do not use raw `app()` resolution; pagination tests pass.

### P1-06: Make destructive and multi-record operations transactional

* Issue: `Modules/Pharma/Services/SupplierTrackingService.php` create, update, delete, deleteMany, and importRows are not transactional. `Modules/Pharma/Livewire/Medicine/Index.php` and `Modules/Pharma/Livewire/DrugBidAward/Index.php` loop deletes one row at a time. `Modules/Pharma/Services/MedicineService.php`, `Modules/Pharma/Services/DrugBidAwardService.php`, and `Modules/Pharma/Services/MedicineImportService.php` manually manage transactions around file handles.
* Root Cause: Transaction boundaries were applied inconsistently and bulk behavior stayed in Livewire.
* Business Impact: Partial writes or partial deletes can leave business data inconsistent.
* Technical Impact: Rollback behavior is hard to test; file handles can leak on unexpected failures.
* Proposed Solution: Move bulk delete into services, use `DB::transaction()` for multi-write operations, close resources in `finally`, and define partial-vs-all-or-nothing import behavior before changing imports.
* Files To Change: `Modules/Pharma/Services/SupplierTrackingService.php`, `Modules/Pharma/Services/MedicineService.php`, `Modules/Pharma/Services/DrugBidAwardService.php`, `Modules/Pharma/Services/MedicineImportService.php`, `Modules/Pharma/Livewire/Medicine/Index.php`, `Modules/Pharma/Livewire/DrugBidAward/Index.php`, `Modules/Pharma/Livewire/SupplierTrackings/Index.php`, tests under `tests/Unit/Pharma/`.
* Risk Level: High.
* Complexity: Medium.
* Estimated Effort: 3-5 days.
* Acceptance Criteria: Bulk deletes are atomic or explicitly partial by confirmed rule; service tests prove rollback; Livewire no longer loops destructive service calls for bulk operations.

### P1-07: Strengthen validation and unique-key handling

* Issue: `Modules/Pharma/Livewire/Medicine/Form.php` does not validate the database unique key `registration_number + packaging_specification`; `Modules/Pharma/Livewire/DrugBidAward/Form.php` does not validate `bidding_notice_code + medicine_name + winning_company_name`; `Modules/Pharma/Livewire/SupplierTrackings/Form.php` validates `status` as any string and `contract_url` as string; `Modules/Pharma/Livewire/SupplierTrackings/Index.php` upload validation has no max file size.
* Root Cause: UI validation covers basic field types but not database/business invariants.
* Business Impact: Users can submit data that fails at DB level or creates unusable status/link values.
* Technical Impact: Exceptions become control flow; error messages are less helpful.
* Proposed Solution: Add Livewire validation rules for composite unique constraints, allowed statuses, URL fields, and upload size. Add service-level invariants for non-HTTP callers and imports.
* Files To Change: `Modules/Pharma/Livewire/Medicine/Form.php`, `Modules/Pharma/Livewire/DrugBidAward/Form.php`, `Modules/Pharma/Livewire/SupplierTrackings/Form.php`, `Modules/Pharma/Livewire/SupplierTrackings/Index.php`, `Modules/Pharma/Services/MedicineService.php`, `Modules/Pharma/Services/DrugBidAwardService.php`, `Modules/Pharma/Services/SupplierTrackingService.php`, `Modules/Pharma/Services/ImportExport.php`, tests under `tests/Feature/Pharma/`.
* Risk Level: Medium.
* Complexity: Medium.
* Estimated Effort: 2-4 days.
* Acceptance Criteria: Duplicate submissions show field-level validation errors; invalid statuses and URLs are rejected; upload size is bounded; service callers cannot bypass critical invariants.

### P1-08: Harden import row mapping and validation

* Issue: `Modules/Pharma/Services/SupplierTrackingService.php` importRows does not validate normalized rows; `Modules/Pharma/Services/MedicineService.php` accesses positional indexes up to `20` without count checks; `Modules/Pharma/Services/DrugBidAwardService.php` accepts only one date format and rolls back the whole file on parse failures; `Modules/Pharma/Services/MedicineService.php` accepts XLSX uploads but parses with `fgetcsv()`.
* Root Cause: File parsing is ad hoc and not routed through the shared import/export normalizers and report builders.
* Business Impact: Valid user files can fail unexpectedly; invalid files can produce silent skips or misleading success counts.
* Technical Impact: Error reporting is inconsistent and difficult to test.
* Proposed Solution: Move row mapping, normalization, validation, and error reporting to shared import/export services or dedicated Pharma import classes. Confirm sample file formats and mapping mode before implementation.
* Files To Change: `Modules/Pharma/Services/SupplierTrackingService.php`, `Modules/Pharma/Services/MedicineService.php`, `Modules/Pharma/Services/DrugBidAwardService.php`, `Modules/Pharma/Services/MedicineImportService.php`, `Modules/Pharma/Services/ImportExport.php`, possible `Modules/Pharma/Import/*`, tests under `tests/Unit/Pharma/`.
* Risk Level: High.
* Complexity: High.
* Estimated Effort: 1 week after sample files are confirmed.
* Acceptance Criteria: Imports report row-level errors; XLSX is only accepted where actually parsed; malformed rows do not crash whole imports unless all-or-nothing is confirmed; dry-run and duplicate behavior are tested.

### P1-09: Normalize safe error handling

* Issue: `Modules/Pharma/Livewire/Medicine/Index.php`, `Modules/Pharma/Livewire/Medicine/Form.php`, `Modules/Pharma/Livewire/DrugBidAward/Index.php`, and `Modules/Pharma/Livewire/DrugBidAward/Form.php` flash raw exception messages from save/import failures.
* Root Cause: Exceptions are passed directly to user-facing session messages.
* Business Impact: Internal file paths, SQL details, or validation internals may leak to admins.
* Technical Impact: Operational failures are not consistently logged or redacted.
* Proposed Solution: Report/log internal exceptions and display safe user messages. Use domain exceptions or validation errors for expected user-fixable failures.
* Files To Change: `Modules/Pharma/Livewire/Medicine/Index.php`, `Modules/Pharma/Livewire/Medicine/Form.php`, `Modules/Pharma/Livewire/DrugBidAward/Index.php`, `Modules/Pharma/Livewire/DrugBidAward/Form.php`, `Modules/Pharma/Services/MedicineService.php`, `Modules/Pharma/Services/DrugBidAwardService.php`, tests under `tests/Feature/Pharma/`.
* Risk Level: Medium.
* Complexity: Low.
* Estimated Effort: 1 day.
* Acceptance Criteria: Unexpected exceptions are logged but not flashed raw; user-facing messages are safe and actionable; tests assert no raw exception text appears.

### P1-10: Bound queries, exports, imports, and `All` pagination

* Issue: `Modules/Pharma/Livewire/Medicine/Index.php` and `Modules/Pharma/Livewire/DrugBidAward/Index.php` map `All` to `999999`; `Modules/Pharma/Services/SupplierTrackingService.php` exportRows uses `get()`; `Modules/Pharma/Services/ImportExport.php` exportRows loads all records and ignores filters; `Modules/Pharma/Services/SupplierTrackingService.php` importRows queries Medicine per row; `Modules/Pharma/Services/SupplierTrackingService.php` medicinesForSelect loads all medicines.
* Root Cause: Convenience loading was used instead of chunked/lazy/query-limited operations.
* Business Impact: Large catalogs or exports can time out, exhaust memory, or produce incomplete downloads.
* Technical Impact: No query-count or memory bounds exist for heavy workflows.
* Proposed Solution: Guard `All` with a configured cap, use chunk/lazy exports through shared storage, apply filters in export queries, prefetch medicine lookup maps for imports, and use searchable paginated selectors for large medicine lists.
* Files To Change: `Modules/Pharma/Livewire/Medicine/Index.php`, `Modules/Pharma/Livewire/DrugBidAward/Index.php`, `Modules/Pharma/Livewire/DrugBidAward/Form.php`, `Modules/Pharma/Livewire/SupplierTrackings/Form.php`, `Modules/Pharma/Services/MedicineService.php`, `Modules/Pharma/Services/DrugBidAwardService.php`, `Modules/Pharma/Services/SupplierTrackingService.php`, `Modules/Pharma/Services/ImportExport.php`, related blades under `Modules/Pharma/resources/views/livewire/`.
* Risk Level: High.
* Complexity: Medium.
* Estimated Effort: 3-5 days.
* Acceptance Criteria: No request path loads unbounded datasets; export respects filters; medicine selectors are bounded/searchable; tests cover capped `All` and filtered export behavior.

### P1-11: Improve schema integrity, indexes, and model metadata

* Issue: `Modules/Pharma/Models/Medicine.php` uses `$guarded` instead of `$fillable`; `Modules/Pharma/Models/SupplierTracking.php` uses public `$exceptExport`; `Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php` lacks indexes for search/filter fields; `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php` lacks a unique key matching import `uniqueBy`, stores free-string status, and uses `cascadeOnDelete()`.
* Root Cause: Schema/model definitions were sufficient for first CRUD but not aligned with import/export, search, and historical data rules.
* Business Impact: Search may degrade; exports may expose wrong fields; deleting a medicine may delete supplier history unexpectedly.
* Technical Impact: Import upsert behavior can diverge from database constraints; model export defaults are unclear.
* Proposed Solution: Add explicit `$fillable`, replace public export exclusion with a safe accessor, add confirmed indexes/unique constraints, document status values, and confirm whether supplier history should be retained before changing cascade behavior.
* Files To Change: `Modules/Pharma/Models/Medicine.php`, `Modules/Pharma/Models/SupplierTracking.php`, `Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php`, `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php`, possible new follow-up migration, tests under `tests/Unit/Pharma/` and migration tests.
* Risk Level: High for schema changes; Medium for model metadata.
* Complexity: Medium.
* Estimated Effort: 2-5 days depending on data migration needs.
* Acceptance Criteria: Model fillable/export metadata is explicit; indexes match real query patterns; import unique key is backed by DB constraint or documented why not; delete behavior is confirmed and tested.

### P1-12: Fix external link safety and date formatting

* Issue: `Modules/Pharma/resources/views/livewire/medicine/index.blade.php`, `Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php`, and `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php` render external URLs directly with `target="_blank"`; `Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php` formats a cast date through `date('d/m/Y', strtotime(...))`.
* Root Cause: Blade views perform presentation shortcuts without centralized URL/date handling.
* Business Impact: Links can navigate users to unsafe locations; date display can break on null or non-string values.
* Technical Impact: Presentation logic is inconsistent and harder to test.
* Proposed Solution: Add `rel="noopener noreferrer"` for external links, consider URL allowlist policy for document URLs, and use cast-aware date formatting with null-safe access.
* Files To Change: `Modules/Pharma/resources/views/livewire/medicine/index.blade.php`, `Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php`, `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php`, validation files that accept URL fields.
* Risk Level: Medium.
* Complexity: Low.
* Estimated Effort: 0.5-1 day.
* Acceptance Criteria: External links include safe `rel`; invalid URLs are rejected by validation; date display handles null and cast objects safely.

### P1-13: Replace Bootstrap-era page wrappers with the active Admin UI container

* Issue: `Modules/Pharma/resources/views/pages/index.blade.php`, `Modules/Pharma/resources/views/pages/create.blade.php`, `Modules/Pharma/resources/views/pages/edit.blade.php`, `Modules/Pharma/resources/views/pages/drug-bid-award/index.blade.php`, `Modules/Pharma/resources/views/pages/drug-bid-award/create.blade.php`, `Modules/Pharma/resources/views/pages/drug-bid-award/edit.blade.php`, `Modules/Pharma/resources/views/pages/supplier-trackings/index.blade.php`, `Modules/Pharma/resources/views/pages/supplier-trackings/create.blade.php`, and `Modules/Pharma/resources/views/pages/supplier-trackings/edit.blade.php` use `container-fluid`.
* Root Cause: Legacy Bootstrap/AdminLTE layout conventions remain in page blades.
* Business Impact: UI consistency suffers across admin pages.
* Technical Impact: New Tailwind-based UI standards are mixed with Bootstrap layout classes.
* Proposed Solution: Replace wrappers with the active Tailwind page container after security and route fixes are complete.
* Files To Change: all page blades listed above.
* Risk Level: Medium.
* Complexity: Low.
* Estimated Effort: 0.5-1 day.
* Acceptance Criteria: Page blades use the standard admin page container; Livewire component mounting remains unchanged; visual regression is checked manually or by browser snapshot if available.

### P1-14: Consolidate repeated formatting and filter query logic

* Issue: Money/percent formatting is duplicated in `Modules/Pharma/Livewire/SupplierTrackings/Index.php` and `Modules/Pharma/Livewire/SupplierTrackings/Form.php`; supplier tracking filter query logic is duplicated in `paginate()`, `getFilteredIds()`, and `exportRows()` in `Modules/Pharma/Services/SupplierTrackingService.php`; medicine and bid award list pages duplicate import/upload/table/bulk-delete patterns.
* Root Cause: Reusable behavior was copied feature by feature.
* Business Impact: Small behavior changes require edits in multiple places.
* Technical Impact: Higher regression risk and inconsistent output.
* Proposed Solution: Extract query builder helpers inside `SupplierTrackingService`, use a shared formatting helper or Blade component for VND/percent display, and defer larger table/import UI consolidation until import/export architecture is stable.
* Files To Change: `Modules/Pharma/Services/SupplierTrackingService.php`, `Modules/Pharma/Livewire/SupplierTrackings/Index.php`, `Modules/Pharma/Livewire/SupplierTrackings/Form.php`, `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php`, `Modules/Pharma/resources/views/livewire/supplier-trackings/form.blade.php`, later `Modules/Pharma/resources/views/livewire/medicine/index.blade.php`, `Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php`.
* Risk Level: Medium.
* Complexity: Medium.
* Estimated Effort: 2-3 days.
* Acceptance Criteria: Supplier tracking filters are defined once; money/percent formatting is consistent; no broad table abstraction is introduced until it removes proven duplication safely.

## 4. P2 Nice To Have Improvements

### P2-01: Remove confirmed scaffold and unused files

* Issue: `Modules/Pharma/Models/Pharma.php`, `Modules/Pharma/resources/views/pharma.blade.php`, `Modules/Pharma/resources/views/components/placeholder.blade.php`, `Modules/Pharma/resources/views/livewire/placeholder.blade.php`, `Modules/Pharma/resources/views/pages/supplier-trackings/show.blade.php`, and `Modules/Pharma/readme.md` appear unused or scaffold-only.
* Root Cause: Module generator artifacts were kept after real features were added.
* Business Impact: Developers may mistake placeholders for supported extension points.
* Technical Impact: Dead files create noise and can mask route/component discovery problems.
* Proposed Solution: After route and module boot tests confirm no references, delete unused placeholders or replace `Modules/Pharma/readme.md` with real module documentation.
* Files To Change: files listed in the Issue field.
* Risk Level: Low.
* Complexity: Low.
* Estimated Effort: 0.5 day after tests exist.
* Acceptance Criteria: Confirmed unused files are removed; route/view/component discovery still passes; `Modules/Pharma/readme.md` contains useful module documentation or is removed by convention.

### P2-02: Clarify optional supplier tracking show page

* Issue: `Modules/Pharma/Http/Controllers/SupplierTrackingController.php` has `show()`, and `Modules/Pharma/resources/views/pages/supplier-trackings/show.blade.php` exists, but `Modules/Pharma/routes/web.php` has no show route and the blade mounts no component.
* Root Cause: Show flow was scaffolded but not completed.
* Business Impact: Users have no detail page despite controller/page artifacts suggesting one.
* Technical Impact: Dead code increases maintenance ambiguity.
* Proposed Solution: Confirm whether a supplier tracking detail page is required. If yes, add route, page, Livewire detail component, authorization, and tests. If no, remove the controller method and blade after route tests pass.
* Files To Change: `Modules/Pharma/Http/Controllers/SupplierTrackingController.php`, `Modules/Pharma/resources/views/pages/supplier-trackings/show.blade.php`, optional `Modules/Pharma/routes/web.php`, optional new `Modules/Pharma/Livewire/SupplierTrackings/Show.php`, optional new `Modules/Pharma/resources/views/livewire/supplier-trackings/show.blade.php`.
* Risk Level: Low.
* Complexity: Low if removed; Medium if implemented.
* Estimated Effort: 0.5 day to remove; 1-2 days to implement.
* Acceptance Criteria: No unused show artifacts remain, or a complete authorized show flow exists.

### P2-03: Improve page/icon polish after functional refactors

* Issue: `Modules/Pharma/resources/views/livewire/medicine/index.blade.php`, `Modules/Pharma/resources/views/livewire/medicine/form.blade.php`, `Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php`, `Modules/Pharma/resources/views/livewire/drug-bid-award/form.blade.php`, `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php`, and `Modules/Pharma/resources/views/livewire/supplier-trackings/form.blade.php` use many manual inline SVGs and slightly divergent UI patterns.
* Root Cause: UI was handcrafted per screen.
* Business Impact: Screens feel less consistent.
* Technical Impact: More markup to maintain.
* Proposed Solution: After security/import/performance work, standardize repeated buttons, icons, empty states, and action groups using existing shared Blade components where available.
* Files To Change: all Livewire blades listed in the Issue field; possible shared Blade components under `Modules/Shared` only if genuine cross-module reuse is confirmed.
* Risk Level: Low.
* Complexity: Medium.
* Estimated Effort: 2-4 days.
* Acceptance Criteria: UI remains functionally equivalent; repeated action markup is reduced; no new UI framework is introduced.

### P2-04: Improve Pharma module documentation

* Issue: `Modules/Pharma/readme.md` contains scaffold commands rather than purpose, routes, data tables, import/export rules, permissions, and operational notes.
* Root Cause: Scaffold README was never replaced.
* Business Impact: Future maintainers lack module-specific guidance.
* Technical Impact: Onboarding and safe operation of imports/exports are harder.
* Proposed Solution: Replace with a concise module README after canonical import/export and status decisions are confirmed.
* Files To Change: `Modules/Pharma/readme.md`, optionally link to `docs/modules/Pharma/ANALYSIS.md` and `docs/modules/Pharma/REFACTOR_PLAN.md`.
* Risk Level: Low.
* Complexity: Low.
* Estimated Effort: 0.5 day.
* Acceptance Criteria: README documents module purpose, routes, permissions, data tables, import/export entry points, and known operational constraints.

### P2-05: Add migration comments after schema decisions

* Issue: `Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php` has sparse business comments compared with later migrations.
* Root Cause: Initial medicine schema was created before the current database documentation standard.
* Business Impact: Field meanings are less clear for operators and future developers.
* Technical Impact: Schema intent is harder to preserve during refactors.
* Proposed Solution: Add comments in a follow-up migration or during a migration hygiene pass, after confirming production database compatibility and whether existing migrations may be edited in this repo.
* Files To Change: `Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php` or a new migration under `Modules/Pharma/database/migrations/`.
* Risk Level: Low.
* Complexity: Low.
* Estimated Effort: 0.5-1 day.
* Acceptance Criteria: Important medicine fields have meaningful comments; migration smoke tests pass.

## 5. Recommended Implementation Order

### Phase 1: Safety and Security

1. Fix or remove `Modules/Pharma/routes/api.php` and `Modules/Pharma/Http/Controllers/Api/PharmaController.php`.
2. Fix or remove the broken `admin.pharma.supplier-trackings.import-export` route in `Modules/Pharma/routes/web.php`.
3. Add named permissions to `Modules/Pharma/routes/web.php`, Pharma controllers, and all mutating Livewire methods.
4. Add server-side authorization for route IDs, selected IDs, deletes, imports, and exports.
5. Normalize safe error handling so raw exceptions are not flashed to users.
6. Add route/security regression tests for allowed and denied behavior.

### Phase 2: Correctness and Maintainability

1. Confirm supplier tracking status vocabulary and update validation/import/display consistently.
2. Add composite unique validation for medicines and drug bid awards.
3. Move direct model queries out of `Modules/Pharma/Livewire/DrugBidAward/Form.php`.
4. Add transactions and service-owned bulk delete operations.
5. Consolidate supplier tracking import/export on the shared foundation.
6. Plan and confirm medicine and drug bid award import/export mappings before replacing legacy CSV code.
7. Update model metadata and schema constraints only after production data impact is understood.

### Phase 3: Performance and Cleanup

1. Guard `All` pagination and replace unbounded `get()` exports.
2. Optimize medicine selectors and import lookup queries.
3. Add confirmed indexes for search/filter/sort fields.
4. Replace `container-fluid` page wrappers with the active Tailwind page container.
5. Remove confirmed scaffold files and unused show/API artifacts.
6. Polish shared UI patterns, icons, formatting helpers, and module README.

## 6. Files Change Matrix

| File Path | Change Type | Priority | Reason |
|---|---|---|---|
| `Modules/Pharma/routes/api.php` | Modify or remove route | P0 | Public broken API route. |
| `Modules/Pharma/Http/Controllers/Api/PharmaController.php` | Modify or remove | P0/P2 | Missing `index()` and scaffold-only API controller. |
| `Modules/Pharma/routes/web.php` | Modify | P0 | Add permissions and fix missing `importExport` route target. |
| `Modules/Pharma/config/module.php` | Modify | P0 | Ensure permission definitions match enforced abilities. |
| `Modules/Pharma/Http/Controllers/PharmaController.php` | Modify | P0 | Restore/enforce permission checks. |
| `Modules/Pharma/Http/Controllers/DrugBidAwardController.php` | Modify | P0 | Add permission checks. |
| `Modules/Pharma/Http/Controllers/SupplierTrackingController.php` | Modify | P0/P2 | Add permission checks; fix/remove `show`; add/remove `importExport`. |
| `Modules/Pharma/Livewire/Medicine/Index.php` | Modify | P0/P1 | Authorize actions, transaction-safe bulk delete, safe errors, pagination, import/export cleanup. |
| `Modules/Pharma/Livewire/Medicine/Form.php` | Modify | P0/P1 | Authorize save/edit IDs, unique validation, safe errors. |
| `Modules/Pharma/Livewire/DrugBidAward/Index.php` | Modify | P0/P1 | Authorize actions, transaction-safe bulk delete, safe errors, pagination, import/export cleanup. |
| `Modules/Pharma/Livewire/DrugBidAward/Form.php` | Modify | P0/P1 | Authorize save/edit IDs, remove direct model query, add unique validation. |
| `Modules/Pharma/Livewire/SupplierTrackings/Index.php` | Modify | P0/P1 | Authorize actions, remove duplicate import/export workflow, bound export/import/delete behavior. |
| `Modules/Pharma/Livewire/SupplierTrackings/Form.php` | Modify | P0/P1 | Authorize save/edit IDs, status and URL validation, service injection cleanup. |
| `Modules/Pharma/Services/MedicineService.php` | Modify | P1 | Canonical queries, import/export consolidation, bulk delete, validation invariants, bounded selectors. |
| `Modules/Pharma/Services/MedicineImportService.php` | Modify or remove | P1 | Duplicate medicine import service. |
| `Modules/Pharma/Services/DrugBidAwardService.php` | Modify | P1 | Canonical queries, import/export consolidation, bulk delete, validation invariants. |
| `Modules/Pharma/Services/SupplierTrackingService.php` | Modify | P1 | Transactions, filter query reuse, bounded export, import validation, status alignment. |
| `Modules/Pharma/Services/ImportExport.php` | Modify | P1 | Shared import/export canonical service, filters, status alignment, row validation. |
| `Modules/Pharma/Console/Commands/ImportMedicineCommand.php` | Modify | P1 | Delegate to canonical import service or document CLI-only path. |
| `Modules/Pharma/Models/Medicine.php` | Modify | P1 | Replace broad `$guarded` with explicit `$fillable`. |
| `Modules/Pharma/Models/DrugBidAward.php` | Review | P1 | Ensure fillable/casts support canonical import/export and validation. |
| `Modules/Pharma/Models/SupplierTracking.php` | Modify | P1 | Replace public `$exceptExport` pattern and align status/export metadata. |
| `Modules/Pharma/Models/Pharma.php` | Remove after confirmation | P2 | Empty scaffold model. |
| `Modules/Pharma/database/migrations/2026_05_21_145242_create_medicines_table.php` | Modify if safe or add migration | P1/P2 | Add indexes and comments. |
| `Modules/Pharma/database/migrations/2026_05_22_135028_create_drug_bid_awards_table.php` | Review/add follow-up migration | P1 | Validate constraints against service rules. |
| `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php` | Modify if safe or add migration | P1 | Status, unique key, cascade delete, indexes. |
| `Modules/Pharma/resources/views/pages/index.blade.php` | Modify | P1 | Replace `container-fluid`. |
| `Modules/Pharma/resources/views/pages/create.blade.php` | Modify | P1 | Replace `container-fluid`. |
| `Modules/Pharma/resources/views/pages/edit.blade.php` | Modify | P1 | Replace `container-fluid`. |
| `Modules/Pharma/resources/views/pages/drug-bid-award/index.blade.php` | Modify | P1 | Replace `container-fluid`. |
| `Modules/Pharma/resources/views/pages/drug-bid-award/create.blade.php` | Modify | P1 | Replace `container-fluid`. |
| `Modules/Pharma/resources/views/pages/drug-bid-award/edit.blade.php` | Modify | P1 | Replace `container-fluid`. |
| `Modules/Pharma/resources/views/pages/supplier-trackings/index.blade.php` | Modify | P1 | Replace `container-fluid`. |
| `Modules/Pharma/resources/views/pages/supplier-trackings/create.blade.php` | Modify | P1 | Replace `container-fluid`. |
| `Modules/Pharma/resources/views/pages/supplier-trackings/edit.blade.php` | Modify | P1 | Replace `container-fluid`. |
| `Modules/Pharma/resources/views/pages/supplier-trackings/show.blade.php` | Remove or complete | P2 | Empty unused page. |
| `Modules/Pharma/resources/views/pages/supplier-trackings/import-export.blade.php` | Create if route retained | P0/P1 | Dedicated import/export page if chosen. |
| `Modules/Pharma/resources/views/livewire/medicine/index.blade.php` | Modify | P1/P2 | Safe external links, guarded `All`, import/export UI updates, icon polish. |
| `Modules/Pharma/resources/views/livewire/medicine/form.blade.php` | Modify | P2 | UI polish after validation/security. |
| `Modules/Pharma/resources/views/livewire/drug-bid-award/index.blade.php` | Modify | P1/P2 | Safe links, cast-aware date formatting, guarded `All`, icon polish. |
| `Modules/Pharma/resources/views/livewire/drug-bid-award/form.blade.php` | Modify | P1/P2 | Bounded selector integration and UI polish. |
| `Modules/Pharma/resources/views/livewire/supplier-trackings/index.blade.php` | Modify | P1/P2 | Remove duplicate import/export, safe links, formatting cleanup. |
| `Modules/Pharma/resources/views/livewire/supplier-trackings/form.blade.php` | Modify | P1/P2 | Formatting cleanup, status validation UI. |
| `Modules/Pharma/resources/views/pharma.blade.php` | Remove after confirmation | P2 | Scaffold placeholder page. |
| `Modules/Pharma/resources/views/components/placeholder.blade.php` | Remove after confirmation | P2 | Scaffold placeholder component. |
| `Modules/Pharma/resources/views/livewire/placeholder.blade.php` | Remove after confirmation | P2 | Scaffold placeholder Livewire view. |
| `Modules/Pharma/readme.md` | Rewrite or remove | P2 | Scaffold notes are not useful module documentation. |
| `Modules/Pharma/Import/*` | Create if needed | P1 | Split import mapping/validation when shared service becomes too large. |
| `Modules/Pharma/Export/*` | Create if needed | P1 | Split export query/mapping/template logic when needed. |
| `tests/Feature/Pharma/*` | Create | P0/P1 | Route, authorization, Livewire, import/export, and UI behavior tests. |
| `tests/Unit/Pharma/*` | Create | P1 | Service, validation, transaction, import mapping, and calculation tests. |

## 7. Risk Control

Do not change business data semantics until confirmed:

- Do not change supplier tracking status values in production data without a mapping/backfill decision for `Modules/Pharma/database/migrations/2026_05_23_141810_create_supplier_trackings_table.php`.
- Do not change `cascadeOnDelete()` on `pharma_supplier_trackings.medicine_id` until the business confirms whether supplier history must survive medicine deletion.
- Do not replace medicine or drug bid award import behavior until sample files, header/position mapping, unique keys, null-overwrite behavior, duplicate mode, dry-run behavior, and transaction strategy are confirmed.
- Do not delete scaffold or unused files until route/view/component discovery tests prove there are no active references.
- Do not add broad cross-module abstractions for tables, formatting, or import/export unless the shared behavior is proven and compatible with `Modules/Shared`.
- Do not introduce DTOs, direct model queries in Livewire, controller business logic, Bootstrap/jQuery patterns, or app-level business classes outside `Modules/Pharma`.
- Do not edit existing migrations in a deployed environment unless the project confirms migrations are not yet applied; otherwise create forward-only follow-up migrations.
- Do not expose raw exception messages, filesystem paths, SQL errors, import debug internals, or unauthorized record existence in user-facing responses.
- Do not queue imports/exports until authorization context, progress reporting, retry/idempotency, and failure reporting are defined.
