# Admin Rebuild Specification

This specification governs rebuilding/refactoring `Modules/Admin`. It resolves the copied “Category Rebuild Specification” wording in the prompt by targeting Admin, because `docs/modules/Admin/ANALYSIS.md` and `docs/modules/Admin/REFACTOR_PLAN.md` are the requested module documents.

Every design decision below references one or more issues from:

- `docs/modules/Admin/ANALYSIS.md`
- `docs/modules/Admin/REFACTOR_PLAN.md`

## 1. Goal

The rebuilt Admin module must be a shell module only: layout, navigation, dashboard entry, profile shell, theme/header/menu shell UI, and narrowly scoped shell settings.

Goals:

- Keep Admin as a presentation shell, not a domain owner.
  - Reference: `ANALYSIS.md` sections 1, 18; `REFACTOR_PLAN.md` P1-1.
- Contain P0 risks before broad refactoring: unauthenticated API, database download/export/restore/drop/truncate, shell command strings, credential exposure, foreign-key restoration, and raw exception leakage.
  - Reference: `ANALYSIS.md` sections 3, 4, 9, 12, 14; `REFACTOR_PLAN.md` P0-1 through P0-6.
- Move Admin menu behavior out of Livewire and into a service-backed flow.
  - Reference: `ANALYSIS.md` sections 2, 6, 13, 14, 15; `REFACTOR_PLAN.md` P1-3, P1-4, P1-10.
- Stop Admin from owning product, order, post, category, coupon, role, staff, affiliate, banner, flash sale, chat, and domain settings behavior.
  - Reference: `ANALYSIS.md` sections 16, 18; `REFACTOR_PLAN.md` P1-1, P1-7, P1-8.
- Standardize import/export through `Modules/Shared/Services/ImportExport` and canonical domain modules.
  - Reference: `ANALYSIS.md` sections 11, 13, 15; `REFACTOR_PLAN.md` P1-5, P1-6.

Needs confirmation before coding:

- Whether the Admin API route should be removed or protected.
- Whether database administration screens remain in Admin or move to `Modules/System`.
- Whether Admin shell menu data should remain in the existing `categories` table or move to a dedicated admin menu table.
- Which module canonically owns settings, user addresses, banners, flash sales, affiliate schemes, and chat.

## 2. Target Architecture

Required flow:

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

Target Admin shell flow:

```text
Modules/Admin/routes/web.php
→ Thin Admin controllers
→ Admin page Blade shells
→ Admin Livewire components for shell UI only
→ Admin/Shared Blade components
→ Admin shell services
→ Shared import/export only for confirmed Admin-owned shell data
→ Admin shell models
→ Admin shell migrations
```

Design decisions:

- Routes define URL, name, middleware, permission middleware, and controller actions only.
  - Reference: `ANALYSIS.md` section 3; `REFACTOR_PLAN.md` P1-2.
- Controllers return views or pass scalar IDs only.
  - Reference: `ANALYSIS.md` section 4; `REFACTOR_PLAN.md` P1-7.
- Page Blade files extend `Admin::layouts.master` and mount Livewire only.
  - Reference: `ANALYSIS.md` section 5.
- Livewire owns state, UI validation, events, and service calls only.
  - Reference: `ANALYSIS.md` sections 6, 13; `REFACTOR_PLAN.md` P1-3, P1-4.
- Services own queries, transactions, business rules, import/export orchestration, validation invariants, and cache invalidation.
  - Reference: `ANALYSIS.md` sections 9, 14; `REFACTOR_PLAN.md` P1-3, P1-11.
- Import/export must use `Modules/Shared/Services/ImportExport` and only for confirmed Admin-owned data.
  - Reference: `ANALYSIS.md` section 11; `REFACTOR_PLAN.md` P1-5.
- Domain management routes currently stranded in `Modules/Admin/routes/web copy.php` must not be restored into Admin without canonical ownership confirmation.
  - Reference: `ANALYSIS.md` sections 3, 17; `REFACTOR_PLAN.md` P2-1.

## 3. Database Design

### Tables

Confirmed or candidate Admin-owned tables:

- `header_menus`
  - Reference: `ANALYSIS.md` section 10.
- `header_menu_items`
  - Reference: `ANALYSIS.md` section 10.
- `settings`
  - Needs confirmation before coding because `Modules/Admin/Models/Setting.php` duplicates settings also referenced by Website.
  - Reference: `ANALYSIS.md` section 10; `REFACTOR_PLAN.md` P1-8.
- Admin shell menu storage
  - Needs confirmation before coding: current `Modules/Admin/Models/Category.php` uses the default `categories` table for menu data, which conflicts with Category/Website ownership.
  - Reference: `ANALYSIS.md` sections 10, 18; `REFACTOR_PLAN.md` P1-8.

