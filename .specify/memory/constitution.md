<!--
Sync Impact Report
- Version change: N/A → 1.0.0
- Added principles:
  - I. Actions-First Business Logic (NON-NEGOTIABLE)
  - II. Multi-Panel Separation with Shared Foundation
  - III. Enum-Driven State Management (NON-NEGOTIABLE)
  - IV. Audit Trail and Activity Logging
  - V. Test Coverage with Pest
  - VI. Internationalization by Default (NON-NEGOTIABLE)
  - VII. Policy-Based Authorization
  - VIII. Database Convention Discipline
- Added sections:
  - Technical Constraints
  - Development Workflow
  - Governance
- Templates requiring updates:
  - .specify/templates/plan-template.md ✅ updated
  - .specify/templates/tasks-template.md ✅ updated
  - .specify/templates/spec-template.md ✅ no changes needed
- Follow-up TODOs: none
-->

# Lazos de Fe Constitution

## Core Principles

### I. Actions-First Business Logic (NON-NEGOTIABLE)

All business logic MUST reside in `app/Actions/` classes using `lorisleiva/laravel-actions` with the `AsAction` trait. Controllers, Livewire components, and Filament Resources MUST NOT contain business logic beyond input validation and delegation. Actions MUST be organized by panel context (`Actions/Admin/`, `Actions/Member/`) when panel-specific, or at the `Actions/` root when shared. Every Action MUST be independently callable via `::run()` for testability.

### II. Multi-Panel Separation with Shared Foundation

The project operates three Filament panels: Admin (`/admin`), Member (`/member`), and Venture (`/app`). Each panel MUST have its own namespace under `app/Filament/{Panel}/`. Cross-panel shared logic MUST live in `app/Filament/Shared/Resources/` and be extended (not duplicated) by panel-specific Resources. Panel-specific Resources MUST only add panel-relevant relations, pages, navigation, and query scoping. Each panel MUST use its own authentication guard. New features MUST declare which panel(s) they target.

### III. Enum-Driven State Management (NON-NEGOTIABLE)

All entity states and workflow transitions MUST be modeled as PHP 8.3 backed enums implementing `Filament\Support\Contracts\HasLabel`. Enums MUST use integer backing values. State labels MUST use Laravel translation keys (`__()`) — never hardcoded strings. State transitions MUST occur exclusively within Action classes, never in Models, Controllers, or Resource hooks. New stateful entities MUST define their enum before any implementation begins.

### IV. Audit Trail and Activity Logging

All state-changing operations MUST be logged via `spatie/laravel-activitylog` using the `Util::getActivityLog()` helper pattern. Logs MUST capture: the event name, the causer (authenticated user), and the client IP address. Comment trails on models MUST use the polymorphic `Comments` relation via `addComment()`. Every approval, rejection, activation, or deactivation MUST produce both an activity log entry and a model comment.

### V. Test Coverage with Pest

All new features MUST include Pest tests. Feature tests go in `tests/Feature/`, unit tests in `tests/Unit/`. Tests MUST use the testing database configuration from `phpunit.xml` (SQLite/testing DB, array cache, array mail, sync queue). Every Action class MUST have at least one feature test exercising the happy path and one test for the primary error path. Filament Resource pages SHOULD have Livewire render tests using `pest-plugin-livewire`.

### VI. Internationalization by Default (NON-NEGOTIABLE)

All user-facing strings MUST use Laravel's `__()` translation helper with structured key paths (e.g., `models/venture.fields.title`, `common.enums.venture-approval-state.approved`). Translation keys MUST follow the convention `{domain}/{model}.{section}.{key}`. Filament form labels, table column labels, enum labels, notification messages, and validation messages MUST NOT contain hardcoded Spanish or English strings. Translation files live in `lang/`.

### VII. Policy-Based Authorization

