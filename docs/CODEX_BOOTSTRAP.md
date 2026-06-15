# Codex Repository Bootstrap

This file defines how Codex must work in the INAFO Pharma repository. Read and apply it before analyzing, changing, or generating code.

## Instruction Priority

When instructions conflict, the higher-priority document wins:

1. `docs/CODEX_BOOTSTRAP.md`
2. `docs/AI_PROJECT_CONTEXT.md`
3. `ROADMAP.md`
4. `docs/modules/<ModuleName>/ANALYSIS.md`
5. `docs/modules/<ModuleName>/REFACTOR_PLAN.md`

Additional rules:

- User instructions define the requested outcome, but implementation must still respect the architecture and safety rules above.
- Module documents refine a task only when they do not conflict with higher-priority documents.
- Existing code is evidence of current behavior, not automatically the desired architecture.
- Do not copy a legacy pattern merely because it already exists.
- If two rules at the same priority conflict, use the newer explicit version and report the decision.
- Do not silently resolve an ambiguity that could change business behavior, data, security, or architecture.

Known conflict resolutions:

- Use validated arrays, not DTOs. The no-DTO rule in `AI_PROJECT_CONTEXT.md` overrides roadmap references to validated DTOs.
- New admin UI follows Tailwind CSS 4 and Admin UI v1.1. The roadmap's Bootstrap/AdminLTE inventory describes current repository state, not the target standard.
- Import/export follows the shared v1.5 architecture even when legacy modules contain custom implementations.

## Mandatory Reading Before Coding

For every coding task, read:

1. This file completely.
2. `docs/AI_PROJECT_CONTEXT.md` completely.
3. Root `ROADMAP.md`, especially current P0/P1 priorities and dependencies.
4. `docs/modules/<ModuleName>/ANALYSIS.md`, if it exists.
5. `docs/modules/<ModuleName>/REFACTOR_PLAN.md`, if it exists.
6. The module's `module.json`, routes, provider, relevant Models, Services, Livewire classes, Controllers, migrations, views, and tests.
7. Shared classes/components used by the affected feature.

Before editing:

- Inspect the working tree and preserve unrelated user changes.
- Identify the canonical module owner of the domain.
- Trace the current request flow and all affected callers.
- Search for duplicate implementations and cross-module dependencies.
- Check existing tests, authorization, validation, transactions, and query behavior.
- Determine the relevant roadmap item and whether unresolved P0 security work affects the task.

If a required module document does not exist, continue using repository evidence and higher-priority standards. Do not invent its contents.

## Architecture Rules

Global laws:

```text
SIMPLE > OVER-ENGINEERING
SERVICE LAYER = BUSINESS LOGIC
NO DTO
```

Required application flow:

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

Layer responsibilities:

- **Route:** URL, name, middleware, and controller action only.
- **Controller:** return views, redirect, and pass simple scalar parameters only.
- **Page Blade:** layout shell that mounts Livewire.
- **Livewire:** UI state, input validation, UI actions, and Service calls.
- **Service:** queries, business rules, transformations, transactions, derived fields, and persistence.
- **Model:** ORM configuration, fillable fields, casts, scopes already established by the project, and relationships.
- **Migration:** schema, constraints, indexes, comments, and correct data types.

Forbidden:

- Database or Model queries in Blade, Controllers, or Livewire.
- Business logic in Controllers, Blade, Livewire, or Models.
- Transactions outside Services/import persistence classes.
- Direct `request()` access inside Services.
- Service dependencies on Livewire state or UI classes.
- New DTOs.
- Bypassing the Service layer.
- Pseudocode, incomplete namespaces/imports, or unrequested fake data in production code.

Use `wire:model.live` by default. Do not use `wire:model.defer` as the default binding strategy.

## Module Boundaries

All module business code belongs under:

```text
Modules/<ModuleName>/
```

Do not add module business code to:

```text
app/Models
app/Http
app/Services
```

Extending shared framework classes such as `App\Http\Controllers\Controller` is allowed.

Required module namespaces:

```php
Modules\<ModuleName>\Models
Modules\<ModuleName>\Services
Modules\<ModuleName>\Http\Controllers
Modules\<ModuleName>\Livewire
```

Boundary rules:

- A business concept has one canonical owning module, Model, and Service.
- `Admin` is a presentation shell; domain modules own their business behavior.
- Reuse `Modules/Shared` only for genuinely cross-module infrastructure.
- Do not create circular module dependencies.
- Do not duplicate Models, Services, Livewire components, or import/export foundations.
- During refactoring, migrate callers before deleting a duplicate implementation.
- Keep module manifests, providers, routes, migrations, and Livewire aliases consistent.

