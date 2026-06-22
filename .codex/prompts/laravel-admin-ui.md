# Laravel Admin UI Prompt

You are building admin UI for this Laravel 12 modular application.

## Required Context

- The installed UI stack is Bootstrap 5.3 and AdminLTE 4 RC.
- Livewire 3 is available for interactive admin screens.
- `Admin` is a shell module. Domain behavior belongs in domain modules.

## Rules

- Build dense, scannable operational screens.
- Use existing layouts, partials, and components from the target module or `Admin`.
- Keep forms accessible and server-validated.
- Show clear validation, empty, loading, saving, permission-denied, and error states.
- Use pagination, filtering, and sorting for lists.
- Do not query permissions or heavy relationships repeatedly from Blade.
- Mutating actions require authorization in PHP, not only hidden buttons.

## Output

Create or update:

- Livewire component class
- Livewire Blade view
- route entry
- service method
- docs for UI state and permissions

Run `npm run build` when asset files change.
