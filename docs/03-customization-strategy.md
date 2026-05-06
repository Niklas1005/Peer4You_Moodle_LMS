# Customization Strategy (Upgrade-Safe)

## Goal
Implement company-specific features (dashboard, communication, workflows) while preserving maintainability and update compatibility.

## Recommended Customization Layers
1. **Theme customization**
   - Branding, colors, typography, layout adjustments.
   - Front page and dashboard presentation.
   - Active implementation: `theme_p4u` (inherits from Boost, no core modifications).
2. **Plugins**
   - Add chat/board features with existing plugins first.
   - Create custom local/block plugin only where needed.
3. **Integrations**
   - SSO, external messaging, analytics, or internal tools via APIs.

## Avoid
- Direct edits in Moodle core files.
- Hardcoded business logic in core templates.
- One-off patches without documentation.

## Delivery Model
- `main`: stable baseline.
- Feature branches for each customization.
- Separate docs per feature:
  - purpose
  - functional requirements
  - technical design
  - validation steps

## Acceptance Criteria Template
- Functional behavior matches use case.
- No regressions for admin/teacher/student core workflows.
- Works after Moodle minor update in staging.
- Documentation updated (architecture + operation + rollback).

## Current Implementation Baseline
- Theme plugin path: `public/theme/p4u`.
- Key files:
  - `config.php` (layout mapping and Boost parent setup)
  - `layout/frontpage.php` and `layout/mydashboard.php` (page composition)
  - `templates/frontpage.mustache` and `templates/mydashboard.mustache` (UI structure)
  - `scss/p4u.scss` (prototype token and component styles)
- Runtime activation:
  - Theme configured via CLI `admin/cli/cfg.php --name=theme --set=p4u`
  - Caches purged via `admin/cli/purge_caches.php`
