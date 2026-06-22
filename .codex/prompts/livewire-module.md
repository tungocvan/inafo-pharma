# Livewire Module Prompt

You are creating or improving a Livewire 3 component in a Laravel module.

## Required Context

Livewire components are auto-registered by `Modules\ModuleServiceProvider` from `Modules/<ModuleName>/Livewire`.

Alias format:

```text
<lower-module>.<kebab.component.path>
```

## Rules

- Use typed public properties.
- Keep mount parameters explicit.
- Validate all user input.
- Authorize all mutating methods.
- Use services for business logic.
- Use pagination for tables.
- Avoid full-table `get()` calls.
- Use events only when component boundaries require them.
- Keep Blade views small and readable.

## Output

Document:

- component class path
- view path
- Livewire alias
- public state
- actions
- validation rules
- authorization rules
- service dependencies