## Security And Roadmap Gates

Roadmap P0 work takes precedence over feature cleanup and broad refactoring.

For privileged or destructive behavior:

- Require named permissions in addition to `auth:admin`.
- Enforce ownership/authorization server-side for records, downloads, imports, exports, and every mutating action.
- Never trust hidden UI controls, route IDs, selected IDs, table names, file paths, commands, or client state.
- Do not allow browser-provided arbitrary Artisan commands, shell commands, executable paths, SQL, table identifiers, or backup paths.
- Use explicit allowlists, fixed arguments, timeouts, audit logs, and confirmation gates.
- Validate backup paths and table identifiers against server-controlled metadata.
- Use Symfony Process argument arrays rather than shell command strings.
- Do not expose credentials in source, logs, command lines, process lists, or user-facing exceptions.
- Destructive actions require explicit confirmation, authorization, transactions where applicable, and audit records.
- Fail closed when required secrets or permissions are absent.

Do not expand or expose System administration features in production while applicable Phase 0 controls remain unresolved.

## Import/Export Rules

Import/export follows v1.5 and the shared foundation.

Before implementation, require and analyze:

1. Sample or real Excel file.
2. Relevant migration.
3. Relevant Model with `$table`, `$fillable`, `$casts`, relationships, and `$exceptExport`.
4. Confirmed mapping mode: header aliases or positional A/B/C mapping.
5. Unique key, import mode, dry-run behavior, null-overwrite behavior, transaction strategy, and derived fields.

Stop for confirmation before writing import/export code when these decisions are not already approved.

Required flow:

```text
Page Blade
→ shared.import-export.panel
→ Module ImportExport Service
→ Module Import/Export Classes when needed
→ Shared BaseImportExportService
→ Model
→ Database
```

Rules:

- Pass `serviceClass` to `shared.import-export.panel`; never pass a Model directly.
- Reuse `Modules/Shared/Services/ImportExport`.
- Do not copy shared file validation, normalization, header mapping, reporting, storage, or download logic into modules.
- Keep `Modules/<ModuleName>/Services/ImportExport.php` as the module entry point.
- Split mapping, normalization, validation, export query, export mapping, and template building when the service becomes large or has independent responsibilities.
- `$columnMapping` takes precedence over `$headerAliases`.
- Map and normalize before validating rows.
- Confirm a business unique key; do not assume spreadsheet `id`.
- Supported modes are `create_only`, `update_or_create`, `skip_duplicate`, and explicitly confirmed `replace`.
- Never truncate, replace, delete, or overwrite important fields with null without explicit confirmation.
- Do not import calculated/formula values; calculate them in the Service.
- Dry-run performs full analysis and validation without persistent writes.
- Large imports/exports must use bounded iteration and queues when request-time processing is unsafe.
- Reports include totals, successes, errors, skipped rows, row-level details, and debug metadata.
- Export defaults to `$fillable` minus `$exceptExport`.
- Store generated exports through the shared storage foundation; protect sensitive or temporary files appropriately.
- Log internal failures without returning stack traces or raw exception text.

## Admin UI Rules

New admin UI uses:

- Laravel Blade.
- Livewire 3.1 class-based components.
- Tailwind CSS 4.
- `Admin::layouts.master`.
- Admin UI v1.1.

Required UI behavior:

- Clean, responsive SaaS admin layout.
- Standard page container and rounded card styling.
- Clear page title, description, and primary action.
- Field-level validation messages.
- Empty states for empty lists.
- Loading and disabled states for save, delete, import, and export actions.
- Responsive tables with `overflow-x-auto`.
- Server-side pagination with `10`, `25`, `50`, `100`, and guarded `All`.
- `resetPage()` when pagination inputs or filters require it.
- Searchable relationship/combobox fields use `x-select-search`.
- Buttons and inputs use consistent height and visual states.
- Dangerous actions use danger styling and confirmation.

Money rules:

- Display readable thousands separators.
- Store clean decimal values, never formatted strings.
- Never use `float` for money.
- Validate the cleaned numeric value.

Do not introduce Bootstrap, jQuery, inline CSS, or a second UI pattern into new work unless a higher-priority task explicitly requires compatibility with an existing screen. Isolate unavoidable legacy compatibility and do not treat it as the new standard.

## Refactoring Workflow

### 1. Analysis