Not Admin-owned unless confirmed:

- Product, order, post, category domain, coupon, role, staff/user, affiliate, banner, flash sale, chat, footer, homepage, and Website settings tables.
  - Reference: `ANALYSIS.md` sections 16, 18; `REFACTOR_PLAN.md` P1-1.

### Columns

`header_menus` target columns:

- `id`
- `name`
- `location`
- `is_active`
- `created_at`
- `updated_at`

`header_menu_items` target columns:

- `id`
- `header_menu_id`
- `parent_id`
- `title`
- `url`
- `route_name`
- `params`
- `icon`
- `target`
- `sort_order`
- `is_active`
- `created_at`
- `updated_at`

`settings` target columns if Admin remains owner:

- `id`
- `key`
- `value`
- `group_name`
- `type`
- `label`
- `created_at`
- `updated_at`

Admin shell menu target columns if a dedicated table is approved:

- `id`
- `name`
- `slug`
- `url`
- `icon`
- `permission_name`
- `parent_id`
- `is_active`
- `sort_order`
- `created_at`
- `updated_at`

Needs confirmation before coding:

- Whether to add a new dedicated `admin_menu_items` table or keep using `categories` with `type = menu`.
- Whether `permission_name` should reference `permissions.name` or store nullable strings for disabled/deleted permissions.

### Indexes

Required indexes:

- `header_menus.location` unique.
- `header_menu_items.header_menu_id`.
- `header_menu_items.parent_id`.
- `header_menu_items.sort_order`.
- `header_menu_items.is_active`.
- `settings.key` unique if Admin remains settings owner.
- Admin shell menu table, if created:
  - unique `slug`
  - index `parent_id`
  - index `permission_name`
  - index `is_active`
  - index `sort_order`

References:

- `ANALYSIS.md` section 10 identifies current tables.
- `ANALYSIS.md` section 15 identifies menu tree query risks.
- `REFACTOR_PLAN.md` P1-8 and P1-10 require clear ownership and query optimization.

### Foreign Keys

Required foreign keys:

- `header_menu_items.header_menu_id` references `header_menus.id` with cascade delete.
- `header_menu_items.parent_id` references `header_menu_items.id` with cascade delete.
- Admin shell menu table, if created:
  - `parent_id` references same table with null-on-delete or cascade behavior.
  - Needs confirmation before coding: cascade delete may delete large menu subtrees; null-on-delete may preserve children.

Do not add foreign keys to Product/Website/Role/User tables from Admin unless the canonical ownership decision explicitly allows it.

### Constraints

Required constraints:

- `header_menus.location` non-null and unique.
- `header_menu_items.title` non-null.
- `header_menu_items.target` constrained to `_self` or `_blank`.
- Booleans use boolean casts and database defaults.
- JSON columns such as `params` remain valid JSON.

Needs confirmation before coding:

- Whether `url` and `route_name` are mutually exclusive or can coexist.
- Whether menu permission references are strict (`exists:permissions,name`) or soft strings for stale permission compatibility.

### Migration Notes

- Malformed negative-year migration filenames must be resolved with a compatibility plan before renaming.
  - Files:
    - `Modules/Admin/database/migrations/-0001_11_30_000024_create_settings_table.php`
    - `Modules/Admin/database/migrations/-0001_11_30_000034_create_header_menus_table.php`
    - `Modules/Admin/database/migrations/-0001_11_30_000035_create_header_menu_items_table.php`
  - Reference: `ANALYSIS.md` section 10; `REFACTOR_PLAN.md` P1-9.
- Do not change production migration filenames blindly.
- If creating a dedicated Admin menu table, include a data migration plan from existing `categories` menu rows only after confirming ownership.
  - Reference: `REFACTOR_PLAN.md` P1-8.

## 4. Model Design

### Model Classes

Keep or rebuild as Admin-owned:

- `Modules\Admin\Models\HeaderMenu`
- `Modules\Admin\Models\HeaderMenuItem`
- `Modules\Admin\Models\Setting`
  - Needs confirmation before coding due to duplicate Website settings ownership.
- `Modules\Admin\Models\Category`
  - Needs confirmation before coding. Prefer replacing with `AdminMenuItem` if Admin owns shell menus.

Move or migrate out of Admin:

- `Modules\Admin\Models\AffiliateLevel`
- `Modules\Admin\Models\AffiliateScheme`
- `Modules\Admin\Models\Banner`
- `Modules\Admin\Models\ChatMessage`
- `Modules\Admin\Models\ChatSession`
- `Modules\Admin\Models\FlashSale`
- `Modules\Admin\Models\FlashSaleItem`
- `Modules\Admin\Models\UserAddress`
- `Modules\Admin\Models\Admin`

Reference: `ANALYSIS.md` sections 10, 16, 18; `REFACTOR_PLAN.md` P1-1, P1-8, P2-4.

