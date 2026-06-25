# ConTrackPro MVP Plan

This plan documents the current module strategy, what is already working, and the recommended next work. Keep this file updated whenever a module is merged into `staging`.

## Product Goal

Build a practical MVP for BFP Region II contract and project monitoring.

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
| Dashboard | Partial | UI exists. Needs real metrics from connected modules. |
| User Management | In progress | Backend admin endpoints exist. Frontend module is active branch work. |
| Infrastructure Plans | In progress | Project CRUD backend and service exist. Frontend connection still needs hardening. |
| Engineering Plans | Partial | Upload/store exists. Needs DB-backed listing, review, download, archive. |
| Contract Management | MVP connected | DB-backed CRUD/archive, documents, summary, permissions, audit logs, seeders, tests. |
| Project Accomplishments | MVP connected | DB-backed CRUD/archive/validate, documents, summary, project progress sync, seeders, tests. |
| Cashflows | Not connected | UI exists. Backend tables/models exist. |
| Variation Orders | Not connected | UI exists. Backend tables/models exist. |
| Reports | Not connected | UI exists. Needs reporting endpoints. |
| Audit Logs | Partial | Audit log writing exists. Read-only audit module API pending. |
| Notifications | Not connected | UI and tables/models exist. Workflow pending. |
| Contractor Performance | Not connected | UI and table/model exist. Workflow pending. |

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

### 1. Stabilize User Management

Owner: `user-management` branch.

Backend:

- Confirm admin user CRUD works.
- Confirm accept/reject access requests.
- Confirm role dropdown uses `roles`.
- Confirm active/inactive user updates.

Frontend:

- Remove static user rows.
- Load users from `/api/v2/admin/users`.
- Add user create/edit/status actions.
- Show role and active status.

Tests:

- Admin can list users.
- Admin can create/update/deactivate user.
- Non-admin is rejected if permissions are enforced.

### 2. Finish Project Plans Foundation

Owner: `infrastructure-plans` or `project-plans` branch.

Backend:

- Finalize `ProjectController`.
- Confirm `projects` validation matches schema.
- Add summary endpoint for plan/project dashboard metrics.
- Add audit logging for create/update/archive.

Frontend:

- Replace static infrastructure plan data.
- Use `project.service.js`.
- Add create/edit/archive project flow.
- Make Engineering Plans project dropdown use real projects.

Tests:

- Project CRUD.
- Project archive.
- Project list filters.

### 3. Finish Engineering Plans

Owner: `engineering-plans` branch.

Backend:

- Add list endpoint.
- Add show endpoint.
- Add download endpoint.
- Add status/review endpoint.
- Add archive endpoint.
- Add audit logging.

Frontend:

- Replace static engineering document list.
- Add loading/empty/error states.
- Add download/review/archive actions.
- Keep upload modal connected.

Tests:

- Upload stores private file.
- Review changes status.
- Download requires auth.

### 4. Build Financial Management

Owner: new `financial-management` branch.

Start with Variation Orders because it changes contract value.

Backend:

- Variation order CRUD.
- VO document upload.
- VO approval/rejection.
- Approved VO should affect revised contract amount or be included in contract financial summary.

Frontend:

- Replace static VO table.
- Add VO create/edit/approve/reject.
- Show contract link and amount impact.

Then add Cashflows:

- Cashflow periods by contract.
- Billing/payment status.
- Disbursement summary.
- Budget vs expenditure chart from DB.

Tests:

- VO approval updates financial summary.
- Cashflow summary calculates totals from DB.

### 5. Records and Reports

Owner: new `records-reports` branch.

Backend:

- Audit log index endpoint.
- Report summary endpoints.
- Filters by module, user, date, project, contract.

Frontend:

- Replace static reports/audit logs.
- Add export-ready views.

Tests:

- Audit logs are created by write actions.
- Audit logs are read-only.

### 6. Dashboard Metrics

Owner: new `dashboard-metrics` branch.

Backend:

- Dashboard summary endpoint.
- Pull from projects, contracts, accomplishments, variation orders, cashflows, documents.

Frontend:

- Replace static cards/charts.
- Add loading and empty states.

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
