# ConTrackPro API Documentation

This document describes the current ConTrackPro backend API and the planned API surface for future MVP modules. It is intended for frontend developers, backend developers, testers, and future maintainers.

## API Overview

Base URLs:

```text
Docker/local backend: http://localhost:8000/api/v2
Frontend env value:   http://127.0.0.1:8000/api/v2
Postman baseUrl:      http://localhost:8000/api
Postman route style:  {{baseUrl}}/v2/login
```

Standard JSON headers:

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <access_token>
```

Use `multipart/form-data` only for endpoints that upload files.

## Authentication

### Login

```http
POST /login
```

Request:

```json
{
  "email": "admin@contrackpro.test",
  "password": "password"
}
```

Response:

```json
{
  "token_type": "Bearer",
  "expires_in": 31536000,
  "access_token": "<token>"
}
```

Frontend behavior:

- Save `access_token`.
- Send it as `Authorization: Bearer <token>` for protected routes.
- Redirect to dashboard only after the token is stored.

### Current Auth And Profile Routes

| Method | Endpoint | Auth | Purpose |
| --- | --- | --- | --- |
| POST | `/login` | Public | Issue Passport access token. |
| POST | `/logout` | Required | Revoke/end active session. |
| POST | `/register` | Public | Submit personnel access request. |
| POST | `/password-forgot` | Public | Request password reset. |
| POST | `/password-reset` | Public | Reset password with token. |
| GET | `/me` | Required | Read authenticated user profile. |
| PATCH | `/me` | Required | Update authenticated user profile. |

## Current Module APIs

### Dashboard

Dashboard endpoints are DB-backed and use connected project, contract, financial, document, and audit data.

| Method | Endpoint | Permission | Purpose |
| --- | --- | --- | --- |
| GET | `/admin/dashboard/summary` | `dashboard:view` | Global dashboard cards, charts, alerts, and recent activity. |
| GET | `/admin/dashboard/export` | `dashboard:export` | Export dashboard summary data. |

### Contract Management

These endpoints are DB-backed and connected to the `projects`, `contractors`, `contracts`, `contract_documents`, and `audit_logs` tables.

| Method | Endpoint | Permission | Purpose |
| --- | --- | --- | --- |
| GET | `/contract-management/summary` | `contracts:view` | Dashboard cards for contract module. |
| GET | `/contract-management/options` | `contracts:view` | Projects, contractors, and filter options. |
| GET | `/contracts` | `contracts:view` | Paginated/filterable contract list. |
| POST | `/contracts` | `contracts:create` | Create a contract record. |
| GET | `/contracts/{contract}` | `contracts:view` | Read one contract. |
| PATCH | `/contracts/{contract}` | `contracts:edit` | Update one contract. |
| DELETE | `/contracts/{contract}` | `contracts:delete` | Archive one contract. |
| POST | `/contracts/{contract}/documents` | `contract_documents:create` | Upload supporting documents. |
| GET | `/contract-documents/{document}/download` | `contract_documents:view` | Download private document. |
| PATCH | `/contract-documents/{document}/status` | `contract_documents:approve` | Review/approve/reject document. |
| DELETE | `/contract-documents/{document}` | `contract_documents:delete` | Archive document. |

Create contract request:

```json
{
  "contract_number": "LGU-TUAO-CON-2026-010",
  "project_id": 1,
  "contractor_id": 1,
  "contract_title": "Improvement Contract - Sample Municipal Facility",
  "contract_type": "Infrastructure Works",
  "original_contract_amount": 18500000,
  "start_date": "2026-05-04",
  "end_date": "2026-12-30",
  "status": "Active"
}
```

Implementation notes:

- Backend validates IDs against the database.
- Archive is preferred over hard delete for audit history.
- Contract document downloads must stay authenticated.
- Mutating actions should write audit log entries.

### Project Accomplishments

These endpoints are DB-backed and connected to the `projects`, `contracts`, `project_accomplishments`, `accomplishment_documents`, and `audit_logs` tables.

| Method | Endpoint | Permission | Purpose |
| --- | --- | --- | --- |
| GET | `/project-accomplishments/summary` | `project_accomplishments:view` | Summary cards for accomplishment module. |
| GET | `/project-accomplishments/options` | `project_accomplishments:view` | Projects/contracts/status options. |
| GET | `/project-accomplishments` | `project_accomplishments:view` | Paginated/filterable accomplishment list. |
| POST | `/project-accomplishments` | `project_accomplishments:create` | Create accomplishment record. |
| GET | `/project-accomplishments/{accomplishment}` | `project_accomplishments:view` | Read one accomplishment. |
| PATCH | `/project-accomplishments/{accomplishment}` | `project_accomplishments:edit` | Update accomplishment record. |
| PATCH | `/project-accomplishments/{accomplishment}/validate` | `project_accomplishments:approve` | Validate or review accomplishment. |
| DELETE | `/project-accomplishments/{accomplishment}` | `project_accomplishments:delete` | Archive accomplishment record. |
| POST | `/project-accomplishments/{accomplishment}/documents` | `project_accomplishments:create` | Upload proof/supporting document. |
| GET | `/accomplishment-documents/{document}/download` | `project_accomplishments:view` | Download private accomplishment document. |

Create accomplishment request:

```json
{
  "project_id": 1,
  "contract_id": 1,
  "reporting_period": "2026-06",
  "physical_progress": 65,
  "financial_progress": 58,
  "status": "Validated",
  "remarks": "Structural works completed and inspected."
}
```

Implementation notes:

- Accomplishment progress should update project progress when accepted.
- Validation is a separate endpoint because it is a review action.
- Uploaded proof documents remain private.

### Cashflows, Invoices, And Payments

These endpoints are DB-backed and connected to `contracts`, `cashflow_periods`, `invoices`, `invoice_documents`, `payments`, and `audit_logs`.

| Method | Endpoint | Permission | Purpose |
| --- | --- | --- | --- |
| GET | `/cashflow-periods/summary` | `cashflow_periods:view` | Cashflow summary cards and chart totals. |
| GET | `/cashflow-periods/options` | `cashflow_periods:view` | Contract and filter options for cashflow forms. |
| GET | `/cashflow-periods` | `cashflow_periods:view` | Paginated/filterable cashflow period list. |
| POST | `/cashflow-periods` | `cashflow_periods:create` | Create cashflow period. |
| GET | `/cashflow-periods/{period}` | `cashflow_periods:view` | Read one cashflow period. |
| PATCH | `/cashflow-periods/{period}` | `cashflow_periods:edit` | Update cashflow period. |
| DELETE | `/cashflow-periods/{period}` | `cashflow_periods:delete` | Archive cashflow period. |
| GET | `/cashflow-periods/{period}/invoices` | `invoices:view` | List invoices for one period. |
| GET | `/invoices/summary` | `invoices:view` | Invoice summary cards. |
| GET | `/invoices/options` | `invoices:view` | Invoice form options. |
| GET | `/invoices` | `invoices:view` | Paginated/filterable invoice list. |
| POST | `/invoices` | `invoices:create` | Create invoice. |
| GET | `/invoices/{invoice}` | `invoices:view` | Read one invoice. |
| PATCH | `/invoices/{invoice}` | `invoices:edit` | Update invoice. |
| PATCH | `/invoices/{invoice}/verify` | `invoices:create` | Mark invoice verified. |
| PATCH | `/invoices/{invoice}/approve` | `invoices:approve` | Approve invoice. |
| DELETE | `/invoices/{invoice}` | `invoices:delete` | Archive invoice. |
| POST | `/invoices/{invoice}/documents` | `invoices:create` | Upload invoice support document. |
| GET | `/invoice-documents/{document}/download` | `invoices:view` | Download private invoice document. |
| POST | `/payments` | `invoices:create` | Record payment against an invoice. |

### Variation Orders

These endpoints are DB-backed and connected to `contracts`, `variation_orders`, `variation_order_documents`, and `audit_logs`.

| Method | Endpoint | Permission | Purpose |
| --- | --- | --- | --- |
| GET | `/variation-orders/summary` | `variation_orders:view` | VO cards and financial totals. |
| GET | `/variation-orders/options` | `variation_orders:view` | Contract and filter options. |
| GET | `/variation-orders` | `variation_orders:view` | Paginated/filterable VO list. |
| POST | `/variation-orders` | `variation_orders:create` | Create draft VO. |
| GET | `/variation-orders/{order}` | `variation_orders:view` | Read one VO. |
| PATCH | `/variation-orders/{order}` | `variation_orders:edit` | Update draft/reviewable VO metadata. |
| PATCH | `/variation-orders/{order}/submit` | `variation_orders:create` | Submit draft VO for review. |
| PATCH | `/variation-orders/{order}/review` | `variation_orders:approve` | Approve/reject/review VO. |
| DELETE | `/variation-orders/{order}` | `variation_orders:delete` | Archive VO. |
| POST | `/variation-orders/{order}/documents` | `variation_orders:create` | Upload VO document. |
| GET | `/variation-order-documents/{document}/download` | `variation_orders:view` | Download private VO document. |

Important rule:

- Approved variation orders update the contract revised amount once and affect financial summaries.

### Admin Access Requests

These endpoints support the request-access workflow from registration to approved active user.

| Method | Endpoint | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/admin/access-requests` | Required | List pending/approved/rejected access requests. |
| PATCH | `/admin/access-requests/{accessRequest}/approve` | Required | Approve access request and create/activate user. |
| PATCH | `/admin/access-requests/{accessRequest}/reject` | Required | Reject access request. |