### Fillable Fields

`HeaderMenu`:

- `name`
- `location`
- `is_active`

`HeaderMenuItem`:

- `header_menu_id`
- `parent_id`
- `title`
- `url`
- `route_name`
- `params`
- `icon`
- `target`
- `sort_order`
- `is_active`

`Setting`, if Admin remains owner:

- `key`
- `value`
- `group_name`
- `type`
- `label`

`AdminMenuItem`, if created:

- `name`
- `slug`
- `url`
- `icon`
- `permission_name`
- `parent_id`
- `is_active`
- `sort_order`

### Casts

Required casts:

- `is_active`: boolean
- `sort_order`: integer
- `params`: array for `HeaderMenuItem`

Recommended casts:

- Date/time fields use default Eloquent datetime handling.

### Relationships

`HeaderMenu`:

- `items()`: has many `HeaderMenuItem`
- `rootItems()`: has many `HeaderMenuItem` where `parent_id` is null

`HeaderMenuItem`:

- `children()`: has many self, ordered by `sort_order`
- `parent()`: belongs to self
- `menu()`: belongs to `HeaderMenu`

`AdminMenuItem`, if created:

- `children()`: has many self
- `parent()`: belongs to self

Needs confirmation before coding:

- Whether `HeaderMenuItem` should support route parameters as JSON array or normalized child table.

### Scopes

Recommended scopes:

- `active()`
- `root()`
- `ordered()`
- `location($location)` on `HeaderMenu`

`Modules\Admin\Models\Category::menu()` should be retired or retained only if Admin menu ownership remains on `categories`.

Reference: `ANALYSIS.md` section 10; `REFACTOR_PLAN.md` P1-8.

### Accessors / Mutators

Avoid business logic in models. Only simple presentation-safe accessors are allowed:

- `resolved_url` accessor for `HeaderMenuItem` only if it avoids Blade route logic.

Needs confirmation before coding:

- Whether route URL resolution belongs in the service instead of a model accessor.

## 5. Service Design

### Service Classes

Required Admin shell services:

- `Modules\Admin\Services\MenuService`
  - New service for sidebar/admin menu behavior.
  - Reference: `REFACTOR_PLAN.md` P1-3.
- `Modules\Admin\Services\HeaderMenuService`
  - Keep and harden for header menu tree behavior.
  - Reference: `ANALYSIS.md` section 9.
- `Modules\Admin\Services\ProfileService`
  - Keep for authenticated admin profile.
  - Reference: `ANALYSIS.md` section 6.
- `Modules\Admin\Services\AddressService`
  - Keep only if user address ownership remains with Admin; otherwise migrate.
  - Needs confirmation before coding.
  - Reference: `REFACTOR_PLAN.md` P1-11.
- `Modules\Admin\Services\DatabaseService`
  - Either remove from Admin, move to System, or harden behind P0 controls.
  - Needs confirmation before coding.
  - Reference: `REFACTOR_PLAN.md` P0-2 through P0-6.
- `Modules\Admin\Services\SettingsService`
  - Keep only if Admin owns settings.
  - Needs confirmation before coding.
  - Reference: `REFACTOR_PLAN.md` P1-8.

Services to migrate out of Admin unless ownership is confirmed:

- `AdminAffiliateService`
- `AffiliateRankService`
- `BannerService`
- `ChatService`
- `FlashSaleService`
- `HomeSettingService`
- Env/System config services if they are System-owned

Reference: `ANALYSIS.md` sections 9, 18; `REFACTOR_PLAN.md` P1-1.

### Public Methods

`MenuService`:

- `paginate(array $filters, int|string $perPage)`
- `tree(array $filters = [])`
- `findForEdit(int $id)`
- `create(array $data)`
- `update(int $id, array $data)`
- `delete(int $id)`
- `bulkDelete(array $ids)`
- `bulkSetActive(array $ids, bool $active)`
- `bulkAssignPermission(array $ids, ?string $permissionName)`
- `reorder(array $tree)`
- `duplicate(int $id)`
- `validateImport(array $payload): array`
- `import(array $payload, array $options): array`
- `export(array $filters = []): array`
- `restoreDefault(array $options): array`

`HeaderMenuService`:

- Keep existing public methods:
  - `getMenuTreeByLocation`
  - `createItem`
  - `updateItem`
  - `deleteItem`
  - `reorderItems`
- Add explicit validation and authorization integration at caller boundary.

`DatabaseService`, if retained:

- Replace raw table/file methods with server-owned identifiers.
- Methods should return safe result arrays or domain result objects already established by the project; no DTOs.

### Responsibilities

Services own:

- Database queries.
- Search/filter/sort/pagination.
- Transactions.
- Slug generation.
- Tree validation and cycle prevention.
- Cache invalidation.
- Import/export orchestration.
- Domain invariants.

