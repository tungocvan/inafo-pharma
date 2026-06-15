# Project Overview

This document is the source of truth for AI-assisted development in the INAFO Pharma Laravel admin project. It consolidates every Markdown prompt currently stored in `docs/prompts/`, removes duplicate rules, resolves conflicts by the newest applicable version, and preserves the architecture decisions that must govern future work.

## Governing Stack

- Laravel 12.
- Livewire 3.1, class-based components only.
- Tailwind CSS 4.
- `nwidart/laravel-modules`.
- MySQL.
- Admin authentication middleware: `auth:admin`.
- Main admin layout: `Admin::layouts.master`.
- Excel import/export: `rap2hpoutre/fast-excel:^5.7`.
- Blade components are preferred for reusable UI.
- Do not use Bootstrap.
- Do not use jQuery unless the existing implementation makes it unavoidable.
- Do not use inline CSS when Tailwind can express the design.

## Global Engineering Laws

```text
SIMPLE > OVER-ENGINEERING
SERVICE LAYER = BUSINESS LOGIC
NO DTO
```

- All module business code belongs under `Modules/<ModuleName>/`.
- Do not create module business models, controllers, or services under `app/Models`, `app/Http`, or `app/Services`.
- Extending a framework/base class such as `App\Http\Controllers\Controller` is allowed.
- Use validated arrays between Livewire and services. Do not introduce DTOs unless a future standard explicitly replaces the active no-DTO rule.
- Do not guess unclear business rules, mappings, relationships, derived-field behavior, destructive import modes, or unique keys.
- Prefer a simple design that satisfies the known business rules over speculative abstractions.
- Production code must be complete, namespaced, maintainable, and free of pseudocode and fake data unless fake data is explicitly requested.

## Source Precedence And Conflict Resolution

Rules are resolved by semantic scope first and version second:

1. Import/export work is governed by **Import / Export Module Laravel v1.5 FINAL**.
2. File-driven analysis is governed by **File-Driven Analysis v7.1 REFINED**.
3. General module implementation is governed by **Laravel Module + Livewire v6.1 FINAL**.
4. Admin presentation is governed by **Laravel Admin UI v1.1**.

Resolved conflicts:

| Topic | Conflict | Active resolution |
|---|---|---|
| UI prompt filename | Filename says v1.0 while document heading says v1.1 | The content heading is authoritative: Admin UI v1.1 is active. |
| Analysis prompt filename | Filename says v7 while document heading/version block says v7.1 | File-Driven Analysis v7.1 REFINED is active. |
| Validation ownership | General module rules place form validation in Livewire; import rules require per-row validation in import classes/services | Livewire validates UI/form/upload state. Services or module import validators validate mapped import rows and business invariants. |
| Import/export logic location | General rule says logic is in Service; v1.5 allows dedicated `Import/` and `Export/` classes | The module service remains the entry point/orchestrator. Detailed mapping, normalization, validation, query, and template logic may be delegated to module import/export classes. |
| Build order | General module output starts with migration; import/export v1.5 starts with module import/export service after schema already exists | Use the general order for new modules. Use the v1.5 order for adding import/export to an existing schema. |
| Confirmation workflow | General workflow has five analysis steps; file-driven and import/export prompts define more detailed gates | Use the most specific workflow. File-driven or import/export tasks must complete their detailed analysis and stop for confirmation before implementation. |
| Relationship inference | Architecture needs relationships, but file analysis forbids automatic assumptions | Detect and propose relationships with confidence/evidence; do not implement uncertain relationships without confirmation. |
| Derived fields | Analysis asks whether to store or calculate at runtime; implementation rules say Service calculates them | Confirm persistence strategy first. In all cases, calculation belongs in the Service; imports must not trust spreadsheet formula values. |
| Pagination `All` | UI requires an `All` option while performance rules prohibit loading large datasets | Keep `All` in the standard selector, but guard, limit, warn, or disable it when dataset size makes it unsafe. |
| Code comments | Prompts request replace-friendly comments, while maintainability discourages noise | Use short structural or non-obvious comments only; do not narrate self-explanatory code. |

# Architecture Standards

## Laravel Module Standard

