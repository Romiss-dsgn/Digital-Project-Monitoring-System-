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
  "access_token": "<token>",
  "refresh_token": "<token>"
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
  "contract_number": "BFP-R2-CON-2024-010",
  "project_id": 1,
  "contractor_id": 1,
  "contract_title": "Construction Contract - Sample Fire Station",
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

Use `multipart/form-data` for engineering plan uploads.

Query parameters for list:

```text
page=1
per_page=10
type=structural
status=for_review
search=fire station
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
- Refreshes the list from the database after upload.

Pending work should add show, authenticated download, review/status update, archive, and audit logging.

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

## Planned API Roadmap

These endpoints are planning targets. Do not treat them as implemented until `php artisan route:list` confirms them.

### Dashboard

| Method | Planned endpoint | Purpose |
| --- | --- | --- |
| GET | `/dashboard/summary` | Global cards: active projects, ongoing projects, total budget, overdue documents. |
| GET | `/dashboard/project-status` | Chart data for project status. |
| GET | `/dashboard/budget-vs-expenditure` | Chart data for budget/expenditure comparison. |
| GET | `/dashboard/recent-activity` | Latest important audit/project events. |

### Project Plans

Use this as the future unified planning area for Infrastructure Plans and Engineering Plans.

| Method | Planned endpoint | Purpose |
| --- | --- | --- |
| GET | `/project-plans/summary` | Project plan cards and counts. |
| GET | `/project-plans` | Combined list of infrastructure and engineering plan records. |
| GET | `/admin/engineering-plans/{engineeringPlan}` | Read one engineering plan. |
| PATCH | `/admin/engineering-plans/{engineeringPlan}` | Update metadata/status. |
| GET | `/admin/engineering-plans/{engineeringPlan}/download` | Authenticated file download. |
| PATCH | `/admin/engineering-plans/{engineeringPlan}/review` | Approve/reject engineering plan. |
| DELETE | `/admin/engineering-plans/{engineeringPlan}` | Archive engineering plan. |

### Financial Management

Use this as the future combined area for Cashflows and Variation Orders.

| Method | Planned endpoint | Purpose |
| --- | --- | --- |
| GET | `/financial-management/summary` | Financial cards for budget, disbursement, pending VOs. |
| GET | `/cashflow-periods` | List cashflow periods. |
| POST | `/cashflow-periods` | Create cashflow period. |
| PATCH | `/cashflow-periods/{cashflowPeriod}` | Update cashflow period. |
| DELETE | `/cashflow-periods/{cashflowPeriod}` | Archive cashflow period. |
| GET | `/variation-orders` | List variation orders. |
| POST | `/variation-orders` | Create variation order. |
| PATCH | `/variation-orders/{variationOrder}` | Update variation order. |
| PATCH | `/variation-orders/{variationOrder}/review` | Approve/reject variation order. |
| DELETE | `/variation-orders/{variationOrder}` | Archive variation order. |

Important rule:

- Approved variation orders should update revised contract value and affect cashflow summaries.

### Records And Reports

| Method | Planned endpoint | Purpose |
| --- | --- | --- |
| GET | `/reports/summary` | Report cards and available exports. |
| GET | `/reports/contracts` | Contract report rows. |
| GET | `/reports/accomplishments` | Accomplishment report rows. |
| GET | `/reports/financial` | Financial report rows. |
| GET | `/audit-logs` | Filterable audit trail. |
| GET | `/audit-logs/{auditLog}` | Read one audit event. |

### Notifications

| Method | Planned endpoint | Purpose |
| --- | --- | --- |
| GET | `/notifications` | List user/system notifications. |
| PATCH | `/notifications/{notification}/read` | Mark notification as read. |
| PATCH | `/notifications/read-all` | Mark all notifications as read. |

### Contractor Performance

| Method | Planned endpoint | Purpose |
| --- | --- | --- |
| GET | `/contractor-performance` | List ratings. |
| POST | `/contractor-performance` | Create contractor performance rating. |
| PATCH | `/contractor-performance/{rating}` | Update rating. |
| DELETE | `/contractor-performance/{rating}` | Archive rating. |

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

