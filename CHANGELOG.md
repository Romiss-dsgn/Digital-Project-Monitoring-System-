# Changelog

All notable project-owned changes to ConTrackPro are documented here.

## Unreleased

### Documentation

- Rewrote the root README around the current ConTrackPro MVP state.
- Replaced inherited backend/frontend starter README content with project-specific documentation.
- Added `API.md` for current endpoints, planned endpoints, request examples, tables, API rules, and testing notes.
- Expanded the root README with software architecture and deployment planning notes.
- Updated Docker documentation with setup, seeding, migration, phpMyAdmin, and troubleshooting commands.
- Added `PLAN.md` for module sequencing, module merge decisions, branch rules, and PR checklist.
- Updated the issue template to include module, branch, API route, DB table, and testing context.

### Current Working Scope

- Docker setup runs Laravel backend, MySQL, and phpMyAdmin.
- Vue frontend runs separately through `npm run serve`.
- Authentication, request access, user approval, profile, and admin user management foundations exist.
- Contract Management is DB-backed for core CRUD/archive, documents, summaries, permissions, audit logs, seeders, and tests.
- Project Accomplishments is DB-backed for core CRUD/archive/validation, documents, summaries, project progress sync, seeders, and tests.
- Infrastructure Plans / Projects backend and service foundation exist.
- Engineering Plans upload foundation exists.

### Pending MVP Scope

- Finish User Management frontend integration.
- Finish Infrastructure Plans / Project Plans DB-backed flow.
- Finish Engineering Plans listing/review/download/archive flow.
- Implement Financial Management by connecting Variation Orders first, then Cashflows.
- Implement Records & Reports with read-only reports and audit log views.
- Replace remaining static dashboard/module data with DB-backed summaries.