### Folder structure

Standard module layout:

```text
Modules/<ModuleName>/
├── Config/
│   └── config.php
├── Database/
│   ├── Migrations/
│   └── Seeders/
├── Models/
│   └── <Feature>.php
├── Services/
│   └── <Feature>Service.php
├── Http/
│   └── Controllers/
│       └── <Feature>Controller.php
├── Livewire/
│   └── <Feature>/
│       ├── Index.php
│       └── Form.php
├── Resources/
│   └── views/
│       ├── pages/<feature-slug>/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       └── livewire/<feature-slug>/
│           ├── index.blade.php
│           └── form.blade.php
├── Routes/
│   └── web.php
├── Providers/
│   └── <ModuleName>ServiceProvider.php
└── module.json
```

Required namespaces:

```php
Modules\<ModuleName>\Models
Modules\<ModuleName>\Services
Modules\<ModuleName>\Http\Controllers
Modules\<ModuleName>\Livewire
```

Do not place module classes under `App\Models`, `App\Services`, or `App\Http\Controllers\<Module>`.

### Route flow

Mandatory flow:

```text
Route
→ Controller
→ Page Blade
→ Livewire PHP
→ Livewire Blade
→ Service
→ Model
→ Database
```

For import/export, the expanded flow is:

```text
Route
→ Controller
→ Page Blade
→ Shared Livewire Import/Export Panel
→ Module ImportExport Service
→ Module Import/Export Classes
→ Shared Base ImportExport Service
→ Model
→ Database
```

Routes define only URL, route name, middleware, and controller action.

- Admin URLs live under `admin/<module-slug>`.
- Route names use `admin.<module-slug>.<feature-slug>.*`.
- Default middleware is `web`, `auth:admin`.
- Route names and slugs must be explicit and consistent.

### Controller rules

Controllers are thin adapters. They may:

- Return a view.
- Redirect.
- Pass simple scalar identifiers or display parameters to a page view.

Controllers must not:

- Query the database or call a Model.
- Implement business rules or data transformations.
- Validate business data.
- Open transactions.

### Page Blade rules

Page Blade is a layout shell that:

- Extends `Admin::layouts.master`.
- Defines the title and page-level layout.
- Mounts the relevant Livewire component.

Page Blade must not query the database, call a Model or Service, contain business logic, or implement the feature table/form directly when that behavior belongs in Livewire.

An edit page passes a scalar ID:

```blade
@livewire('<module-slug>.<feature-slug>.form', ['id' => $id])
```

The Livewire form accepts `?int $id = null` so dependency resolution does not interpret the ID as an injectable service.

### Livewire rules

Livewire owns:

- UI state and lifecycle.
- Input binding.
- Form and UI validation.
- UI actions and confirmation state.
- Calling services.
- Rendering Livewire Blade views.

Livewire must not:

- Query Models directly.
- contain core business logic, complex domain processing, or transactions.
- Implement import/export mapping or persistence.

Inject services through `boot()` where appropriate:

```php
protected <Feature>Service $service;

public function boot(<Feature>Service $service): void
{
    $this->service = $service;
}
```

Organize component code as:

```text
STATE
LIFECYCLE
VALIDATION
ACTIONS
RENDER
```

Use `wire:model.live` by default. Do not use `wire:model.defer` as the default binding strategy.

### Service rules

The Service layer is mandatory and is the only owner of business logic.

Services own:

- Database queries.
- Search, filter, sort, and pagination.
- Create, update, delete, and bulk delete.
- Transactions.
- Data normalization before persistence.
- Derived fields and formulas.
- Import/export orchestration.
- Domain-level validation and invariants that are not merely UI input rules.

Services must not:

- Return views.
- Contain UI/Tailwind classes.
- Read `request()` directly.
- Depend on Livewire component state.
- Echo, print, or directly render a response.

Service methods accept arrays/scalars and return Models, collections, paginators, arrays, or explicit result objects already established by the codebase. Under the active standard, do not create DTOs.

Use transactions for multi-write operations and any write whose failure could leave inconsistent state. A simple read does not need a transaction.

### Model rules

Models are ORM definitions only.