Livewire must not:

- Call models directly.
- Open transactions.
- Read/write files directly.
- Implement recursive persistence.

Reference: `ANALYSIS.md` sections 2, 6, 9; `REFACTOR_PLAN.md` P1-3.

### Transaction Boundaries

Transactions required:

- Menu create/update if related writes are introduced.
- Menu delete with children.
- Bulk delete.
- Bulk permission assignment.
- Reorder tree.
- Duplicate tree.
- Import.
- Restore default menu.
- Address default change if `AddressService` remains in Admin.
- Any database destructive operation if retained, plus `try/finally` for FK checks.

References:

- `ANALYSIS.md` section 14.
- `REFACTOR_PLAN.md` P0-5, P1-4, P1-11.

### Business Rules

Menu rules:

- `name` required, max 255.
- `slug` unique.
- `parent_id` must exist and cannot create a cycle.
- `permission_name` must be valid or explicitly allowed as stale text.
  - Needs confirmation before coding.
- Restore default menu must validate all data before replacing anything.
- Bulk operations require permission and explicit UI confirmation.

Database admin rules:

- No browser-provided table names, paths, shell commands, or executable paths.
- Use server-controlled metadata and named permissions.
- Fail closed when permissions or required secrets are absent.

References:

- `ANALYSIS.md` sections 12, 13, 14.
- `REFACTOR_PLAN.md` P0-1 through P0-6, P1-4.

## 6. Livewire Design

### Component List

Active shell components to keep/refactor:

- `Modules/Admin/Livewire/Menus/MenuTable.php`
- `Modules/Admin/Livewire/Menus/MenuForm.php`
- `Modules/Admin/Livewire/Profile/UserProfile.php`
- `Modules/Admin/Livewire/Profile/UserAddress.php`
  - Needs confirmation before coding: user address ownership.
- `Modules/Admin/Livewire/ThemeSwitcher.php`
- `Modules/Admin/Livewire/Header/GeneralSettings.php`
- `Modules/Admin/Livewire/Header/MenuManager.php`
- `Modules/Admin/Livewire/Partials/Header.php`
- `Modules/Admin/Livewire/Partials/HeaderNotifications.php`
- `Modules/Admin/Livewire/Partials/HeaderSearch.php`
- `Modules/Admin/Livewire/Partials/HeaderUser.php`
- `Modules/Admin/Livewire/Partials/Sidebar.php`

Disable, migrate, or remove after ownership confirmation:

- Product, Post, Category, Coupon, Order, Role, Staff, Affiliate, Banner, Chat, FlashSale, Footer, Home, and Database Livewire classes.

References:

- `ANALYSIS.md` sections 6, 16, 18.
- `REFACTOR_PLAN.md` P1-1, P1-7.

### State Properties

`MenuTable` target state:

- `search`
- `status`
- `selectedIds`
- `selectAll`
- `perPage`
- `sortField`
- `sortDirection`
- `showImportModal`
- `importFile`
- `showRestoreConfirm`
- `showBulkPermissionModal`
- `bulkPermission`
- `confirmingDeleteId`

`MenuForm` target state:

- `id`
- `name`
- `url`
- `icon`
- `permissionName`
- `parentId`
- `isActive`
- `isSection`

Use `wire:model.live` by default.

Reference: `CODEX_BOOTSTRAP.md`; `ANALYSIS.md` sections 6, 13.

### Validation Rules

Livewire UI validation:

- `name`: required string max 255.
- `url`: nullable string max 500.
- `icon`: nullable string max 100.
- `permissionName`: nullable string max 255; service validates existence or stale permission policy.
- `parentId`: nullable integer; service validates ownership and cycle rules.
- `isActive`: boolean.
- `isSection`: boolean.
- `importFile`: required only for import, file size/type controlled by shared import panel if used.

Service validation:

- Parent exists and belongs to menu set.
- No parent cycle.
- Permission policy.
- Slug uniqueness.
- Import tree schema and duplicate behavior.

References:

- `ANALYSIS.md` section 13.
- `REFACTOR_PLAN.md` P1-4.

### Events

Allowed Livewire/browser events:

- `notify`
- `menu-saved`
- `menu-deleted`
- `menu-reordered`
- `import-completed`
- `restore-completed`
- `permission-denied`

Events must not carry trusted IDs for authorization decisions; services re-check IDs.

Reference: `ANALYSIS.md` section 12; `REFACTOR_PLAN.md` P2-5.

### Pagination

Menu list pagination:

- Default `10`.
- Options: `10`, `25`, `50`, `100`, guarded `All`.
- `All` must be capped or disabled when row count exceeds a safe threshold.
- Changing filters, search, sort, or `perPage` resets to page one.

