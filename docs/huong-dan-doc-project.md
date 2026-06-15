
<Module_Name>="Account"

Step 01:
Read docs/NAME_PROJECT_ANALYSIS.md

Based on the analysis:

Create a ROADMAP.md

Classify tasks:

P0 = Critical
P1 = Important
P2 = Nice to have

Estimate:
- Complexity
- Risk
- Impact

Generate implementation order.

<Module_Name>="Account"
Step 02:
You are a Senior Laravel 12 Architect.

Read ROADMAP.md first.

Analyze this module only:

Modules/<Module_Name>

Do not change any code yet.

Generate this file:

docs/modules/<Module_Name>/ANALYSIS.md

Please analyze by this flow:

Route
→ Controller
→ Page Blade
→ Livewire PHP
→ Livewire Blade
→ Shared Components
→ Service
→ Import/Export
→ Model
→ Migration

Include in ANALYSIS.md:

1. Module purpose
2. Route list
3. Controllers
4. Page Blade files
5. Livewire PHP classes
6. Livewire Blade views
7. Services and public methods
8. Models and database tables
9. Import/Export classes
10. Authorization/security risks
11. Validation problems
12. Transaction risks
13. N+1/query performance risks
14. Duplicate logic
15. Files that look unused
16. Refactor plan:
   - P0 Critical
   - P1 Important
   - P2 Nice to have

Important rules:
- Do not refactor now.
- Do not edit code now.
- Do not touch unrelated modules.
- Every issue must include exact file path.
- Every recommendation must include priority: P0, P1, or P2.

Step 03:
You are a Senior Laravel 12 Architect.

Read ROADMAP.md first.

Analyze this module only:

Modules/<Module_Name>

Do not change any code yet.

Generate this file:

docs/modules/<Module_Name>/ANALYSIS.md

Please analyze by this flow:

Route
→ Controller
→ Page Blade
→ Livewire PHP
→ Livewire Blade
→ Shared Components
→ Service
→ Import/Export
→ Model
→ Migration

Include in ANALYSIS.md:

1. Module purpose
2. Route list
3. Controllers
4. Page Blade files
5. Livewire PHP classes
6. Livewire Blade views
7. Services and public methods
8. Models and database tables
9. Import/Export classes
10. Authorization/security risks
11. Validation problems
12. Transaction risks
13. N+1/query performance risks
14. Duplicate logic
15. Files that look unused
16. Refactor plan:
   - P0 Critical
   - P1 Important
   - P2 Nice to have

Important rules:
- Do not refactor now.
- Do not edit code now.
- Do not touch unrelated modules.
- Every issue must include exact file path.
- Every recommendation must include priority: P0, P1, or P2.

Step 04:
Read:

docs/modules/<Module_Name>/ANALYSIS.md 
ROADMAP.md

Do not write code yet.

Create:

docs/modules/<Module_Name>/REFACTOR_PLAN.md

Requirements:

For every issue found in ANALYSIS.md:

1. Explain root cause
2. Explain business impact
3. Explain technical impact
4. Estimate risk
5. Estimate complexity

Group into:

P0 Critical
P1 Important
P2 Nice to have

For each item generate:

* Issue
* Root Cause
* Proposed Solution
* Files To Change
* Estimated Risk
* Estimated Effort

Then generate:

## Recommended Implementation Order

Phase 1
Phase 2
Phase 3

Important:

Do not generate code.

Focus on architecture quality,
maintainability,
performance,
security,
Laravel 12 best practices,
Livewire 3 best practices.

Every recommendation must contain exact file paths.

Step 05:
docs/modules/<Module_Name>/ANALYSIS.md
ROADMAP.md
docs/modules/<Module_Name>/REFACTOR_PLAN.md

Do not write code.

Generate:

docs/modules/<Module_Name>/REBUILD_SPEC.md 

Include:

1. Database Design
2. Model Design
3. Service Design
4. Livewire Design
5. Import Design
6. Export Design
7. Permissions
8. Transactions
9. UI Components
10. Test Strategy

This document will become the source of truth for implementation.

Use:
Cấp độ 1 — 

composer.json
        ↓
