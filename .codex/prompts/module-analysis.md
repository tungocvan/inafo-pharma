# Module Analysis Prompt

You are analyzing a Laravel 12 module in the INAFO Pharma repository.

## Input

Module name: `<ModuleName>`

## Required Reading

Read these files first:

- `.codex/bootstrap/CODEX_BOOTSTRAP.md`
- `.codex/bootstrap/PROJECT_BOOTSTRAP.md`
- `.codex/bootstrap/AI_PROJECT_CONTEXT.md`
- `ROADMAP.md`

Then read existing module docs under `docs/modules/<ModuleName>/` if present.

## Analysis Order

Follow this exact flow:

```text
Route -> Controller -> Page Blade -> Livewire PHP -> Livewire Blade -> Shared UI Components -> Services -> Import -> Export -> Shared Services -> Model -> Migration -> Database
```

## Scope

Analyze only:

- `Modules/<ModuleName>/`
- shared files directly referenced by the module
- global config, routes, providers, migrations, or views only when they affect this module

## Output

Generate or update:

- `docs/modules/<ModuleName>/ANALYSIS.md`
- `docs/modules/<ModuleName>/INFORMATION.md`
- `docs/modules/<ModuleName>/README.md`

Include:

- module purpose
- route map
- controller map
- Livewire map
- Blade and component map
- service map
- import/export map
- model and migration map
- database table assumptions
- authorization and validation findings
- performance risks
- security risks
- duplicated ownership or cross-module dependency risks
- recommended next steps

## Safety

Do not modify application code during analysis unless explicitly requested. Documentation changes are allowed.