Reference: `ANALYSIS.md` section 15; `REFACTOR_PLAN.md` P1-10.

### Search / Filter / Sort Behavior

Menu search:

- Search by `name`, `slug`, `url`, and `permission_name`.

Filters:

- `active`
- `inactive`
- `all`
- parent/root only if useful.

Sort:

- Default `sort_order`.
- Optional `name`, `created_at`, `updated_at`.

All search/filter/sort queries belong in `MenuService`.

Reference: `REFACTOR_PLAN.md` P1-3, P1-10.

## 7. Blade/UI Design

### Page Blade Files

Keep/refactor active shell pages:

- `Modules/Admin/resources/views/pages/dashboard.blade.php`
- `Modules/Admin/resources/views/pages/menus/index.blade.php`
- `Modules/Admin/resources/views/pages/menus/create.blade.php`
- `Modules/Admin/resources/views/pages/menus/edit.blade.php`
- `Modules/Admin/resources/views/pages/profiles/profile.blade.php`
- `Modules/Admin/resources/views/pages/admin/themes.blade.php`
- `Modules/Admin/resources/views/pages/admin/header/index.blade.php`

Do not restore domain page blades into Admin without ownership confirmation:

- Product, post, order, category, coupon, customer, role, staff, database, settings, footer, home, banner, affiliate, flash-sale, and chat page blades.

Reference: `ANALYSIS.md` section 5; `REFACTOR_PLAN.md` P1-1, P2-1.

### Livewire Blade Files

Keep/refactor active shell views:

- `Modules/Admin/resources/views/livewire/menus/menu-table.blade.php`
- `Modules/Admin/resources/views/livewire/menus/menu-form.blade.php`
- `Modules/Admin/resources/views/livewire/profile/user-profile.blade.php`
- `Modules/Admin/resources/views/livewire/profile/user-address.blade.php`
- `Modules/Admin/resources/views/livewire/theme-switcher.blade.php`
- `Modules/Admin/resources/views/livewire/header/general-settings.blade.php`
- `Modules/Admin/resources/views/livewire/header/menu-manager.blade.php`
- `Modules/Admin/resources/views/livewire/partials/*.blade.php`

Duplicate views under `Modules/Admin/resources/views/livewire/admin/*` must be removed only after render-path verification.

Reference: `ANALYSIS.md` section 7; `REFACTOR_PLAN.md` P2-3.

### Shared Components

Admin-only components:

- `Modules/Admin/resources/views/components/menu-item.blade.php`
- `Modules/Admin/resources/views/components/icon.blade.php` if only used by Admin shell.
- `Modules/Admin/resources/views/components/toast.blade.php` if layout-specific.

Move candidates to `Modules/Shared` after usage audit:

- `category-select.blade.php`
- `currency-input.blade.php`
- `editor.blade.php`
- `gallery.blade.php`
- `image-upload.blade.php`

Reference: `ANALYSIS.md` section 8; `REFACTOR_PLAN.md` P1-12.

### AdminLTE / Bootstrap Layout Rules

Target standard:

- Use Laravel Blade, Livewire 3.1, Tailwind CSS 4, and `Admin::layouts.master`.
- Do not add Bootstrap, jQuery, inline CSS, or a second UI pattern in new/refactored Admin shell work.

Needs confirmation before coding:

- Existing layout may contain AdminLTE/Bootstrap assets from the current repository. New refactor work must not expand those dependencies unless compatibility is explicitly required.

Reference:

- `CODEX_BOOTSTRAP.md` Admin UI rules.
- `ROADMAP.md` P1-03 notes frontend stack mismatch.

### Table Design

Menu table must include:

- Search input.
- Status filter.
- Per-page selector.
- Responsive `overflow-x-auto`.
- Empty state.
- Loading state.
- Row actions with disabled/loading states.
- Bulk action confirmation.
- Server-side pagination.
- No direct permission reliance for action protection.

Reference: `ANALYSIS.md` sections 6, 12, 15; `REFACTOR_PLAN.md` P1-2, P1-10, P2-5.

### Form Design

Menu form must include:

- Field-level errors.
- Parent menu selector.
- Permission selector or permission text policy.
- URL/section behavior.
- Save loading state.
- Cancel/back link.

Use `x-select-search` for long parent/permission lists if available.

Reference: `ANALYSIS.md` section 13; `REFACTOR_PLAN.md` P1-4.

## 8. Import Design

Admin-owned import scope:

- Only Admin shell menus may be imported by Admin.
- Product, post, coupon, role, and other imports must move to canonical modules.

References:

- `ANALYSIS.md` section 11.
- `REFACTOR_PLAN.md` P1-5, P1-6.

### Import Classes

Preferred design:

