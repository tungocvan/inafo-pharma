# Project Bootstrap

This document is the source of truth for module loading in this project.

The module system is defined by:

- `composer.json`
- `Modules/ModuleServiceProvider.php`

When this document and a module's local documentation disagree, the behavior in
`Modules/ModuleServiceProvider.php` takes precedence.

# Autoload Architecture

Composer owns PHP class autoloading through these PSR-4 mappings:

| Namespace | Directory |
| --- | --- |
| `App\` | `app/` |
| `Modules\` | `Modules/` |
| `Database\Factories\` | `database/factories/` |
| `Database\Seeders\` | `database/seeders/` |
| `Tests\` | `tests/` through `autoload-dev` |

Every module class must therefore be located below `Modules/` and use a
namespace matching its path. For example:

```php
// Modules/Role/Livewire/RoleForm.php
namespace Modules\Role\Livewire;
```

After adding, moving, or renaming PHP classes, regenerate Composer's autoload
map when required:

```bash
composer dump-autoload
```

Composer package discovery does not discover project modules. Module discovery
and resource registration are handled only by `Modules\ModuleServiceProvider`.

# Module Discovery

At boot time, `ModuleServiceProvider`:

1. Reads every direct child directory of `Modules/`.
2. Resolves that directory's module manifest.
3. Determines the module type and enabled state.
4. Sorts all modules by boot order, then alphabetically by directory name.
5. Stores all discovered modules in `config('modules.registry')`.
6. Registers resources only for enabled modules.

`Modules/ModuleServiceProvider.php` itself is not a module because discovery
only considers directories.

The manifest path is resolved in this order:

1. `Modules/<Module>/config/module.php`
2. `Modules/<Module>/Config/module.php`

A manifest must return an array. The supported bootstrap keys are:

```php
return [
    'type' => 'domain',
    'enabled' => true,
];
```

Rules:

- `enabled` defaults to `true` and is cast to `bool`.
- `type` must be `shell`, `support`, or `domain`.
- A missing, non-array, or invalid manifest falls back to inferred defaults.
- An invalid type also marks the registry source as `fallback`.
- Setting `LOG_MODULE=true` logs the names of enabled modules.

Each registry entry contains:

```php
[
    'name' => 'Role',
    'type' => 'support',
    'enabled' => true,
    'path' => '/absolute/path/to/Modules/Role',
    'source' => 'manifest', // or "fallback"
]
```

The registry includes disabled modules, but disabled modules load no config,
routes, resources, helpers, migrations, Livewire components, Blade components,
or commands.

# Module Types

Supported module types and boot order:

| Order | Type | Purpose |
| --- | --- | --- |
| 1 | `shell` | Application shell and top-level presentation |
| 2 | `support` | Shared infrastructure or cross-domain capability |
| 3 | `domain` | Business-domain functionality |

Modules of the same type are booted alphabetically by directory name using a
case-sensitive string comparison.

When no valid type is declared, these fallback types apply:

| Module | Fallback type |
| --- | --- |
| `Admin` | `shell` |
| `Auth` | `support` |
| `Role` | `support` |
| `Template` | `support` |
| Any other module | `domain` |

Module type controls boot order only. It does not change which resources are
registered.

# Route Registration

Route files are discovered from the first existing route directory:

1. `routes/`
2. `Routes/`

Only these files are loaded:

- `routes/web.php`
- `routes/api.php`

Web routes are loaded with Laravel's `loadRoutesFrom()` exactly as written.
The module provider does not automatically add a URL prefix, route-name prefix,
controller namespace, or `web` middleware. The route file must declare the
required middleware and prefixes itself.

API routes are automatically wrapped with:

```php
Route::prefix('api')
    ->middleware('api')
    ->group(...);