- Models live in `Modules/<ModuleName>/Models`.
- Define `$table` explicitly when convention is insufficient or clarity benefits.
- Define `$fillable`.
- Define `$casts`.
- Define Eloquent relationships clearly.
- Keep complex business logic, imports, exports, and transactions out of Models.
- Import/export defaults may read `$fillable`.
- Sensitive export exclusions may be declared through `$exceptExport` plus a safe accessor such as `getExceptExport()`.

### Migration rules

- Use accurate table and column names.
- Choose nullable constraints from real business rules.
- Add timestamps unless the table has a documented reason not to.
- Use `decimal`, never `float`, for money.
- Use JSON for flexible, shallowly queried data when it avoids unnecessary table splitting.
- Do not over-normalize when the confirmed requirement is a single table.
- Add indexes to fields used frequently for search, filter, sort, joins, or uniqueness.
- Add foreign keys/relationships only after the relationship is understood.
- Add concise `comment()` values to the table and important columns.
- Codes, statuses, money, formulas, JSON, dates, URLs, and creator/updater fields require clear business comments.
- Avoid meaningless comments such as `data`, `info`, or `note field`.

## Import Export Standard

### Import architecture

Before implementation, require and inspect:

1. A sample or real Excel file.
2. The relevant migration.
3. The relevant Model, including `$table`, `$fillable`, `$casts`, relationships, and `$exceptExport` when present.
4. Confirmation of header-based mapping or positional A/B/C mapping.

Do not write import/export code until the inputs have been analyzed and the user has confirmed the proposed mapping and behavior.

Every module exposes import/export through:

```text
Modules/<ModuleName>/Services/ImportExport.php
```

This class extends `Modules\Shared\Services\ImportExport\BaseImportExportService` and declares or overrides, as applicable:

```php
protected function modelClass(): string;
protected array $requiredHeaders = [];
protected array $uniqueBy = [];
protected array $rules = [];
protected array $headerAliases = [];
protected function normalizeRow(array $row): array;
protected function mapExportRow(Model $model): array;
protected function templateSampleRow(): array;
```

Optional hooks include filtered export rows and pre-persistence processing.

For a simple feature, the service may contain the module-specific rules. Split it when it exceeds roughly 200-300 lines or contains several independent responsibilities:

```text
Modules/<ModuleName>/
├── Import/
│   ├── <Feature>Import.php
│   ├── RowMapper.php
│   ├── RowNormalizer.php
│   └── RowValidator.php
├── Export/
│   ├── <Feature>Export.php
│   ├── ExportQuery.php
│   ├── ExportMapper.php
│   └── TemplateBuilder.php
└── Services/
    └── ImportExport.php
```

Do not split a small implementation merely to match the maximum structure. Import classes never own export behavior, and export classes never own import behavior.

### Export architecture

Export must support the applicable subset of:

- Current records.
- Active filters.
- Selected IDs.
- One or multiple sheets.
- A professional import template.

Store generated files under:

```text
storage/app/public/exports
```

Export defaults to Model `$fillable`, not every database column. Remove fields declared in `$exceptExport`. Sensitive values such as passwords, tokens, internal notes, `created_by`, and `updated_by` should be excluded. Derived fields not in `$fillable` may be added explicitly by `mapExportRow()` or an equivalent extension point.

Export templates include:

- Canonical headers.
- Sample data.
- Required and optional field notes.
- Valid value lists where useful.
- Warnings that derived/formula fields are system-calculated and not importable.

### Shared ImportExport foundation

Reuse:

```text
Modules/Shared/Services/ImportExport/
├── BaseImportExportService.php
└── Concerns/
    ├── HandlesExportStorage.php
    ├── HandlesHeaderMapping.php
    ├── HandlesImportReport.php
    └── NormalizesImportRows.php
```

Do not duplicate these cross-module concerns:

- Import file validation.
- Header normalization and aliases.
- String, number, money, date, and boolean normalization.
- Basic import/export loops.
- Reports and debug reports.
- Export paths and public download URLs.

Use the shared UI:

```blade
@livewire('shared.import-export.panel', [
    'serviceClass' => \Modules\<ModuleName>\Services\ImportExport::class,
    'title' => 'Import / Export <Name>',
    'description' => 'Import data from Excel or export current data.',
])
```

