# ConTrackPro System Audit and E2E Test Plan

Date checked: 2026-07-11

This document records the current repo-level audit, known defects, API/frontend connection status, cleanup candidates, and end-to-end scenarios for QA.

## Current Verification Summary

### Passed Checks

- Frontend production build passed with `npm run build`.
- Backend route list for `/api/v2` loads successfully in Docker.
- Authenticated API smoke checks returned `200 OK` after reseeding and recreating the Passport personal access client:
  - `GET /api/v2/me`
  - `GET /api/v2/admin/projects`
  - `GET /api/v2/admin/engineering-plans`
  - `GET /api/v2/contracts`
  - `GET /api/v2/contract-management/summary`
  - `GET /api/v2/project-accomplishments`
  - `GET /api/v2/project-accomplishments/summary`
  - `GET /api/v2/cashflow-periods`
  - `GET /api/v2/cashflow-periods/summary`
  - `GET /api/v2/invoices`
  - `GET /api/v2/variation-orders`
  - `GET /api/v2/variation-orders/summary`
  - `GET /api/v2/admin/reports/project-status`
  - `GET /api/v2/admin/audit-logs/stats`
  - `GET /api/v2/admin/users/stats`

### Important Local Setup Notes

If login shows `Personal access client not found. Please create one.`, run:

```powershell
docker compose exec backend php artisan passport:keys --force
docker compose exec backend php artisan passport:client --personal --name="ConTrackPro Personal Access Client" --no-interaction
docker compose exec backend php artisan optimize:clear
```

Seed data can be restored with:

```powershell
docker compose exec backend php artisan db:seed --force
```

## Current Logical Flow

The intended MVP flow is now mostly consistent:

1. Infrastructure Plans creates the project and contractor link.
2. Engineering Plans uploads technical documents against an existing project.
3. Project Accomplishments records progress against an existing project.
4. Contract Management creates contracts tied to projects and contractors.
5. Variation Orders modify contract value/time after review.
6. Cashflows, invoices, and payments track planned vs actual disbursement.
7. Reports and Audit Logs provide monitoring output.
8. User Management controls roles and module permissions.

The biggest remaining product gap is the main Dashboard. It is still mostly static/hardcoded while Reports already uses backend data.

## Known Issues Found

### 1. Backend tests can disturb the local development database

`backend/phpunit.xml` does not configure a separate test database. Some tests use `RefreshDatabase`, so running `php artisan test` inside the Docker backend can wipe/rebuild the normal `contrackpro` database if no separate `.env.testing` exists.

Recommended fix:

- Add a real `.env.testing` and separate database such as `contrackpro_test`, or configure sqlite in-memory only if all migrations/queries are compatible.
- Do not rely on the normal Docker development database for automated tests.

### 2. Some backend feature tests are stale

Current `php artisan test` result: 23 passed, 6 failed.

Known stale failures:

- `ProjectControllerTest` still creates a project without `contractor_id` or `new_contractor_name`, but the current ProjectController correctly requires one.
- `VariationOrderTest` expects `reviewed_at` to equal a past timestamp that the test never writes before review.
- `CashflowPeriodTest` has strict JSON numeric assertions and summary expectations that are brittle when the test database is not isolated.

### 3. Profile image upload service has no matching backend route

`frontend/src/services/profile.service.js` calls:

```text
POST /api/v2/uploads/users/{id}/profile-image
```

No matching route exists in `backend/routes/api.php`. The request also does not include `Authorization`. Profile text fields use `/api/v2/me` correctly, but image upload should be treated as unsupported until a backend route is added or the frontend method is removed.

### 4. Main Dashboard is mostly static

`frontend/src/views/Dashboard.vue` still hardcodes charts, recent updates, deadlines, fiscal years, and summary values. It should be the next module to connect to real summary endpoints after current module stabilization.

Fixed during this audit:

- Dashboard quick action buttons were using non-existing route names like `add-project`, `upload-document`, `add-contract`, `create-vo`, and `generate-report`. They now route to existing module routes.

### 5. Some action menus still depend on Bootstrap dropdown behavior

The Cashflow and Engineering Plans row action menus have been changed to Vue-controlled overlays. Remaining module views still using Bootstrap dropdown toggles should be checked because this is the same pattern that caused intermittent action-button bugs:

- `frontend/src/views/modules/VariationOrders.vue`
- `frontend/src/views/modules/ContractorPerformance.vue`
- `frontend/src/views/modules/ContractManagement.vue`

### 6. Documentation is behind the current code

Docs that need updating:

- `PLAN.md` and `README.md` still say Cashflows and Variation Orders are not connected, but current backend routes and frontend services are connected.
- `frontend/README.md` and `backend/README.md` also describe Cashflows/Variation Orders as pending.
- `API.md` says login returns `refresh_token`, but current `LoginController` returns `token_type`, `expires_in`, and `access_token`.
- `API.md` Project Accomplishments sample payload is stale versus `ProjectAccomplishmentController`.

## Cleanup Candidates

Do not delete these blindly; verify route/sidebar usage first.

- `backend/app/Models/list-user.php`
  - This is not a valid namespaced Laravel model. It is a one-off script placed inside `app/Models`. Move it to `scripts/` or delete it.
- `test-engineering-plan-permissions.ps1`
  - Useful QA script, but it is root-level clutter. Move to `scripts/qa/` and document expected seed users.
- Template/demo frontend routes still exist but are not part of the ConTrackPro sidebar flow:
  - `frontend/src/views/Tables.vue`
  - `frontend/src/views/Billing.vue`
  - `frontend/src/views/Rtl.vue`
  - `frontend/src/views/Notifications.vue`
  - `frontend/src/views/SignIn.vue`
  - `frontend/src/views/SignUp.vue`
- Static or not-yet-integrated modules:
  - `frontend/src/views/modules/ContractorPerformance.vue`
  - `frontend/src/views/modules/NotificationsInbox.vue`

## E2E Scenario 1: Project Planning to Engineering to Accomplishment

Goal: Confirm the core project lifecycle uses Infrastructure Plans as the source of truth.

Primary method: GUI, with Postman/API checks after each major step.

Preconditions:

- Docker backend is running at `http://127.0.0.1:8000`.
- Frontend is running at `http://localhost:8080`.
- Admin can log in with `admin@contrackpro.test` / `password`.
- Passport personal access client exists.

Steps:

1. Log in as System Administrator.
2. Open Dashboard, click `Add Project`, and confirm it routes to Infrastructure Plans.
3. In Infrastructure Plans, create a new project.
   - Use an existing contractor from the dropdown, or use the add-new-contractor flow.
   - Save and confirm the project appears in the table.
4. Open Engineering Plans.
   - Confirm the project dropdown contains the newly created project.
   - Upload an engineering plan document linked to that project.
   - Change status through the allowed review path.
5. Open Project Accomplishments.
   - Confirm the same project is available.
   - Create a progress/milestone report linked to that project.
   - Validate/approve the accomplishment if the role has permission.
6. Open Reports.
   - Generate/refresh Project Status Report.
   - Confirm the project appears with updated status/completion.
7. Open Audit Logs.
   - Confirm project creation, engineering plan upload/review, and accomplishment actions were recorded.

Expected API checks:

```http
GET /api/v2/admin/projects
GET /api/v2/admin/engineering-plans
GET /api/v2/project-accomplishments
GET /api/v2/admin/reports/project-status
GET /api/v2/admin/audit-logs
```

Pass criteria:

- Every record is tied to the same `project_id`.
- Engineering Plans and Accomplishments do not create standalone projects.
- Status/permissions are enforced by role.
- Audit trail records the important changes.

## E2E Scenario 2: Contract, Variation Order, Cashflow, Invoice, Payment

Goal: Confirm the financial flow is connected and contract value changes propagate logically.

Primary method: GUI plus Postman for exact response validation.

Preconditions:

- At least one active project and contractor exist.
- Admin or authorized contract/cashflow user is logged in.

Steps:

1. Open Contract Management.
   - Create a contract linked to an existing project and contractor.
   - Confirm it appears in the contract table and summary cards.
2. Upload a contract document.
   - Approve/review the document if permissions allow.
3. Open Variation Orders.
   - Create a draft variation order for the contract.
   - Submit it.
   - Review/approve it.
4. Return to Contract Management.
   - Confirm the revised contract amount changed by the approved variation amount.
5. Open Cashflows Management.
   - Create a cashflow period for the same contract.
   - Create an invoice under that contract/period.
   - Verify then approve the invoice.
   - Record a partial payment, then a final payment.
