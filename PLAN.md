# ConTrackPro MVP Plan

This plan documents the current module strategy, what is already working, and the recommended next work. Keep this file updated whenever a module is merged into `staging`.

## Product Goal

Build a practical MVP for LGU Tuao contract and project monitoring.

The MVP should prove these workflows first:

1. Authorized users can log in and access only allowed modules.
2. Admins can approve and manage users.
3. Projects can be created and monitored.
4. Contracts can be created, updated, archived, and linked to contractors/projects.
5. Documents can be uploaded and reviewed.
6. Accomplishments can update project progress.
7. Financial modules can track contract changes and payments.
8. Reports and audit logs can support accountability.

## Current Module Status

| Module | Status | Notes |
| --- | --- | --- |
| Dashboard | MVP connected | Summary/export endpoints read connected projects, contracts, cashflow, VOs, documents, and alerts. |
| User Management | MVP connected | Admin users, roles, stats, access request approval/rejection, and frontend management screen are connected. |
| Infrastructure Plans | MVP connected | DB-backed project register, summary cards, filters, create/edit/archive flow, audit logging, and UI cleanup are active. This remains the only project creation workflow. |
| Engineering Plans | MVP connected | DB-backed listing, summary cards, pagination, project dropdown, upload, download, review status updates, archive, and audit logging exist. |
| Contract Management | MVP connected | DB-backed CRUD/archive, documents, summary, permissions, audit logs, seeders, tests. |
| Project Accomplishments | MVP connected | DB-backed CRUD/archive/validate, documents, summary, project progress sync, seeders, tests. |
| Cashflows | MVP connected | Cashflow periods, invoices, payments, summaries, options, documents, and seed data are connected. |
| Variation Orders | MVP connected | VO CRUD/lifecycle, documents, summaries, contract revised amount impact, seed data, and tests are connected. |
| Reports | MVP connected | Admin report endpoints and frontend views read connected source module data. |
| Audit Logs | MVP connected | Important writes are logged and the read-only audit API/frontend module is connected. |
| Notifications | Out of MVP | Hidden/redirected until the workflow is intentionally built. |
| Contractor Performance | Out of MVP | Hidden/redirected until contractor rating workflow is intentionally built. |

## Recommended Final Module Structure

```text
Dashboard
Project Plans
  - Infrastructure Plans
  - Engineering Plans
Contract Management
Financial Management
  - Cashflows
  - Variation Orders
Project Accomplishments
Records & Reports
  - Reports
  - Audit Logs
User Management
Settings
```

## Module Merge Decisions

### Project Ownership Rule

For the MVP, do not create duplicate "Add Project" flows across modules.

Infrastructure Plans is the source of truth for project records:

- Project code
- Project name
- Location
- Contractor
- Budget
- Timeline
- Phase
- Overall status
- Latest summary progress

Engineering Plans and Project Accomplishments must use existing project records from `projects`.

```text
Infrastructure Plans
  creates and edits the project

Engineering Plans
  uploads and reviews technical documents for that project

Project Accomplishments
  records milestone/progress evidence for that project
```

This keeps the database clean and avoids different modules creating conflicting versions of the same project.

### Project Plans

Merge conceptually:

- Infrastructure Plans
- Engineering Plans

Reason:

- Infrastructure Plans represent the project/facility record.
- Engineering Plans are supporting technical documents.
- They belong to the same planning workflow.

MVP implementation:

- Keep separate menu items for now if needed.
- Share the same `projects` data.
- Engineering plan records should belong to a project.
- Later, this can become one `Project Plans` module with tabs.

### Infrastructure Plans vs Project Accomplishments

These modules both show progress, but they are not redundant.

Infrastructure Plans is the project registry and planning baseline:

- One row per project.
- Stores the latest high-level status and progress.
- Answers: "What project exists, where is it, who owns it, and what is its current state?"

Project Accomplishments is the progress evidence/history:

- Many accomplishment records can belong to one project.
- Stores milestone updates, report attachments, validation status, and progress details.
- Answers: "What work was completed, when was it reported, and what evidence supports the progress?"

MVP rule:

- Add/edit project only in Infrastructure Plans.
- Add milestone/report only in Project Accomplishments.
- When an accomplishment is validated, it may update or support the project summary progress shown in Infrastructure Plans.

### UI Naming Guidance

Do not rename the sidebar modules yet. Keep the current labels so the team avoids unnecessary router/sidebar churn:

```text
Infrastructure Plans
Engineering Plans
Project Accomplishments
```

Instead, add short page descriptions under each module title:

- Infrastructure Plans: "Register and manage project baselines, locations, budgets, contractors, timelines, and overall status."
- Engineering Plans: "Upload, review, and track technical drawings and engineering documents linked to existing projects."
- Project Accomplishments: "Record milestone progress, completion evidence, validation status, and accomplishment reports for existing projects."