Approve request:

```json
{
  "password": "password123"
}
```

### Admin User Management

These endpoints are for system administrators.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/admin/users/stats` | User dashboard counts. |
| GET | `/admin/users` | List users. |
| POST | `/admin/users` | Create user. |
| GET | `/admin/users/{user}` | Read user. |
| PUT | `/admin/users/{user}` | Update user. |
| PATCH | `/admin/users/{user}/status` | Activate/deactivate user. |
| DELETE | `/admin/users/{user}` | Delete/archive user, depending implementation. |
| POST | `/admin/users/{user}/accept` | Accept pending user. |
| DELETE | `/admin/users/{user}/reject` | Reject pending user. |
| GET | `/admin/roles` | List available roles. |

### Projects And Infrastructure Plans

These endpoints are the backend foundation for Infrastructure Plans / Project Plans.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/admin/projects/options` | Lightweight project/contractor options for dropdowns. |
| GET | `/admin/projects` | List projects. |
| POST | `/admin/projects` | Create project. |
| PATCH | `/admin/projects/{project}` | Update project. |
| DELETE | `/admin/projects/{project}` | Delete/archive project. |

### Engineering Plans

These endpoints are DB-backed for the current Engineering Plans MVP. Engineering plans are linked to existing project records from `projects`.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/admin/engineering-plans` | Paginated/filterable engineering plan document list with summary stats. |
| POST | `/admin/engineering-plans` | Upload/store engineering plan record and file. |
| GET | `/admin/engineering-plans/{engineeringPlan}` | Show one engineering plan record. |
| GET | `/admin/engineering-plans/{engineeringPlan}/download` | Authenticated download for the stored plan file. |
| PATCH | `/admin/engineering-plans/{engineeringPlan}/status` | Update review status, reviewer, review timestamp, and remarks. |
| DELETE | `/admin/engineering-plans/{engineeringPlan}` | Archive an engineering plan record. |

Use `multipart/form-data` for engineering plan uploads.

Query parameters for list:

```text
page=1
per_page=10
type=structural
status=for_review
search=municipal hall
```

Upload fields:

```text
project_id      required, existing project ID
plan_title      required
plan_type       required, e.g. architectural, structural, electrical, mechanical
version         optional
review_status   optional, e.g. for_review, approved, revision, uploaded
remarks         optional
file            required for upload flow
```

Current frontend behavior:

- Loads rows and stats from `GET /admin/engineering-plans`.
- Loads project options from the project API.
- Requires an existing project before upload.
- Uploads plan files through `POST /admin/engineering-plans`.
- Downloads stored files through the authenticated download endpoint.
- Updates review status from the row actions.
- Archives records instead of hard-deleting them.
- Refreshes the list from the database after write actions.

### Reports

Reports are DB-backed read-only endpoints for the connected MVP modules.

| Method | Endpoint | Permission | Purpose |
| --- | --- | --- | --- |
| GET | `/admin/reports/project-status` | `reports:view` | Project status report data. |
| GET | `/admin/reports/{reportType}` | `reports:view` | Report rows for supported report types such as `contract-summary`, `cashflow-analysis`, and `variation-orders`. |
| GET | `/admin/reports/{reportType}/export` | `reports:export` | Export supported report data. |

### Audit Logs

Audit logs are DB-backed and should be written by important create, update, upload, review, archive, export, and approval actions.

| Method | Endpoint | Permission | Purpose |
| --- | --- | --- | --- |
| GET | `/admin/audit-logs` | `audit_logs:view` | Filterable audit log list. |
| GET | `/admin/audit-logs/stats` | `audit_logs:view` | Audit log summary counters. |
| GET | `/admin/audit-logs/modules` | `audit_logs:view` | Available module filters. |
| GET | `/admin/audit-logs/roles` | `audit_logs:view` | Available role filters. |
| POST | `/admin/audit-logs/export` | `audit_logs:export` | Export audit log rows. |

### JSON:API User Resource

The project still exposes Laravel JSON:API user resource routes.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/users` | List users through JSON:API server. |
| POST | `/users` | Create user through JSON:API server. |
| GET | `/users/{user}` | Read user through JSON:API server. |
| PATCH | `/users/{user}` | Update user through JSON:API server. |
| DELETE | `/users/{user}` | Delete user through JSON:API server. |

