# Implementation Plan: [FEATURE]

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]
**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

[Extract from feature spec: primary requirement + technical approach from research]

## Technical Context

**Language/Version**: PHP 8.3+ / Laravel 11
**Primary Dependencies**: Filament 3.3, lorisleiva/laravel-actions, spatie/laravel-activitylog
**Storage**: MySQL 8.0 (dev/Sail), MariaDB (prod)
**Testing**: Pest 2.x with pest-plugin-laravel and pest-plugin-livewire
**Target Platform**: Web (Docker: Sail dev, Caddy+PHP-FPM prod)
**Project Type**: web-service (multi-panel Filament admin)
**Constraints**: Spanish-first i18n, faith-based community context
**Scale/Scope**: Community members, ventures, approval workflows

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [ ] **Actions-First**: All business logic planned for `app/Actions/` — no logic in controllers/resources
- [ ] **Panel Separation**: Target panel(s) identified; shared resources in `Filament/Shared/`
- [ ] **Enum States**: All new stateful entities have enums defined
- [ ] **Audit Trail**: State changes include activity logging and comment trail
- [ ] **Pest Tests**: Test plan includes feature tests for all Actions
- [ ] **i18n**: All user-facing strings use `__()` with translation keys
- [ ] **Authorization**: Policies defined for new resources, extending `BasePolicy`
- [ ] **Database Conventions**: Migrations, factories, and seeders planned for all new models

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
app/
├── Actions/{Panel}/        # Business logic (lorisleiva/laravel-actions)
├── Enums/                  # State enums (HasLabel)
├── Filament/
│   ├── Admin/Resources/    # Admin panel resources
│   ├── Member/Resources/   # Member panel resources
│   ├── Shared/Resources/   # Shared base resources
│   └── Venture/Resources/  # Public venture panel resources
├── Helpers/                # Util, AppUtil, AppMacros
├── Mail/                   # Mailable classes
├── Models/                 # Eloquent models
│   └── Traits/             # Model traits
└── Policies/               # BasePolicy + model policies

database/
├── factories/              # Model factories
├── migrations/             # Timestamped migrations
└── seeders/                # Model seeders

tests/
├── Feature/                # Feature/integration tests
│   ├── Admin/              # Admin panel tests
│   ├── Member/             # Member panel tests
│   └── Shared/             # Cross-panel tests
└── Unit/                   # Unit tests
```

**Structure Decision**: Laravel 11 multi-panel Filament architecture with Actions pattern

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