```

An API route file must not add another top-level `api` prefix unless a resulting
`/api/api/...` URL is intentional. Additional module-specific prefixes and
middleware belong inside the route file.

Files with other names, such as `admin.php` or `web copy.php`, are not loaded.

# View Registration

Resources are discovered from the first existing directory:

1. `resources/`
2. `Resources/`

Views are loaded from:

```text
resources/views/
```

The same view path is registered under two namespaces:

```blade
{{ view('Role::roles.index') }}
{{ view('role::roles.index') }}
```

The namespace values are the exact module directory name and its lowercase
form. New code should use the lowercase namespace consistently.

Translations are also registered when `resources/lang/` exists:

- Namespaced translations use the exact module name, such as `Role::messages`.
- JSON translations are added globally through Laravel's JSON translation
  loader.

# Livewire Registration

Livewire components are recursively discovered only under:

```text
Modules/<Module>/Livewire/
```

The class namespace must match:

```text
Modules\<Module>\Livewire\<RelativeClass>
```

The component alias is:

```text
<lowercase-module>.<kebab-case-relative-class>
```

Examples:

| File | Class | Alias |
| --- | --- | --- |
| `Role/Livewire/RoleForm.php` | `Modules\Role\Livewire\RoleForm` | `role.role-form` |
| `Role/Livewire/Admin/RoleTable.php` | `Modules\Role\Livewire\Admin\RoleTable` | `role.admin.role-table` |

Classes that cannot be autoloaded are skipped. PHP files under `Livewire/` are
registered by class existence; they are not explicitly checked for inheritance
from a Livewire base class.

# Migration Registration

Migrations are loaded from the first existing path:

1. `database/migrations/`
2. `Database/Migrations/`

Registration uses Laravel's `loadMigrationsFrom()`. Migrations in other
directories are ignored by the module bootstrap.

Migration files should follow Laravel's normal timestamp naming convention and
must not depend on a later-booting module having already registered runtime
resources.

# Command Registration

Commands are registered only when the application is running in the console.
They are recursively discovered under:

```text
Modules/<Module>/Console/
```

The namespace must match the relative file path:

```text
Modules\<Module>\Console\<RelativeClass>
```

Only loadable classes extending `Illuminate\Console\Command` are registered.
Other classes in `Console/` are ignored.

# Namespace Rules

The following rules are mandatory:

1. The top-level namespace is `Modules`.
2. The second namespace segment exactly matches the module directory name.
3. Remaining namespace segments match the class's directory path and casing.
4. The class name matches the PHP filename.
5. Livewire classes live under `Modules\<Module>\Livewire`.
6. Console commands live under `Modules\<Module>\Console`.
7. Blade component classes live under one of:
   - `Modules\<Module>\View\Components`
   - `Modules\<Module>\Http\Components`

Linux paths are case-sensitive. A casing mismatch may work in another
environment but fail in production or during module registration.

# Folder Structure Rules

A module may use this structure:

```text
Modules/<Module>/
├── config/
│   ├── module.php
│   └── <other-config>.php
├── routes/
│   ├── web.php
│   └── api.php
├── resources/
│   ├── views/
│   │   └── components/
│   └── lang/
├── database/
│   └── migrations/
├── Helpers/
├── Livewire/
├── View/
│   └── Components/
├── Http/
│   └── Components/
└── Console/
```

Bootstrap-supported case alternatives are limited:

| Resource | Accepted paths |
| --- | --- |
| Manifest | `config/module.php`, `Config/module.php` |
| Config directory | `config/`, `Config/` |
| Routes directory | `routes/`, `Routes/` |
| Resources directory | `resources/`, `Resources/` |
| Migrations | `database/migrations/`, `Database/Migrations/` |

Other recognized paths have fixed casing: `Helpers`, `Livewire`, `Console`,
`View/Components`, and `Http/Components`.

When both alternatives exist, only the first path listed above is used.

Every top-level PHP file in `config/` or `Config/` is merged under:

```text
<lowercase-module>.<filename>
```

For example, `Modules/Website/Config/website.php` is available as
`config('website.website')`.

All PHP files below `Helpers/` are loaded recursively with `require_once`.

Class-based Blade components use the lowercase module name as their prefix:

```blade
<x-role::component-name />
```

Anonymous Blade components are discovered under
`resources/views/components/` and use the same lowercase prefix.

# Coding Constraints

- `Modules/ModuleServiceProvider` is the only automatic module bootstrap.
- A module-level service provider under `Providers/` is not automatically
  discovered or registered by this system.
- Only enabled modules may contribute bootstrap resources.
- Module names come from directory names; manifests cannot rename a module.
- Keep module manifests side-effect free and return a plain array.
- Declare module type explicitly instead of relying on fallback inference.
- Use lowercase module keys for config and new view references.
- Put route middleware, route names, and module-specific prefixes in route
  files; do not assume they are added automatically.
- Do not add a second global `api` prefix in `api.php`.
- Keep Livewire and command file paths aligned exactly with their namespaces.
- Do not place arbitrary classes in `Livewire/`; every loadable class there is
  offered to Livewire registration.
- Do not place executable helper files outside `Helpers/` and expect automatic
  loading.
- Do not expect models, services, jobs, events, listeners, imports, exports, or
  providers to be registered by directory scanning. They are available through
  Composer autoloading only, unless Laravel or application code registers them.
- Config, route, view, translation, migration, helper, Livewire, Blade
  component, and command registration follows the module boot order.
- Changes to loading conventions must be made in
  `Modules/ModuleServiceProvider.php` and reflected in this document in the
  same change.

After all enabled modules are registered, the provider grants users with the
`Super Admin` role unconditional authorization through `Gate::before`.