Prefer the module-specific admin user endpoints for ConTrackPro UI work unless the team intentionally keeps JSON:API resources for that screen.

## Future Or Deferred API Notes

The current MVP already exposes Dashboard, Project Plans, Engineering Plans, Contract Management, Cashflows, Variation Orders, Project Accomplishments, Reports, Audit Logs, User Management, and Settings/Profile routes.

Deferred modules:

| Module | Current MVP decision |
| --- | --- |
| Notifications | Out of MVP. Keep hidden/redirected until notification generation, read state, permissions, and QA flow are built. |
| Contractor Performance | Out of MVP. Keep hidden/redirected until contractor rating workflow, permissions, seed rules, and reports are built. |

Possible future consolidation:

- A `/project-plans/*` namespace may later combine Infrastructure Plans and Engineering Plans.
- A `/financial-management/*` namespace may later combine cashflows, invoices, payments, and variation orders.
- Keep the existing module-specific endpoints stable while the MVP is being tested.

## Database Tables By Module

| Module | Main tables |
| --- | --- |
| Auth/User Management | `users`, `roles`, `role_permissions`, `access_requests` |
| Project Plans | `projects`, `project_documents`, `engineering_plans` |
| Contract Management | `contractors`, `contracts`, `contract_documents` |
| Project Accomplishments | `project_accomplishments`, `accomplishment_documents` |
| Financial Management | `cashflow_periods`, `invoices`, `payments`, `variation_orders`, `variation_order_documents` |
| Records and Reports | source module tables, `audit_logs` |
| Notifications | `notifications`, `notification_recipients` |
| Contractor Performance | `contractor_performance_ratings` |