- Read mandatory documents and affected code.
- Identify current behavior, canonical ownership, dependencies, duplicates, security risks, data risks, and test gaps.
- Trace Route through Database.
- Compare current behavior with the roadmap and active standards.
- Do not change behavior based on assumptions.

### 2. Refactor Plan

- Define exact files to create, modify, migrate, and eventually remove.
- Separate behavior-preserving work from intended behavior changes.
- Document authorization, validation, transaction, migration, performance, and compatibility risks.
- Define caller migration order and rollback considerations.
- Keep scope narrow and avoid unrelated cleanup.

### 3. Rebuild Spec

- Confirm schema, relationships, indexes, casts, routes, UI states, authorization, validation, Service contracts, derived fields, import/export behavior, and acceptance tests.
- For new modules, file-driven builds, destructive changes, and import/export, stop at the confirmation gate when decisions remain unapproved.

### 4. Implementation

For new modules:

```text
Migration → Model → Service → Route → Controller
→ Page Blade → Livewire PHP → Livewire Blade
```

For refactors:

- Add or strengthen tests around existing behavior first when risk is high.
- Establish the canonical implementation.
- Migrate callers incrementally.
- Preserve backward compatibility only where it is intentionally required.
- Remove duplicates only after callers and tests confirm they are unused.
- Do not revert unrelated working-tree changes.

### 5. Verification

- Run focused tests, then the relevant broader suite.
- Run configured formatting, static analysis, migration, and frontend build checks.
- Review authorization denial paths, transaction rollback, query counts, N+1 behavior, and memory bounds.
- Report commands, results, and anything that could not be verified.

## Coding Standards

- Follow Laravel 12, Livewire 3.1, and existing repository conventions that do not conflict with this file.
- Use strict, clear names and correct module namespaces.
- Keep Controllers and Livewire components thin.
- Accept validated arrays/scalars in Services.
- Keep functions focused; avoid god classes and speculative abstractions.
- Add abstractions only for real reuse or complexity reduction.
- Use concise comments only for non-obvious logic or structural replacement points.
- Use `decimal` for money and explicit casts for booleans, dates, arrays, and JSON.
- Add meaningful schema comments to important columns.
- Index actual search, filter, sort, join, foreign-key, and unique-key fields.
- Prevent N+1 queries with eager loading and query-count awareness.
- Do not query inside loops or load unbounded datasets into memory.
- Cache only with explicit invalidation and acceptable stale-data behavior.
- Use queues for large or long-running work with progress, retry, idempotency, and failure reporting.
- Do not leak raw exceptions, credentials, personal data, or stack traces.
- Preserve unrelated user changes and avoid destructive Git/filesystem operations unless explicitly requested.

## Testing Requirements

Every change requires tests proportional to its risk.

Required coverage where applicable:

- Route boot, authentication, named permissions, ownership, allowed behavior, and denied behavior.
- Livewire validation and CRUD actions.
- Service business rules, search, filters, sorting, pagination, and transactions.
- Model casts and relationships.
- Migration ordering, constraints, indexes, fresh-install behavior, and production MySQL compatibility.
- Derived-field calculation.
- Import mapping, normalization, unique keys, duplicate modes, dry-run, null handling, rollback/partial behavior, and error reports.
- Export filters, selected IDs, `$fillable`, `$exceptExport`, templates, and sensitive-field exclusion.
- Destructive action confirmation, audit behavior, path traversal, identifier tampering, and command allowlists.
- Checkout/payment callbacks and retry/idempotency behavior when affected.
- Query-count or N+1 regression tests for changed relationship-heavy screens.
- Frontend build checks for UI changes.

Testing rules:

- Add a regression test for bug fixes when technically feasible.
- Test failure and denial paths, not only happy paths.
- High-risk security, import, transaction, payment, backup, restore, and destructive workflows require explicit negative tests.
- Do not invent a numeric coverage target; directly test every changed business rule.
- A task is incomplete until relevant tests pass or the final report clearly states what could not run and why.

## Completion Checklist

Before declaring a task complete, verify:

- Mandatory documents were read in priority order.
- The implementation belongs to the canonical module.
- The Service layer owns business logic and transactions.
- Authorization and validation cover every entry point.
- No unconfirmed destructive or data-changing assumptions were introduced.
- UI follows Admin UI v1.1 where applicable.
- Import/export uses the shared v1.5 foundation where applicable.
- Queries are bounded and free of known N+1 regressions.
- Relevant tests and configured checks passed.
- Documentation or module plans were updated when architecture or behavior changed.
- The final response reports changed files, verification results, and unresolved risks.