Never pass a Model class directly to the shared panel. The panel owns upload controls, mode selection, dry-run state, service calls, reports, error tables, loading, and disabled states. It does not own queries, row validation, persistence, mapping, or formulas.

### Header mapping

Header aliases may map multiple real-world labels to one database field. Normalize headers by trimming, lowercasing, converting to `snake_case`, and supporting Vietnamese labels where required.

Example:

```php
protected array $headerAliases = [
    'full_name' => ['full_name', 'name', 'ho_ten', 'họ tên', 'ten_day_du'],
];
```

Unknown or ambiguous mappings must be reported and confirmed, not guessed.

### Column mapping

Support position-based mapping when headers are unstable:

```php
protected array $columnMapping = [
    'A' => 'working_date',
    'B' => 'medicine_name',
    'C' => 'registration_number',
];
```

- `$columnMapping` takes precedence over `$headerAliases`.
- Do not validate required Excel header names in positional mode.
- Validate required database fields after positional mapping.
- Read headers for diagnostics even when they do not control mapping.
- Confirm whether a title row exists and which row begins the data.

### Validation

Import validation occurs after mapping and normalization.

- Define a confirmed unique key; do not default to spreadsheet `id`.
- Supported modes are `create_only`, `update_or_create`, `skip_duplicate`, and `replace`.
- Never select `replace` without explicit confirmation.
- Offer dry-run where data risk warrants it.
- Decide whether partial import is allowed.
- Do not overwrite important existing fields with `null` merely because a spreadsheet cell is blank unless that behavior is confirmed.
- Do not import calculated/formula values; recalculate them in the Service.
- Validate each row with the module-specific rules and return sheet, row, column, value, and reason for errors.

Normalize:

- Strings: trim and convert empty strings to `null` where appropriate.
- Money/numbers: accept common comma, dot, and space separators and persist clean numeric values.
- Dates: support confirmed formats such as `dd/mm/yyyy`, ISO dates, and Excel serial dates.
- Booleans: support confirmed equivalents such as `1/0`, `true/false`, `yes/no`, `có/không`, and `active/inactive`.

### Transactions

- Persistence belongs in the Service/import layer.
- Use transactions to prevent partial inconsistent writes.
- Choose all-or-nothing versus partial row success during analysis and document it.
- Dry-run must perform mapping, normalization, validation, and reporting without persistent writes.
- Never truncate, delete old data, replace records, or perform destructive overwrite behavior without explicit confirmation.

The import result must include:

```text
success
total_rows
success_rows
error_rows
skipped_rows
errors[]: sheet, row, column, value, reason
debug: mode, dry_run, sheets, sheet_counts, headers
```

Log system failures with Laravel logging and do not expose production stack traces in the UI.

## Admin UI Standard

### Form design

Admin pages use a clean SaaS layout:

```blade
<div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
```

Cards use:

```blade
<div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
```

Page headers contain a clear title, short description, optional primary action, and responsive layout.

Forms:

- Group related fields into cards/sections with headings and short descriptions.
- Use `grid grid-cols-1 gap-5 md:grid-cols-2`.
- Give long fields such as addresses and descriptions full width with `md:col-span-2`.
- Place validation errors directly below the relevant field.
- Keep input and button heights consistent.
- Use Indigo for primary actions, Gray for neutral UI, Green for success/active, Red for danger/error, and Amber for warning/pending.
- Avoid excessive color and inconsistent per-form styling.

Standard input classes:

```text
w-full rounded-xl border border-gray-300 px-4 py-3 mt-1 text-sm text-gray-900
placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100
disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500
```

### Tables

Tables require:

- Clear headers.
- Responsive horizontal overflow.
- Explicit actions.
- Status badges where applicable.
- Empty state.
- Loading state for long actions.
- Pagination for non-trivial lists.

Use:

```blade
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            ...
        </table>
    </div>
</div>
```

Bulk delete, when requested, uses `selectedIds`, page-level select-all, per-row checkboxes, confirmation, a selected-delete action, and resets selection after completion.