## API Design Rules

- Keep all frontend-used endpoints versioned under `/api/v2`.
- Use module-specific `summary` and `options` endpoints to avoid hardcoding dashboard cards and dropdowns in Vue.
- Use request validation for every create/update endpoint.
- Use permission middleware for module access.
- Keep uploaded files private and expose them only through authenticated download endpoints.
- Archive historical project records instead of hard deleting when accountability matters.
- Write audit logs for create, update, delete/archive, upload, approve, reject, and validation actions.
- Avoid adding broad generic endpoints when a module-specific endpoint is clearer for the UI.

## Postman Testing Checklist

1. Set `baseUrl` to `http://localhost:8000/api`.
2. Login with `POST {{baseUrl}}/v2/login`.
3. Copy `access_token`.
4. Use Authorization type `Bearer Token`.
5. Test `GET {{baseUrl}}/v2/me`.
6. Test current module endpoints.

Useful route check:

```powershell
docker compose exec backend php artisan route:list --path=api/v2
```

Useful migration/seeding check:

```powershell
docker compose exec backend php artisan migrate:status
docker compose exec backend php artisan db:seed --force
```

## Common API Errors

| Error | Likely cause | Fix |
| --- | --- | --- |
| 401 Unauthorized | Missing/expired bearer token. | Login again and copy the new access token. |
| 403 Forbidden | User role lacks module permission. | Check `role_permissions` seed/data. |
| 404 Not Found | Wrong route prefix. | Use `/api/v2`, not `/api` only. |
| 422 Unprocessable Content | Backend validation failed. | Check response errors and required fields. |
| 500 Invalid key supplied | Passport keys are stale/missing. | Run `passport:keys --force` and `optimize:clear`. |
| Frontend calls `/api/login` | Frontend `.env` is wrong or dev server was not restarted. | Set `VUE_APP_API_BASE_URL=http://127.0.0.1:8000/api/v2`. |

