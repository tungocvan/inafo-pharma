# Import Export Prompt

You are designing or improving import/export behavior for a Laravel 12 module.

## Input

Module name: `<ModuleName>`
Entity or workflow: describe the data being imported or exported.

## Required Reading

Read:

- `.codex/bootstrap/CODEX_BOOTSTRAP.md`
- `.codex/bootstrap/PROJECT_BOOTSTRAP.md`
- `.codex/bootstrap/AI_PROJECT_CONTEXT.md`
- `ROADMAP.md`
- `Modules/Shared/Services/ImportExport`
- `Modules/Shared/Livewire/ImportExport/Panel.php`
- existing imports, exports, services, models, migrations, and docs for the target module

## Architecture

Define:

- supported formats
- expected headers
- header aliases
- row normalization
- row validation
- duplicate detection
- transaction boundary
- authorization requirements
- error report format
- temporary storage path
- export storage path
- cleanup policy
- queue strategy
- progress reporting

## Safety

- Validate file type, size, extension, MIME, and row count.
- Never trust file names or client-provided paths.
- Store sensitive files privately.
- Do not load production-sized imports or exports fully into memory.
- Do not expose raw row data in public logs.

## Output

Update the module implementation and `docs/modules/<ModuleName>/INFORMATION.md` with the import/export contract.