6. Confirm summaries.
   - Cashflow planned total, actual total, variance, invoice status, and remaining balance update correctly.
7. Open Audit Logs.
   - Confirm contract, variation order, invoice, and payment actions are logged.

Expected API checks:

```http
GET /api/v2/contracts
GET /api/v2/contract-management/summary
GET /api/v2/variation-orders
GET /api/v2/variation-orders/summary
GET /api/v2/cashflow-periods
GET /api/v2/cashflow-periods/summary
GET /api/v2/invoices
GET /api/v2/invoices/summary
```

Pass criteria:

- Variation order approval updates contract revised amount only once.
- Invoice cannot be overpaid.
- Partial payment keeps invoice open and exposes remaining balance.
- Full payment marks invoice paid and updates cashflow actuals.
- Audit logs reflect financial changes.

## E2E Scenario 3: Registration, Approval, RBAC, and Profile

Goal: Confirm user access request, approval, permissions, sidebar visibility, and profile behavior.

Primary method: GUI plus Postman for forbidden/allowed checks.

Steps:

1. Log out.
2. Register a new user from the public signup/access request page.
3. Try logging in immediately.
   - Expected: blocked or pending approval message.
4. Log in as System Administrator.
5. Open User Management.
   - Find the access request.
   - Approve it and assign a role.
6. Log in as the newly approved user.
   - Confirm sidebar modules match the assigned role permissions.
   - Try opening an allowed module.
   - Try accessing a forbidden module URL directly.
7. Open Profile.
   - Update normal profile fields.
   - Do not treat profile image upload as passed until a backend upload route exists.
8. Log back in as admin and check Audit Logs/User Management stats.

Expected API checks:

```http
POST /api/v2/register
POST /api/v2/login
GET /api/v2/admin/access-requests
PATCH /api/v2/admin/access-requests/{id}/approve
GET /api/v2/me
PATCH /api/v2/me
GET /api/v2/admin/users/stats
```

Pass criteria:

- Pending users cannot access protected modules.
- Approved users can log in.
- Sidebar uses real `module_permissions`.
- API returns `403` for forbidden modules even if the route is manually opened.
- Profile field updates work through `/me`.

## E2E Scenario 4: Full API Smoke Test for Postman

Goal: Verify the backend API surface is alive before GUI testing.

Steps:

1. Login:

```http
POST /api/v2/login
Content-Type: application/json

{
  "email": "admin@contrackpro.test",
  "password": "password"
}
```

2. Store `access_token` as `{{token}}`.
3. Send each request with:

```http
Authorization: Bearer {{token}}
Accept: application/json
```

4. Check these endpoints:

```http
GET /api/v2/me
GET /api/v2/admin/projects/options
GET /api/v2/admin/projects
GET /api/v2/admin/engineering-plans
GET /api/v2/contracts
GET /api/v2/contract-management/options
GET /api/v2/contract-management/summary
GET /api/v2/project-accomplishments/options
GET /api/v2/project-accomplishments/summary
GET /api/v2/cashflow-periods/options
GET /api/v2/cashflow-periods/summary
GET /api/v2/invoices/options
GET /api/v2/invoices/summary
GET /api/v2/variation-orders/options
GET /api/v2/variation-orders/summary
GET /api/v2/admin/reports/project-status
GET /api/v2/admin/audit-logs/stats
GET /api/v2/admin/users/stats
GET /api/v2/admin/roles
```

Pass criteria:

- All endpoints return `200 OK` for admin.
- Options endpoints return data needed by frontend dropdowns.
- Summary endpoints return numeric totals without server errors.
- Unauthorized request without bearer token returns `401`.
- Forbidden request using a low-permission user returns `403`.

## Recommended Next Module Task

Next high-value module task: connect the main Dashboard to real backend summary data.

Recommended implementation order:

1. Add a backend dashboard summary endpoint, for example `GET /api/v2/admin/dashboard/summary`.
2. Aggregate existing module data:
   - Projects by status and completion.
   - Contract totals and active contracts.
   - Cashflow planned vs actual.
   - Pending/approved variation orders.
   - Recent audit logs.
   - Upcoming project/contract deadlines.
3. Add `frontend/src/services/dashboard.service.js`.
4. Replace Dashboard hardcoded cards/charts/recent updates with API data.
5. Keep the current Dashboard layout, but add loading and error states.
6. Add Postman smoke tests and one feature test for the summary endpoint.

