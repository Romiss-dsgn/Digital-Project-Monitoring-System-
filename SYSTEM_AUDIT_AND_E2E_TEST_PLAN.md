# ConTrackPro QA Runbook and E2E Test Plan

Date checked: 2026-07-18

This runbook is for testing the current ConTrackPro MVP end to end through the GUI, Postman, browser devtools, and database checks.

## Current Code Reality

The current code is ahead of some older docs. Test against the routes and services in the repo, not only the status tables in `PLAN.md`.

Connected MVP modules:

- Auth, logout, registration/access request, profile read/update.
- Dashboard summary and export via `/api/v2/admin/dashboard/summary` and `/api/v2/admin/dashboard/export`.
- Infrastructure Plans through `/api/v2/admin/projects`.
- Engineering Plans through `/api/v2/admin/engineering-plans`.
- Contract Management and contract documents.
- Project Accomplishments and accomplishment documents.
- Cashflow periods, invoices, invoice documents, and payments.
- Variation Orders and variation order documents.
- Reports through `/api/v2/admin/reports/...`.
- Audit Logs through `/api/v2/admin/audit-logs`.
- User Management and roles.

Out of MVP for this pass:

- Contractor Performance is hidden/redirected until the workflow is intentionally built.
- Notifications Inbox is hidden/redirected until the workflow is intentionally built.

## Known Risks To Watch

1. Backend tests must run against the isolated test database.
   - Fixed in this pass: `backend/phpunit.xml` points test runs to `contrackpro_testing`.
   - Use `scripts/qa/run-backend-tests.ps1` instead of running raw tests against the local development DB.

2. Keep docs aligned with route reality.
   - Fixed in this pass: README, PLAN, API, Docker, backend README, and frontend README now describe connected dashboard/finance/reporting/audit modules.
   - Fixed in this pass: `API.md` no longer documents a `refresh_token` in the login response.

3. Some out-of-MVP screens may still contain template-era UI.
   - Contractor Performance and Notifications are hidden/redirected for MVP and should not be used as acceptance criteria.
   - Connected table modules should use Vue-controlled action overlays.

4. Profile image upload is unsupported by design for this MVP pass.
   - Fixed in this pass: the frontend profile service no longer calls the missing upload route.

5. Seed data needs to be small, repeatable, and file-free.
   - Fixed in this pass: the default QA seed now creates 3 users, 3 contractors, 3 projects, 3 contracts, 3 variation orders, 3 cashflow periods, 3 invoices, and 3 project accomplishments.
   - Fixed in this pass: `EngineeringPlansSeeder` creates no active placeholder file rows; engineering plans and all supporting documents should be uploaded during E2E.
   - Fixed in this pass: `ContractManagementSeeder` project statuses use lowercase values, and `ContractManagementController::summary()` counts the same active project statuses used by the dashboard.

## Local QA Reset

Use this when you want a known test baseline.

```powershell
docker compose up -d --build
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed --force
docker compose exec backend php artisan passport:keys --force
docker compose exec backend php artisan passport:client --personal --name="ConTrackPro Personal Access Client" --no-interaction
docker compose exec backend php artisan optimize:clear
```

Use this only when you are okay destroying local data:

```powershell
docker compose exec backend php artisan migrate:fresh --seed
docker compose exec backend php artisan passport:keys --force
docker compose exec backend php artisan passport:client --personal --name="ConTrackPro Personal Access Client" --no-interaction
```

Expected fresh QA seed shape:

| Area | Expected active rows | Notes |
| --- | ---: | --- |
| Users | 3 | Admin, QA Planning Engineer, QA Contract Monitor. |
| Contractors | 3 | Parent records for project/contract dropdowns. |
| Projects | 3 | Infrastructure Plans is the project source of truth. |
| Contracts | 3 | Active, Pending Review, and Draft states. |
| Variation Orders | 3 | Approved, Under Review, and Draft states. |
| Cashflow Periods | 3 | One period per seeded contract. |
| Invoices | 3 | Paid, Approved, and Pending states. |
| Project Accomplishments | 3 | Completed, In Progress, and Delayed states. |
| Engineering Plans | 0 | Create by uploading files during E2E. |
| Document tables | 0 | Contract, invoice, VO, and accomplishment documents must be created by upload tests. |

Frontend:

```powershell
cd frontend
npm run serve
```

Admin seed login:

```text
email: admin@contrackpro.test
password: password
```

## Smoke Checks Before E2E

Backend route check:

```powershell
docker compose exec backend php artisan route:list --path=api/v2
```

Migration status:

```powershell
docker compose exec backend php artisan migrate:status
```

Frontend build:

```powershell
cd frontend
npm run build
```

Browser checks:

- Open DevTools Network tab.
- Enable Preserve log.
- Enable Disable cache while DevTools is open.
- Refresh `/dashboard` 5 to 10 times.
- Expected: no Settings-only sidebar flash, no wrong role flash, no red API errors.

## Postman Environment

Recommended variables:

```text
baseUrl = http://127.0.0.1:8000/api/v2
token =
projectId =
contractorId =
contractId =
engineeringPlanId =
accomplishmentId =
cashflowPeriodId =
invoiceId =
variationOrderId =
```

Login request:

```http
POST {{baseUrl}}/login
Accept: application/json
Content-Type: application/json

{
  "email": "admin@contrackpro.test",
  "password": "password"
}
```

Set `token` from `access_token`, then use:

```http
Authorization: Bearer {{token}}
Accept: application/json
```

## API Smoke Matrix

Run these before detailed CRUD:

```http
GET {{baseUrl}}/me
GET {{baseUrl}}/admin/dashboard/summary
GET {{baseUrl}}/admin/projects/options
GET {{baseUrl}}/admin/projects
GET {{baseUrl}}/admin/engineering-plans
GET {{baseUrl}}/contracts
GET {{baseUrl}}/contract-management/options
GET {{baseUrl}}/contract-management/summary
GET {{baseUrl}}/project-accomplishments/options
GET {{baseUrl}}/project-accomplishments/summary
GET {{baseUrl}}/cashflow-periods/options
GET {{baseUrl}}/cashflow-periods/summary
GET {{baseUrl}}/invoices/options
GET {{baseUrl}}/invoices/summary
GET {{baseUrl}}/variation-orders/options
GET {{baseUrl}}/variation-orders/summary
GET {{baseUrl}}/admin/reports/project-status
GET {{baseUrl}}/admin/reports/contract-summary
GET {{baseUrl}}/admin/reports/cashflow-analysis
GET {{baseUrl}}/admin/reports/variation-orders
GET {{baseUrl}}/admin/audit-logs/stats
GET {{baseUrl}}/admin/users/stats
GET {{baseUrl}}/admin/roles
```

Expected:

- Admin gets `200`.
- Missing bearer token gets `401`.
- Low-permission user gets `403` for unauthorized modules.

## E2E Scenario 1: Auth, RBAC, And Session Stability

Goal: catch login, stale token, permission, sidebar, and profile bugs.

GUI steps:

1. Open `/login`.
2. Login as admin.
3. Refresh `/dashboard` repeatedly.
4. Confirm sidebar modules do not temporarily collapse to Settings only.
5. Confirm navbar name/role stay correct.
6. Logout.
7. Manually set a bad token in DevTools:

```js
localStorage.setItem("user_free", JSON.stringify("bad-token"));
location.href = "/login";
```

8. Confirm app stays on Login or clears invalid session automatically.
9. Register a new access request.
10. Admin approves the request in User Management.
11. Login as the approved user and verify sidebar module visibility matches the assigned role.

Postman checks:

```http
POST {{baseUrl}}/register
GET {{baseUrl}}/admin/access-requests
PATCH {{baseUrl}}/admin/access-requests/{id}/approve
GET {{baseUrl}}/me
PATCH {{baseUrl}}/me
```

Pass criteria:

- No manual localStorage clearing is required for normal use.
- Invalid token does not trap the user on Dashboard.
- Permissions affect both sidebar visibility and API access.

## E2E Scenario 2: Project Planning Flow

Goal: verify Infrastructure Plans is the only project creation source.

GUI steps:

1. Open Dashboard and click Add Project.
2. Confirm route goes to Infrastructure Plans.
3. Create a project with either an existing contractor or a new contractor name.
4. Edit the project status, progress, and dates.
5. Archive a test project.
6. Confirm filters, pagination, summary cards, location distribution, and recent updates still work.

Postman create payload:

```json
{
  "code": "LGU-TUAO-PROJ-901",
  "name": "QA Municipal Facility Test Project",
  "location": "Municipal Hall Compound",
  "new_contractor_name": "QA Test Contractor",
  "startDate": "2026-07-15",
  "endDate": "2026-12-15",
  "budget": 2500000,
  "phase": "Planning",
  "status": "planning",
  "progress": 0,
  "notes": "Created during QA E2E testing."
}
```

API checks:

```http
POST {{baseUrl}}/admin/projects
PATCH {{baseUrl}}/admin/projects/{{projectId}}
DELETE {{baseUrl}}/admin/projects/{{projectId}}
GET {{baseUrl}}/admin/audit-logs?module=projects
```

Pass criteria:

- Project requires contractor selection or new contractor name.
- Engineering Plans and Accomplishments only consume existing projects.
- Project archive removes the record from active lists but audit history remains.

## E2E Scenario 3: Engineering Plans

Goal: verify documents are linked to existing projects.

GUI steps:

1. Open Engineering Plans.
2. Confirm project dropdown is loaded from backend projects.
3. Upload a small PDF/DOCX/PNG test file.
4. Mark it approved, require revision, and send back to review.
5. Download the file.
6. Archive the record.

Postman checks:

```http
GET {{baseUrl}}/admin/engineering-plans
POST {{baseUrl}}/admin/engineering-plans
PATCH {{baseUrl}}/admin/engineering-plans/{{engineeringPlanId}}/status
GET {{baseUrl}}/admin/engineering-plans/{{engineeringPlanId}}/download
DELETE {{baseUrl}}/admin/engineering-plans/{{engineeringPlanId}}
```

Negative tests:

- Upload without `project_id` should fail.
- Upload with non-existing project ID should fail.
- Download without token should fail.

## E2E Scenario 4: Contract And Document Flow

Goal: verify contracts depend on projects and contractors.

GUI steps:

1. Open Contract Management.
2. Create a contract for an existing project/contractor.
3. Edit contract dates/status/amount.
4. Upload a contract document.
5. Review/approve/reject document if role allows.
6. Archive a test contract.

Postman checks:

```http
GET {{baseUrl}}/contract-management/options
POST {{baseUrl}}/contracts
PATCH {{baseUrl}}/contracts/{{contractId}}
POST {{baseUrl}}/contracts/{{contractId}}/documents
PATCH {{baseUrl}}/contract-documents/{documentId}/status
DELETE {{baseUrl}}/contracts/{{contractId}}
```

Pass criteria:

- Contract cannot be created without valid `project_id` and `contractor_id`.
- Contract document downloads require auth.
- Audit logs record create/update/archive/document actions.

## E2E Scenario 5: Variation Order Financial Impact

Goal: verify VO lifecycle and revised contract amount behavior.

GUI steps:

1. Open Variation Orders.
2. Create a draft VO for an active contract.
3. Submit the VO.
4. Review it as Under Review.
5. Approve it.
6. Return to Contract Management and confirm revised contract amount increased once.
7. Try approving again and confirm it is rejected.
8. Upload and download a VO document.

Postman checks:

```http
POST {{baseUrl}}/variation-orders
PATCH {{baseUrl}}/variation-orders/{{variationOrderId}}/submit
PATCH {{baseUrl}}/variation-orders/{{variationOrderId}}/review
POST {{baseUrl}}/variation-orders/{{variationOrderId}}/documents
GET {{baseUrl}}/variation-order-documents/{documentId}/download
GET {{baseUrl}}/variation-orders/summary
```

Pass criteria:

- Draft to Submitted to Under Review to Approved is enforced.
- Approved VO updates contract revised amount once.
- Approved/rejected VO appears in Reports and Dashboard summary.

## E2E Scenario 6: Cashflow, Invoice, And Payment

Goal: verify planned vs actual disbursement.

GUI steps:

1. Open Cashflows Management.
2. Create a cashflow period for a contract.
3. Edit the cashflow period.
4. Create an invoice under the period.
5. Verify and approve the invoice.
6. Record a partial payment.
7. Record a final payment.
8. Confirm invoice status, remaining balance, cashflow actual amount, and variance update.
9. Attempt overpayment and confirm it fails.

Postman checks:

```http
POST {{baseUrl}}/cashflow-periods
PATCH {{baseUrl}}/cashflow-periods/{{cashflowPeriodId}}
POST {{baseUrl}}/invoices
PATCH {{baseUrl}}/invoices/{{invoiceId}}/verify
PATCH {{baseUrl}}/invoices/{{invoiceId}}/approve
POST {{baseUrl}}/payments
GET {{baseUrl}}/cashflow-periods/summary
GET {{baseUrl}}/invoices/summary
```

Pass criteria:

- Partial payment keeps invoice open with remaining balance.
- Full payment marks invoice as Paid.
- Overpayment is rejected before payment creation.
- Dashboard budget/expenditure chart reflects cashflow data.

## E2E Scenario 7: Accomplishments, Reports, And Audit Logs

Goal: verify progress evidence and accountability.

GUI steps:

1. Open Project Accomplishments.
2. Create a milestone for an existing project.
3. Upload proof document.
4. Validate the accomplishment.
5. Confirm project progress updates or is reflected in reports.
6. Open Reports and check Project Status, Contract Summary, Cashflow Analysis, and Variation Orders.
7. Export reports if available.
8. Open Audit Logs and filter by module/user/date.

Postman checks:

```http
POST {{baseUrl}}/project-accomplishments
PATCH {{baseUrl}}/project-accomplishments/{{accomplishmentId}}/validate
POST {{baseUrl}}/project-accomplishments/{{accomplishmentId}}/documents
GET {{baseUrl}}/admin/reports/project-status
GET {{baseUrl}}/admin/reports/contract-summary
GET {{baseUrl}}/admin/reports/cashflow-analysis
GET {{baseUrl}}/admin/reports/variation-orders
GET {{baseUrl}}/admin/audit-logs
POST {{baseUrl}}/admin/audit-logs/export
```

