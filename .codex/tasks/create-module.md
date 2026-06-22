# Task: /create-module <ModuleName>

Create a new Laravel module that fits this repository.

## Steps

1. Read all bootstrap documents and `ROADMAP.md`.
2. Confirm no existing module with the same name exists.
3. Create `Modules/<ModuleName>/config/module.php` with `type`, `enabled`, and a short description.
4. Create standard folders needed for the requested feature.
5. Add routes, controllers, Livewire components, services, models, migrations, policies, imports, or exports only when required.
6. Generate module docs under `docs/modules/<ModuleName>/`.
7. Run formatting and targeted tests when code is created.

## Rules

- Use namespace `Modules\<ModuleName>`.
- Use lower-case folder names where the repository already does: `config`, `routes`, `resources`, `database`.
- Do not register the module manually unless the provider cannot discover it.
- Keep the first version minimal and coherent.