Dangerous actions must show clear intent, use danger styling, and require confirmation where accidental execution is plausible.

### Pagination

Server-side pagination is mandatory for list pages.

```php
public string|int $perPage = 10;
public array $perPageOptions = [10, 25, 50, 100, 'All'];
```

- Default to 10.
- Reset to page one when `perPage` changes.
- Services, not Livewire, build and execute pagination queries.
- Render links only when `perPage !== 'All'`.
- `All` may return a collection only for safe dataset sizes. Warn, cap, or disable it for large data.

### Currency formatting

- Display money with readable thousands separators.
- Keep a formatted UI value separate from the clean numeric persistence value.
- Reject invalid characters.
- Use VND-style formatting for Vietnamese money unless another currency is specified.
- Show `%` clearly for percentages.
- Never store formatted strings or separators in the database.
- Validate the cleaned value as numeric.

### Select Search components

Use `x-select-search` for searchable comboboxes and relationship selectors when the shared component can satisfy the requirement.

- Support `wire:model.live`.
- Provide a clear placeholder.
- Support search/filter for long lists.
- Show validation errors.
- Support disabled state.
- Remain responsive and match standard input styling.

A normal `<select>` is acceptable for a short static list. When the requirement explicitly says combobox, searchable select, or searchable dropdown, `x-select-search` is mandatory.

## Database Standard

### Naming conventions

- Use clear module, model, table, column, route, and relationship names.
- Use consistent module namespaces and feature slugs.
- Store clean values, not display formatting.
- Status values must be documented and validated.
- Use explicit business comments on important schema fields.

### Relationships

- Detect candidate one-to-many, many-to-many, and self-referential relationships during analysis.
- Treat `*_id` patterns as suggestions, not proof.
- Confirm uncertain relationships before migration/model implementation.
- Use enums only for small, stable sets of values.
- Use tables for dynamic values or values with their own relationships/metadata.
- Use JSON for flexible, shallowly queried lists where normalization would add little value.

### Indexes

Index:

- Unique business keys.
- Foreign keys.
- Frequently searched text/code fields where index selectivity is useful.
- Common filter and sort fields.
- Join columns.

Do not add indexes blindly; align indexes with actual query patterns and avoid redundant indexes.

### Transactions

- Transactions belong in Services.
- Use them for create/update/delete flows involving multiple writes, bulk operations, and imports.
- Do not put transactions in Controllers, Blade, Models, or Livewire.
- Destructive operations require explicit intent and confirmation.

## Security Standard

### Authorization

- Admin routes use `web` and `auth:admin`.
- Authentication alone is not sufficient for sensitive features; preserve or add the project's established policies, gates, permissions, or role checks.
- Authorization checks belong at the route/controller/Livewire boundary using existing project conventions, while business enforcement remains in the Service where required.
- Never trust hidden fields, route IDs, selected IDs, import files, or client-side state as authorization.

### Validation

- Livewire validates interactive form input and file upload constraints.
- Services enforce business invariants.
- Import validators validate normalized rows.
- Unique validation must ignore the current record during edit.
- Validate email, URL, date, number, enum/status, and cleaned money values by type.
- Show user-friendly field-level messages without leaking internal exception details.

### Dangerous actions

- Confirm deletes, bulk deletes, replace imports, truncation, and destructive overwrites.
- Do not silently replace existing data.
- Do not overwrite important fields with blank spreadsheet values without a confirmed rule.
- Log system failures and show a safe user-facing error.
- Use null-safe access and UI fallbacks where missing related data is valid.

### File uploads

- Validate import file type, size, readability, and expected workbook structure before processing.
- Keep upload handling in the shared Livewire panel and processing in Services/import classes.
- Do not trust filenames, headers, formulas, MIME metadata, or cell values.
- Store exports in the defined public export directory and generate download URLs through the shared foundation.
- Do not show stack traces in production.

## Performance Standard

### Eager loading

- Prevent N+1 queries.
- Use `with()` for relations displayed by lists/forms.
- Load only relationships and columns needed for the current operation.

### Query optimization