- `Modules/Admin/Services/ImportExport.php` only if Admin shell menu import/export is kept.
- Optional split classes if complexity requires:
  - `Modules/Admin/Import/MenuImport.php`
  - `Modules/Admin/Import/MenuRowMapper.php`
  - `Modules/Admin/Import/MenuRowValidator.php`

Needs confirmation before coding:

- Whether JSON menu import should use shared import/export foundation or remain a menu-service JSON restore tool. If it remains JSON, it still needs service-owned validation, dry-run, and reporting.

### Header Mapping

For spreadsheet-style menu import, canonical headers:

- `name`
- `slug`
- `url`
- `icon`
- `permission_name`
- `parent_slug`
- `is_active`
- `sort_order`

Aliases:

- `name`: `name`, `ten`, `tên`
- `url`: `url`, `link`, `duong_dan`, `đường dẫn`
- `permission_name`: `permission`, `can`, `quyen`, `quyền`

Needs confirmation before coding:

- Current menu import is JSON, not spreadsheet. Header mapping applies only if spreadsheet import is approved.

### Column Mapping

Positional mapping is not approved.

Needs confirmation before coding:

- If menu import files lack stable headers, define A/B/C mapping explicitly before implementation.

### Row Normalization

Normalize:

- Trim strings.
- Empty strings to null for optional fields.
- Boolean values from `1/0`, `true/false`, `yes/no`, `active/inactive`, `có/không`.
- Slug generated by service only if blank.
- `parent_slug` resolved after all rows validate.

### Row Validation

Validate:

- Required `name`.
- Unique slug after normalization.
- Valid parent reference.
- No cycles.
- Valid permission policy.
- Valid boolean.
- URL max length and route/URL policy.

### Duplicate Handling

Default mode:

- `skip_duplicate` for dry-run and safe imports.

Allowed modes after confirmation:

- `create_only`
- `update_or_create`
- `skip_duplicate`

Forbidden without explicit confirmation:

- `replace`
- truncate-before-import
- null overwrite of important fields

Reference: `ANALYSIS.md` sections 13, 14; `REFACTOR_PLAN.md` P1-4.

### Error Reporting

Report format:

- `success`
- `total_rows`
- `success_rows`
- `error_rows`
- `skipped_rows`
- `errors[]`: row, field, value, reason
- `debug`: mode, dry_run, detected format, headers, duplicate keys

Do not expose stack traces or raw exception text.

Reference: `ANALYSIS.md` sections 12, 13; `REFACTOR_PLAN.md` P0-6, P1-5.

## 9. Export Design

Admin-owned export scope:

- Only Admin shell menu export is allowed in Admin unless another Admin-owned table is confirmed.
- Product export must move to the canonical Product owner.

Reference: `ANALYSIS.md` section 11; `REFACTOR_PLAN.md` P1-5, P1-6.

### Export Classes

Preferred design:

- `Modules/Admin/Services/ImportExport.php` for menu export if spreadsheet export is approved.
- Optional:
  - `Modules/Admin/Export/MenuExport.php`
  - `Modules/Admin/Export/MenuExportQuery.php`
  - `Modules/Admin/Export/MenuExportMapper.php`
  - `Modules/Admin/Export/MenuTemplateBuilder.php`

Needs confirmation before coding:

- Whether menu export remains JSON download or becomes shared spreadsheet export.

### Query Design

Menu export query:

- Runs in `MenuService`.
- Filters by current search/status if requested.
- Eager loads children to a bounded depth or uses a service-built tree from one bounded query.
- Does not call unbounded `get()` for large data without a safe cap.

Reference: `ANALYSIS.md` section 15; `REFACTOR_PLAN.md` P1-10.

### Export Mapping

Spreadsheet mapping:

- `name`
- `slug`
- `url`
- `icon`
- `permission_name`
- `parent_slug`
- `is_active`
- `sort_order`

JSON mapping:

- Nested tree with name, slug, url, icon, permission, active flag, sort order, and children.

### Template Generation

If spreadsheet import/export is approved:

- Generate a professional template with headers, sample rows, required/optional notes, and valid boolean examples.

Needs confirmation before coding:

- Template format and Vietnamese header aliases.

### Large Export Strategy

- Use chunking/lazy iteration for domain exports.
- Menu export can be synchronous only if bounded by safe row count.
- For large domain exports, queue in canonical module, not Admin.

Reference: `ANALYSIS.md` section 15; `REFACTOR_PLAN.md` P1-6, P1-10.

## 10. Permissions and Authorization

### Required Permissions

Admin shell permissions:

- `admin.dashboard.view`
- `admin.menu.view`
- `admin.menu.create`
- `admin.menu.update`
- `admin.menu.delete`
- `admin.menu.restore`
- `admin.menu.import`
- `admin.menu.export`
- `admin.header.view`
- `admin.header.update`
- `admin.theme.view`
- `admin.theme.update`
- `admin.profile.view`
- `admin.profile.update`