ModuleServiceProvider.php
        ↓
PROJECT_BOOTSTRAP.md
        ↓
ROADMAP.md
        ↓
ANALYSIS.md
        ↓
REFACTOR_PLAN.md
        ↓
REBUILD_SPEC.md
        ↓
FULL CODE

Cấp độ 2 — 
Rewrite Module: Modules/<Module_Name>
Không sửa code cũ.
Thiết kế lại hoàn toàn Module <Module_Name> theo kiến trúc chuẩn.

Cấp độ 3 — Generate Full Module From Specification
Bạn đã có:
ANALYSIS.md
ROADMAP.md
REFACTOR_PLAN.md
REBUILD_SPEC.md
Sau đó yêu cầu Codex:
Read:

docs/modules/<Module_Name>/ANALYSIS.md
docs/modules/<Module_Name>/REFACTOR_PLAN.md
docs/modules/<Module_Name>/REBUILD_SPEC.md
ROADMAP.md

Generate a completely new version of:

Modules/<Module_Name>

Do not modify existing code.

Create a new implementation following:

Laravel 12
Livewire 3
Bootstrap/AdminLTE
Module Architecture

Required Structure:

Modules/<Module_Name>/

├── Routes/
├── Livewire/
├── Services/
├── Import/
├── Export/
├── Models/
├── Database/
├── Resources/views/

Architecture:

Route
→ Controller
→ Page Blade
→ Livewire PHP
→ Livewire Blade
→ Shared UI Panel
→ Service
→ Import
→ Export
→ Model
→ Database

Requirements:

1. Apply all P0 fixes.
2. Apply all P1 improvements.
3. Ignore P2 unless easy.
4. Business logic only in Services.
5. ImportExport.php must be thin.
6. Use Shared ImportExport foundation.
7. Use transactions.
8. Prevent N+1 queries.
9. Add validation.
10. Add authorization.
11. Generate complete code.

Output:

A. Folder structure
B. Migration
C. Model
D. Service
E. Import
F. Export
G. Livewire PHP
H. Livewire Blade
I. Routes
J. Tests

Generate files one by one.


-------------------------
MODULE_NAME=Category

Before doing anything, read these files in order:

1. docs/CODEX_BOOTSTRAP.md
2. docs/AI_PROJECT_CONTEXT.md
3. docs/PROJECT_BOOTSTRAP.md
4. ROADMAP.md
5. docs/modules/Category/ANALYSIS.md
6. docs/modules/Category/REFACTOR_PLAN.md
7. docs/modules/Category/REBUILD_SPEC.md

Then refactor the existing module safely:

Modules/Category

Goal:

Rewrite/refactor the Category module according to REBUILD_SPEC.md.

Important rules:

* Follow the actual module autoload architecture from docs/PROJECT_BOOTSTRAP.md.
* Follow the coding standards from docs/AI_PROJECT_CONTEXT.md.
* Follow the implementation priorities from ROADMAP.md.
* Follow the module-specific analysis, refactor plan, and rebuild spec.
* Do not modify unrelated modules.
* Do not create a new ServiceProvider unless PROJECT_BOOTSTRAP.md requires it.
* Do not change composer.json unless absolutely required.
* Preserve existing database compatibility unless REBUILD_SPEC.md explicitly says otherwise.
* Preserve existing routes and Livewire aliases unless REBUILD_SPEC.md explicitly says otherwise.
* Keep business logic in Services.
* Keep Livewire focused on UI state and actions.
* Keep ImportExport.php as a thin orchestrator.
* Use transactions for multi-record writes.
* Add authorization checks for mutating actions.
* Add validation before persistence.
* Prevent N+1 queries.

Implementation order:

1. List all files that will be changed or created.
2. Explain the change plan briefly.
3. Implement P0 items first.
4. Then implement P1 items.
5. Ignore P2 unless safe and clearly isolated.
6. Generate or update tests where possible.
7. Generate:

docs/modules/Category/IMPLEMENTATION_SUMMARY.md

Include:

* Files changed
* What was implemented
* Remaining risks
* Tests added or recommended
* Manual verification checklist