Pass criteria:

- Accomplishment requires existing project.
- Validation writes reviewer/validated metadata.
- Audit log entries exist for important writes.
- Report rows match source module records.

## Browser DevTools Checklist

Use this on every connected module page:

- Network has no unexpected `401`, `403`, `404`, `422`, or `500`.
- Failed validation displays a useful message, not a silent failure.
- No debug `console.log` noise remains.
- `console.error` only appears for actual failed API calls.
- Loading state appears during slow network.
- Empty state appears when filters return no data.
- Refresh does not duplicate records or duplicate API submissions.
- Action menus work on first row, middle row, and last row.
- Last-row menu does not expand table height.
- Long names/titles truncate or wrap cleanly.
- Pagination does not lose filters.

## Performance And Loading Checks

Local MVP targets:

- Initial protected route should not show wrong role/sidebar state.
- Dashboard summary should settle within about 2 seconds on local Docker.
- Module list pages should avoid repeated infinite requests.
- File upload should show loading/disabled state.
- Export buttons should disable while exporting.
- Chart pages should destroy old Chart.js instances before re-rendering.

Manual stress tests:

1. Refresh `/dashboard` 10 times.
2. Switch fiscal year quickly 5 times.
3. Change filters quickly on Infrastructure Plans and Variation Orders.
4. Open/close row action menus repeatedly.
5. Try slow network throttling in Chrome DevTools.

## Database Integrity Checks

Use phpMyAdmin or SQL after an E2E run:

```sql
select count(*) as active_projects from projects where is_archived = 0;
select count(*) as active_contracts from contracts where is_archived = 0;
select count(*) as active_variation_orders from variation_orders where is_archived = 0;
select count(*) as active_cashflow_periods from cashflow_periods where is_archived = 0;
select count(*) as invoices from invoices;
select count(*) as active_project_accomplishments from project_accomplishments where is_archived = 0;
select count(*) as active_engineering_plans_before_uploads from engineering_plans where is_archived = 0;
select 'contract_documents' as table_name, count(*) as rows_count from contract_documents
union all select 'invoice_documents', count(*) from invoice_documents
union all select 'variation_order_documents', count(*) from variation_order_documents
union all select 'accomplishment_documents', count(*) from accomplishment_documents;
select count(*) from projects where is_archived = 0 and contractor_id is null;
select count(*) from engineering_plans ep left join projects p on p.id = ep.project_id where ep.is_archived = 0 and p.id is null;
select count(*) from project_accomplishments pa left join projects p on p.id = pa.project_id where pa.is_archived = 0 and p.id is null;
select count(*) from contracts c left join projects p on p.id = c.project_id where c.is_archived = 0 and p.id is null;
select count(*) from contracts c left join contractors ct on ct.id = c.contractor_id where c.is_archived = 0 and ct.id is null;
select count(*) from cashflow_periods cp left join contracts c on c.id = cp.contract_id where cp.is_archived = 0 and c.id is null;
select count(*) from invoices i left join contracts c on c.id = i.contract_id where c.id is null;
select invoice_id, sum(amount_paid) as paid from payments group by invoice_id having paid < 0;
select module, action, count(*) from audit_logs group by module, action order by module, action;
```

Expected:

- Immediately after `migrate:fresh --seed`, active parent/workflow counts should match the seed table above.
- Before upload tests, engineering plans and document tables should be `0`.
- Orphan counts should be `0` for active records.
- Payment totals should never exceed invoice amount through the payment API.
- Audit logs should contain create/update/archive/review/upload actions after QA.

## Recommended Fix Backlog

Completed in this pass:

- Configured an isolated backend test database through `backend/phpunit.xml` and `scripts/qa/run-backend-tests.ps1`.
- Updated README, PLAN, API, Docker, backend README, frontend README, and this QA runbook to match connected MVP routes.
- Removed the unsupported frontend profile image upload call.
- Converted Contract Management and Variation Orders row actions to Vue-controlled fixed overlays.
- Added repeatable PowerShell QA scripts in `scripts/qa/`.
- Moved the engineering permission smoke script into `scripts/qa/`.
- Moved the user listing helper out of `backend/app/Models/` into `scripts/qa/`.
- Hid/redirected Notifications and Contractor Performance out of the MVP route surface.

Remaining high priority:

- Add feature tests for Dashboard summary/export and seeded Variation Orders.
- Run the full GUI E2E scenarios after each `migrate:fresh --seed`.

Remaining medium priority:

- Add an exportable Postman collection that mirrors `scripts/qa/api-smoke.ps1`.
- Revisit Notifications and Contractor Performance only after the connected MVP is stable.