- Use server-side pagination.
- Search, filtering, sorting, and pagination belong in Services.
- Use joins or relationship-aware queries when sorting/filtering by related data.
- Do not query inside loops.
- Index fields that support real search/filter/sort/join patterns.
- Do not load a large full dataset into memory.
- Treat pagination `All`, large imports, and multi-sheet exports as explicit memory risks.

### Caching

The source prompts define no mandatory cache backend or cache duration. Apply caching only when:

- The data is expensive to compute or query.
- The invalidation rule is explicit.
- Stale data behavior is acceptable and documented.

Do not use caching to conceal inefficient queries.

### Queue usage

The source prompts allow Seeder or Job import strategies but do not mandate queues for every task.

- Use synchronous processing for small, bounded operations.
- Use queued jobs for large imports/exports or work likely to exceed request/runtime limits.
- A queued flow must preserve progress/error reporting, logging, authorization context, idempotency, and the same Service/import/export boundaries.
- Decide queue usage during analysis before implementation.

## Testing Standard

### Required tests

Every implementation must include tests proportional to its risk. At minimum, test the changed behavior and architecture-critical paths:

- Route authentication/authorization and expected page responses.
- Livewire form validation, create/update/delete actions, loading/error outcomes where testable, and service invocation behavior.
- Service search/filter/sort/pagination, including reset behavior and safe handling of `All`.
- Model casts and relationships that affect behavior.
- Database constraints, unique keys, decimal persistence, and transaction rollback.
- Derived fields recalculated by the Service.
- Import header aliases and A/B/C mapping.
- Import normalization for money, dates, booleans, and empty strings.
- Import modes, duplicate handling, null-overwrite rules, dry-run, partial/all-or-nothing behavior, and error reports.
- Export `$fillable` defaults, `$exceptExport`, filters, selected IDs, templates, and derived export-only columns.
- Dangerous action confirmation and prevention of unauthorized/destructive execution.
- N+1-sensitive list queries when a feature introduces or changes relationship rendering.

Use unit tests for isolated normalization/mapping/business rules and feature/Livewire tests for integrated behavior.

### Coverage expectations

- The prompts define no numeric coverage percentage; do not invent one.
- New or changed business rules require direct positive and negative tests.
- Bug fixes require a regression test when technically feasible.
- High-risk import, transaction, authorization, and destructive workflows require failure-path tests, not only happy paths.
- A task is not complete until relevant tests pass, or the final report explicitly states what could not be run and why.

## AI Development Workflow

### Step 1: Analysis

Before coding:

- Read the relevant repository files and existing conventions.
- Analyze business requirements, current schema, models, UI, and data inputs.
- For file-driven work, assess missing values, duplicates, invalid formats, inconsistent values, and assign Low/Medium/High data risk.
- Extract Excel sheets, headers, samples, candidate types, nullability, uniqueness, enum candidates, date formats, numeric patterns, and formulas.
- Extract Word/document form structure, UI type, business rules, and UX behavior.
- Map Excel/document fields to database fields with confidence; highlight mismatches.
- Detect derived fields and ask whether they are persisted or runtime-only.
- Propose normalization, tables, relationships, enum-versus-table choices, module naming, and import strategy.
- Do not guess unresolved mappings or business rules.

### Step 2: Refactor Plan

Produce a scoped plan that:

- Identifies files to create and modify.
- Preserves module boundaries and the mandatory architecture flow.
- Separates UI state, business logic, persistence, and shared import/export concerns.
- Identifies reusable existing components/services before adding abstractions.
- Calls out migrations, compatibility risks, destructive changes, performance risks, and test impact.
- Avoids unrelated refactors.

### Step 3: Rebuild Spec

Before implementation, define:

- Confirmed schema, fields, relationships, indexes, comments, and casts.
- Route names and page/component flow.
- Form/list behavior and UI states.
- Validation and authorization rules.
- Derived-field ownership.
- Import mapping mode, unique key, mode, dry-run, null-overwrite behavior, transaction strategy, and export columns.
- Queue/caching decisions where relevant.
- Acceptance criteria and required tests.

For new modules, file-driven builds, and import/export tasks, stop at the confirmation gate. Implementation begins only after the user confirms the mapping, database design, module, derived fields, and import strategy.