Every Filament Resource MUST have a corresponding Policy extending `BasePolicy`. Policies MUST implement the standard Filament authorization methods (`viewAny`, `view`, `create`, `update`, `delete`). The `before()` method in `BasePolicy` grants admin users full access; this pattern MUST be preserved. Permission checks MUST use `hasPermission()` with a panel-prefixed key. Features that bypass authorization (like Member's `$shouldSkipAuthorization`) MUST document the justification.

### VIII. Database Convention Discipline

Every schema change MUST be a timestamped migration — never manual SQL. Every Model MUST have a corresponding Factory in `database/factories/` and a Seeder in `database/seeders/`. Models MUST use `$guarded = []` with explicit `$casts` for non-string columns. Polymorphic relations (Comments, Media, Categories) MUST use Laravel's standard `morphMany`/`morphToMany` conventions. New polymorphic types MUST reuse the existing `commentable`, `ownable`, or `categorizable` morph names.

## Technical Constraints

### Stack Requirements

- **Runtime**: PHP 8.3+, Laravel 11.x, Filament 3.3.x
- **Development**: Laravel Sail with MySQL 8.0, Mailpit, Redis
- **Production**: Caddy + PHP-FPM Alpine + MariaDB (see `docker-compose.prod.yml`)
- **Frontend**: Tailwind CSS 3.4, Vite — no custom JavaScript frameworks
- **Code Style**: PHP-CS-Fixer with Laravel Shift preset, 2-space indentation
- **Dependencies**: New Composer packages MUST be justified in the spec; prefer existing packages (`spatie/laravel-activitylog`, `lorisleiva/laravel-actions`, `filament/*`) over new additions

### File Organization Rules

- `app/Actions/{Panel}/` — Panel-specific business logic
- `app/Enums/` — All state enums (flat, no subdirectories)
- `app/Filament/{Panel}/Resources/` — Panel resources extending shared base
- `app/Filament/Shared/Resources/` — Shared base resources
- `app/Helpers/` — Utility classes (`Util`, `AppUtil`, `AppMacros`)
- `app/Mail/` — Mailable classes organized by context
- `app/Models/` — Eloquent models (flat, traits in `Models/Traits/`)
- `app/Policies/` — Authorization policies extending `BasePolicy`

### Security Requirements

- Authentication MUST use panel-specific guards (`admin`, `member`, `web`)
- Public-facing forms MUST use Captcha (`marcogermani87/filament-captcha`)
- API endpoints MUST use Sanctum token authentication
- All user input MUST be validated before reaching Action classes
- File uploads MUST be stored on the `public` disk with proper cleanup on deletion

## Development Workflow

### Feature Development Process

1. **Spec First**: Every feature MUST have a specification in `specs/{###-feature-name}/spec.md` before implementation begins
2. **Constitution Check**: The plan's Constitution Check section MUST verify compliance with all principles before Phase 0 research
3. **Branch Convention**: Feature branches MUST follow `{###-feature-name}` format (e.g., `002-member-dashboard`)
4. **Action-Test-Resource Order**: Implement in order: (a) Enum if stateful, (b) Migration + Model, (c) Action class, (d) Pest tests for Action, (e) Filament Resource/Pages, (f) Mailable if notifications needed
5. **Code Style Gate**: `./vendor/bin/pint` MUST pass with zero changes before any PR
6. **Docker-First Development**: All development and testing MUST run through Sail (`./vendor/bin/sail`). Direct `php artisan` on the host is discouraged
7. **README Update**: The last task of every spec MUST update `README.md` to reflect any new features, commands, configuration, services, or architectural changes introduced by the spec. Sections affected (e.g., Características, Stack Tecnológico, Arquitectura, Comandos Útiles) MUST be kept in sync with the current state of the application

### Notification Pattern

- State change notifications MUST use dedicated Mailable classes in `app/Mail/`
- In-app notifications MUST use `Util::filamentNotification()` helper
- Email + in-app notification SHOULD be paired for approval/rejection workflows

### Error Handling Pattern

- Action classes MUST throw exceptions for invalid state transitions
- Filament Resource actions MUST wrap Action calls with `Util::run()` for graceful error handling
- Log-level errors MUST use `Util::logChange()` with appropriate category and level

## Governance

This Constitution supersedes all ad-hoc practices. All feature specifications, implementation plans, and task lists MUST verify compliance with these principles. Amendments require:

1. A documented rationale explaining why the current principle is insufficient
2. An impact analysis of existing code affected by the change
3. A migration plan for bringing existing code into compliance (or explicit grandfathering)
4. Version bump and date update below

All PRs and code reviews MUST verify compliance with this Constitution. The plan-template.md Constitution Check section enforces this gate. Use CLAUDE.md for runtime development guidance that supplements (but never contradicts) this Constitution.

**Version**: 1.1.0 | **Ratified**: 2026-03-23 | **Last Amended**: 2026-03-23