Database/system permissions if retained:

- `database.view`
- `database.backup`
- `database.download`
- `database.restore`
- `database.destroy`

Needs confirmation before coding:

- Exact permission names must align with Role module seeders and existing Spatie Permission conventions.

References:

- `ANALYSIS.md` sections 3, 12.
- `REFACTOR_PLAN.md` P0-1, P0-2, P0-3, P1-2.

### Policy / Gate Checks

Required checks:

- Route middleware for page access.
- Livewire action-level checks for mutating operations.
- Service-level invariant checks for destructive or sensitive operations.
- `Gate::before` Super Admin behavior remains project-level behavior.

Reference: `CODEX_BOOTSTRAP.md` security rules; `ANALYSIS.md` section 12.

### Livewire Action Protection

Every mutating Livewire action must call authorization before service execution:

- save
- delete
- bulk delete
- bulk status update
- import
- export
- restore default
- reorder
- duplicate
- theme update
- header update
- database actions if retained

Do not trust hidden buttons or UI menu visibility.

Reference: `ANALYSIS.md` section 12; `REFACTOR_PLAN.md` P2-5.

### Route Middleware

Active web routes:

- `web`
- `auth:admin`
- named permission middleware per route/group

API route:

- Remove or protect with explicit authentication and permission.

Reference: `ANALYSIS.md` section 3; `REFACTOR_PLAN.md` P0-1, P1-2.

## 11. Transactions and Data Integrity

### Actions Requiring DB Transactions

- Menu create/update when writing related data.
- Menu delete and subtree delete.
- Bulk delete.
- Bulk status update.
- Bulk permission assignment.
- Menu reorder.
- Menu duplicate.
- Menu import.
- Menu restore default.
- Address create/update/delete/set default if retained in Admin.
- Database destructive operations if retained, with additional safeguards.

References:

- `ANALYSIS.md` section 14.
- `REFACTOR_PLAN.md` P0-5, P1-4, P1-11.

### Rollback Conditions

Rollback on:

- Failed validation after normalization.
- Duplicate slug conflict.
- Parent cycle detection.
- Invalid permission reference when strict permission mode is enabled.
- File parse failure.
- Any failed row in all-or-nothing import.
- Any service exception during multi-write operations.
- Unauthorized action.
- Failed database process execution.

### Idempotency Concerns

Menu import:

- Use stable unique slug or confirmed unique key.
- Re-running `skip_duplicate` import should not create duplicates.
- Re-running `update_or_create` should be deterministic.

Restore default:

- Must be explicit, dry-run first, and all-or-nothing.

Database restore:

- Needs confirmation before coding.
- If retained, restore operations require opaque backup IDs, audit logs, and failure recovery behavior.

Reference: `REFACTOR_PLAN.md` P1-4, P0-3.

## 12. Performance Strategy

### Eager Loading

- Menu tree should be loaded using a bounded tree strategy.
- Avoid recursive lazy loading of `children`.
- Header menus should eager load required nested items in service code only.

Reference: `ANALYSIS.md` section 15; `REFACTOR_PLAN.md` P1-10.

### Query Optimization

- All queries belong in services.
- Search/filter/sort should use indexed columns.
- Avoid `get()` on unbounded domain datasets.
- Avoid `paginate(999999)`.

References:

- `ANALYSIS.md` sections 6, 15.
- `REFACTOR_PLAN.md` P1-3, P1-6, P1-10.

### Pagination

- Server-side pagination for list pages.
- Default 10 rows.
- Options: 10, 25, 50, 100, guarded `All`.
- `All` capped/disabled if row count exceeds threshold.

### Caching If Needed

Cache only:

- Menu tree.
- Header menu tree.

Requirements:

- Explicit cache keys.
- Invalidate after create/update/delete/reorder/import/restore.
- Do not cache admin search/filter results.

Reference: `ANALYSIS.md` sections 6, 15; `REFACTOR_PLAN.md` P1-3, P2-5.

## 13. Test Strategy

### Route Tests

Test:

- Active Admin routes boot.
- Guest is redirected/denied.
- Authenticated admin without permission is denied.
- Authorized admin can view route.
- API route removed or protected.

References:

- `ANALYSIS.md` section 3.
- `REFACTOR_PLAN.md` P0-1, P1-2.

### Livewire Tests

Test:

- Menu list renders.
- Search/filter/sort pagination calls service behavior.
- Create/update validation.
- Delete confirmation.
- Bulk actions deny without permission.
- Reorder denies invalid payload.
- Import and restore actions require permission and confirmation.

References:

- `ANALYSIS.md` sections 6, 13.
- `REFACTOR_PLAN.md` P1-3, P1-4.

### Service Tests

Test:

- `MenuService` tree building.
- Slug uniqueness.
- Parent cycle rejection.
- Bulk operations transaction behavior.
- Cache invalidation after writes.
- `AddressService` default address transaction if retained.
- `DatabaseService` identifier validation and safe failure if retained.

References:

- `ANALYSIS.md` sections 9, 14, 15.
- `REFACTOR_PLAN.md` P0-4, P0-5, P1-11.

### Import Tests

Test:

- Valid menu import dry-run.
- Invalid JSON/spreadsheet structure.
- Duplicate slug handling.
- Parent missing or cyclic parent reference.
- Null overwrite prevention.
- All-or-nothing rollback.
- Structured error reporting.

Reference: `ANALYSIS.md` sections 11, 13, 14; `REFACTOR_PLAN.md` P1-4, P1-5.

### Export Tests

Test:

- Menu export respects filters.
- Export mapping contains expected fields.
- Template generation if approved.
- Large export uses bounded strategy.
- Product export no longer lives in Admin after migration.

Reference: `ANALYSIS.md` sections 11, 15; `REFACTOR_PLAN.md` P1-5, P1-6.

### Authorization Tests

Test:

- All active routes require `auth:admin` and named permissions.
- Mutating Livewire actions deny unauthorized users.
- Database actions deny unauthorized users and invalid identifiers.
- Menu visibility does not replace server-side authorization.

Reference: `ANALYSIS.md` section 12; `REFACTOR_PLAN.md` P0-1 through P0-3, P2-5.

## 14. Implementation Checklist

### P0

- [ ] Decide whether `Modules/Admin/routes/api.php` is removed or permission-protected. Reference: `REFACTOR_PLAN.md` P0-1.
- [ ] Disable or permission-gate database download/export/truncate/drop/restore flows. Reference: `REFACTOR_PLAN.md` P0-2, P0-3.
- [ ] Replace database shell command strings with safe Process argument arrays and no command-line secrets. Reference: `REFACTOR_PLAN.md` P0-4.
- [ ] Add `try/finally` restoration around foreign-key toggles if database destructive operations remain. Reference: `REFACTOR_PLAN.md` P0-5.
- [ ] Redact raw exception output from database/system flows. Reference: `REFACTOR_PLAN.md` P0-6.
- [ ] Add P0 security tests before exposing database/system UI. Reference: `ROADMAP.md` P0-06.

### P1

- [ ] Confirm canonical ownership for Admin, Product, Order, Post, Category, Role, User/Account, Website, System, and Shared. Reference: `REFACTOR_PLAN.md` P1-1.
- [ ] Add named permissions to active Admin web routes and Livewire actions. Reference: `REFACTOR_PLAN.md` P1-2.
- [ ] Create `Modules/Admin/Services/MenuService.php` and move menu query/persistence/transaction logic out of Livewire. Reference: `REFACTOR_PLAN.md` P1-3.
- [ ] Rebuild menu validation and restore/import behavior as dry-run, validated, all-or-nothing service logic. Reference: `REFACTOR_PLAN.md` P1-4.
- [ ] Move product/post/coupon/role import/export out of Admin or rebuild through canonical module shared import/export services. Reference: `REFACTOR_PLAN.md` P1-5, P1-6.
- [ ] Remove direct model queries from Admin controllers and Livewire. Reference: `REFACTOR_PLAN.md` P1-7.
- [ ] Resolve `Category` and `Setting` ownership before model/schema changes. Reference: `REFACTOR_PLAN.md` P1-8.
- [ ] Prepare migration compatibility plan for malformed Admin migration filenames. Reference: `REFACTOR_PLAN.md` P1-9.
- [ ] Bound menu/product export and unsafe `All` pagination behavior. Reference: `REFACTOR_PLAN.md` P1-10.
- [ ] Add transactions to `AddressService` if it remains Admin-owned. Reference: `REFACTOR_PLAN.md` P1-11.
- [ ] Audit reusable Admin components before moving to `Modules/Shared`. Reference: `REFACTOR_PLAN.md` P1-12.

### P2

- [ ] Verify and remove `Modules/Admin/routes/web copy.php`. Reference: `REFACTOR_PLAN.md` P2-1.
- [ ] Remove `Modules/Admin/Livewire/Affiliate/commission-list.blade.php:Zone.Identifier`. Reference: `REFACTOR_PLAN.md` P2-2.
- [ ] Verify and prune duplicate views under `Modules/Admin/resources/views/livewire/admin/*`. Reference: `REFACTOR_PLAN.md` P2-3.
- [ ] Clean scaffold and placeholder files after reference checks. Reference: `REFACTOR_PLAN.md` P2-4.
- [ ] Document that menu visibility is UI-only and cannot replace authorization. Reference: `REFACTOR_PLAN.md` P2-5.