### Step 4: Implementation

For a new module, build in this order:

```text
1. Migration
2. Model
3. Service
4. Route
5. Controller
6. Page Blade
7. Livewire PHP
8. Livewire Blade
```

For import/export added to an existing module:

```text
1. Module ImportExport service and any Import/Export classes
2. Page Blade mounting shared.import-export.panel
3. Route, if missing
4. Controller, if missing
5. Additional Livewire/page integration, if required
6. Tests and operational instructions
```

Implementation rules:

- Follow existing project patterns where they do not conflict with this document.
- Keep changes scoped.
- Use full namespaces and imports.
- Do not bypass Services.
- Include responsive, empty, loading, disabled, validation, and error states as applicable.
- Use concise comments only for structure or non-obvious logic.

### Step 5: Testing

- Run focused tests first, then the relevant broader suite.
- Run formatting/static analysis tools already configured by the repository.
- Inspect migration and transaction behavior.
- Verify UI states and pagination.
- Test import/export with representative valid, invalid, duplicate, blank, formula, and locale-formatted values.
- Report test commands and outcomes.
- Do not claim completion if required verification was skipped without explanation.

## Active Standards

1. **Laravel Module + Livewire v6.1 FINAL**
   - General Laravel module architecture, Service enforcement, database rules, pagination, validation, performance, UI integration, and implementation workflow.
2. **Laravel Admin UI v1.1**
   - Admin layout, forms, inputs, buttons, tables, empty/loading/error states, searchable selects, currency display, responsive behavior, and visual consistency.
3. **File-Driven Analysis v7.1 REFINED**
   - Analysis-first workflow, data quality, Excel/Word mapping, derived fields, normalization, module naming, import strategy, confirmation gate, no DTO.
4. **Import / Export Module Laravel v1.5 FINAL**
   - Shared import/export foundation, shared Livewire panel, module-specific service/classes, mapping modes, normalization, validation, safe transactions, reports, export rules, and confirmation workflow.
5. **Cross-standard architecture law**
   - Module-first code, class-based Livewire, thin Controllers, shell Page Blades, Service-owned business logic, clean Models, validated-array data flow, no DTO, and no Service bypass.

## Deprecated Standards

### Version history summary

| Version/reference | Status | Replaced by / note |
|---|---|---|
| Laravel Admin UI v1.0 filename label | Deprecated label | The file content declares v1.1; v1.1 governs. |
| File-Driven Analysis v7 filename label | Deprecated label | The file content and version block declare v7.1 REFINED; v7.1 governs. |
| Earlier module prompt versions before v6.1 | Replaced | v6.1 FINAL is the active module standard. |
| Earlier import/export prompt versions before v1.5 | Replaced | v1.5 FINAL is the active import/export standard. |
| Direct Model injection into `shared.import-export.panel` | Deprecated architecture | Pass module `serviceClass` instead. |
| Per-module duplicated import/export UI | Deprecated architecture | Use `shared.import-export.panel` unless a confirmed requirement cannot be supported. |
| Per-module copies of shared mapping/normalization/report/storage logic | Deprecated architecture | Reuse `Modules/Shared/Services/ImportExport`. |
| Monolithic import/export services over roughly 200-300 lines with independent responsibilities | Deprecated design | Keep the service as a thin orchestrator and split module-specific Import/Export responsibilities. |
| DTO-based module data flow | Inactive | Use `validated array → Service → Model`. |
| Model queries in Livewire or Blade | Forbidden legacy pattern | Query through Services. |
| Business logic in Controllers, Blade, Livewire, or Models | Forbidden legacy pattern | Business logic belongs in Services. |
| `wire:model.defer` as the default | Deprecated binding default | Use `wire:model.live`. |
| Float or formatted strings for money | Deprecated data pattern | Use clean decimal values and format only in UI/export presentation. |
| Unconfirmed `replace`, truncate, destructive overwrite, or spreadsheet-ID matching | Forbidden legacy behavior | Analyze risk and require explicit confirmation and a business unique key. |

Future prompt documents must declare a version and scope. A newer rule supersedes this document only when it explicitly replaces an active standard; otherwise, this consolidated document remains authoritative.