This explains the purpose of each module without changing routes, menu labels, or user expectations.

### Financial Management

Merge conceptually:

- Cashflows
- Variation Orders

Reason:

- Cashflow tracks budget releases, billings, disbursements, and payment schedules.
- Variation orders change scope, time, and contract amount.
- Approved variation orders should affect revised contract amount and cashflow expectations.

MVP implementation:

- Build one backend domain sequence around `contracts`.
- Add variation order CRUD first.
- Approved VO should update or expose revised contract value.
- Add cashflow periods/payment tracking after VO data is stable.
- UI can remain separate screens or become one Financial Management screen with tabs.

### Records and Reports

Possible merge:

- Reports
- Audit Logs

Reason:

- Both are mostly read-only monitoring/accountability views.
- Reports summarize system records.
- Audit logs show who changed records.

Caveat:

- Keep separate if normal users can view reports but only administrators can view audit logs.

## Do Not Merge

Keep these standalone:

- Contract Management
- Project Accomplishments
- User Management
- Dashboard

Reason:

- Contract Management is the core domain that other modules depend on.
- Project Accomplishments is operational progress tracking, not finance.
- User Management is admin-only identity/access control.
- Dashboard is the system overview.

## Recommended Next Work Sequence

### 1. Keep The QA Dataset Small And Repeatable

The default seed should remain useful for E2E, not a fake production database.

- Keep 3 users, 3 contractors, 3 projects, 3 contracts, 3 VOs, 3 cashflow periods, 3 invoices, and 3 accomplishments.
- Keep engineering plans and document tables empty after seed.
- Create PDF/DOCX/PNG records during E2E upload tests.
- Rerun `migrate:fresh --seed` when checking cross-module flow.

### 2. Expand Test Coverage Around Connected Modules

Current backend tests cover core project, contract, cashflow, variation order, and accomplishment behavior. The next tests should protect the highest-risk summaries and exports.

- Add/keep feature tests for dashboard summary and export.
- Add/keep feature tests for seeded variation order lifecycle and contract revised amount behavior.
- Add/keep feature tests for report endpoints and audit log filters.
- Run tests through `scripts/qa/run-backend-tests.ps1` so the local development database is not used as the test database.

### 3. Run E2E Before Each Staging Merge

Use the runbook in `SYSTEM_AUDIT_AND_E2E_TEST_PLAN.md`.

- Run API smoke with `scripts/qa/api-smoke.ps1`.
- Run engineering permissions smoke with `scripts/qa/test-engineering-plan-permissions.ps1`.
- Run backend tests with `scripts/qa/run-backend-tests.ps1`.
- Run frontend build with `npm run build` from `frontend/`.
- Check browser DevTools for 401/403/404/422/500 errors and action-menu issues.

### 4. Keep Out-Of-MVP Screens Hidden

Notifications and Contractor Performance are not in the current MVP. Do not expose them in the sidebar until real APIs, permissions, seed rules, and E2E scenarios exist.

- Legacy direct routes can redirect to Dashboard.
- Do not add new static rows for those screens.
- Revisit them only after the connected MVP flow is stable.

### 5. Polish Connected UI Flows

Use Vue-controlled row action overlays on connected table modules so row menus work consistently at the top, middle, and bottom of tables.

- Keep table actions independent from Bootstrap dropdown JavaScript.
- Keep long text truncated or intentionally wrapped.
- Keep loading, empty, error, and permission-denied states visible.
- Keep write buttons disabled while requests are in progress.

## Branching Workflow

Start module branch from latest staging:

```powershell
git fetch origin
git switch staging
git pull origin staging
git switch -c module-name
```

Before PR:

```powershell
git fetch origin
git switch module-name
git merge origin/staging
```

If conflicts happen:

```powershell
git status
rg -n "<<<<<<<|=======|>>>>>>>" .
```

After resolving:

```powershell
git add -A
git commit -m "merge staging into module-name"
git push origin module-name
```

## Pull Request Checklist

- Branch is updated with latest `staging`.
- No conflict markers remain.
- Backend tests pass if backend code changed.
- Frontend build passes if frontend code changed.
- New migrations are documented.
- New seeders are added to `DatabaseSeeder`.
- New routes are listed in the PR description.
- UI screenshots are attached for frontend changes.
- Postman or browser testing steps are included.

## MVP Quality Rules

- Prefer simple CRUD + summary + seed data over complex workflows.
- One module should be fully connected before starting another.
- Avoid adding static mock data once backend endpoints exist.
- Archive instead of deleting records that affect audit/report history.
- Keep file uploads private and require authenticated download endpoints.
- Add audit logs for important write actions.
- Add only the tests needed to protect the module workflow.
