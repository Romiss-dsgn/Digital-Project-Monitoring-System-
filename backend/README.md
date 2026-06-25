# ConTrackPro Backend

This folder contains the Laravel backend for ConTrackPro. It exposes the JSON/API endpoints used by the Vue frontend and owns database persistence, authentication, authorization, file storage, validation, and audit logging.

## Runtime

Recommended local runtime:

- Docker backend container
- PHP 8.2
- MySQL 8
- Laravel Passport

Avoid mixing Docker backend work with XAMPP backend work unless you intentionally maintain two separate databases.

## Important Files

```text
backend/
|-- app/
|   |-- Http/Controllers/Api/V2/
|   |-- Http/Middleware/EnsureModulePermission.php
|   |-- Models/
|   |-- Policies/
|   `-- Services/
|-- database/
|   |-- migrations/
|   |-- seeders/
|   `-- factories/
|-- routes/
|   `-- api.php
|-- docker/
|   `-- entrypoint.sh
|-- Dockerfile
|-- .env.docker.example
`-- README.md
```

## Current Backend Progress

| Domain | Current backend state |
| --- | --- |
| Auth | Login, logout, request-access registration, forgot/reset password, Passport token flow. |
| Profile | `/api/v2/me` read/update profile endpoints. |
| Roles and permissions | `roles` and `role_permissions` seeders; module permission middleware. |
| User management | Admin user CRUD/status/accept/reject endpoints. |
| Access requests | Admin approve/reject request-access workflow. |
| Projects / Infrastructure Plans | Project model, seeder, policy, controller, and admin routes are present. |
| Contract Management | Contracts CRUD/archive, document upload/download/status/archive, summary/options endpoints, audit logging, tests. |
| Project Accomplishments | Accomplishment CRUD/archive/validation, document upload/download, project progress sync, summary/options endpoints, tests. |
| Engineering Plans | Model, request validation, file service, and upload/store endpoint exist. Full listing/review API still pending. |
| Cashflows | Tables/models exist. Controller/API work pending. |
| Variation Orders | Tables/models exist. Controller/API work pending. |
| Reports | Tables/models foundation exists through source modules. Report controller/API pending. |
| Audit Logs | Audit table/model/service exists. Dedicated read-only audit module API pending. |
| Notifications | Tables/models exist. Notification generation/API pending. |
| Contractor Performance | Table/model exists. Controller/API pending. |

## API Routes

Main route file:

```text
backend/routes/api.php
```

Full current and planned API documentation is maintained in [../API.md](../API.md).

Authentication:

```text
POST   /api/v2/login
POST   /api/v2/logout
POST   /api/v2/register
POST   /api/v2/password-forgot
POST   /api/v2/password-reset
GET    /api/v2/me
PATCH  /api/v2/me
```

Contract Management:

```text
GET    /api/v2/contract-management/summary
GET    /api/v2/contract-management/options
GET    /api/v2/contracts
POST   /api/v2/contracts
GET    /api/v2/contracts/{contract}
PATCH  /api/v2/contracts/{contract}
DELETE /api/v2/contracts/{contract}
POST   /api/v2/contracts/{contract}/documents
GET    /api/v2/contract-documents/{document}/download
PATCH  /api/v2/contract-documents/{document}/status
DELETE /api/v2/contract-documents/{document}
```

Project Accomplishments:

```text
GET    /api/v2/project-accomplishments/summary
GET    /api/v2/project-accomplishments/options
GET    /api/v2/project-accomplishments
POST   /api/v2/project-accomplishments
GET    /api/v2/project-accomplishments/{accomplishment}
PATCH  /api/v2/project-accomplishments/{accomplishment}
PATCH  /api/v2/project-accomplishments/{accomplishment}/validate
DELETE /api/v2/project-accomplishments/{accomplishment}
POST   /api/v2/project-accomplishments/{accomplishment}/documents
GET    /api/v2/accomplishment-documents/{document}/download
```

Admin:

```text
GET    /api/v2/admin/access-requests
PATCH  /api/v2/admin/access-requests/{accessRequest}/approve
PATCH  /api/v2/admin/access-requests/{accessRequest}/reject
GET    /api/v2/admin/users/stats
GET    /api/v2/admin/users
POST   /api/v2/admin/users
GET    /api/v2/admin/users/{user}
PUT    /api/v2/admin/users/{user}
PATCH  /api/v2/admin/users/{user}/status
DELETE /api/v2/admin/users/{user}
POST   /api/v2/admin/users/{user}/accept
DELETE /api/v2/admin/users/{user}/reject
GET    /api/v2/admin/roles
POST   /api/v2/admin/engineering-plans
GET    /api/v2/admin/projects
POST   /api/v2/admin/projects
PATCH  /api/v2/admin/projects/{project}
DELETE /api/v2/admin/projects/{project}
```

Check routes:

```powershell
docker compose exec backend php artisan route:list --path=api/v2
```

## Database Tables

The schema foundation includes:

```text
roles
role_permissions
users
access_requests
contractors
projects
project_documents
contracts
contract_documents
engineering_plans
cashflow_periods
invoices
invoice_documents
payments
variation_orders
variation_order_documents
contract_time_extensions
work_suspensions
project_accomplishments
accomplishment_documents
contractor_performance_ratings
notifications
notification_recipients
audit_logs
```

Current data-heavy MVP tables:

- `projects`
- `contractors`
- `contracts`
- `contract_documents`
- `project_accomplishments`
- `accomplishment_documents`
- `audit_logs`
- `users`
- `roles`
- `role_permissions`

## Seeders

Seeders are registered in:

```text
backend/database/seeders/DatabaseSeeder.php
```

Current seeders:

```text
RolesSeeder
RolePermissionsSeeder
UsersSeeder
ContractManagementSeeder
ProjectAccomplishmentsSeeder
ProjectsSeeder
```

Run all seeders:

```powershell
docker compose exec backend php artisan db:seed --force
```

Reset and reseed:

```powershell
docker compose exec backend php artisan migrate:fresh --seed --force
```

Seeded admin account:

```text
admin@contrackpro.test
password
```

## Permissions

Module APIs use `EnsureModulePermission`:

```text
backend/app/Http/Middleware/EnsureModulePermission.php
```

Typical route middleware:

```php
->middleware('permission:contracts,view')
->middleware('permission:contracts,create')
->middleware('permission:contract_documents,approve')
```

Supported permission columns:

```text
can_view
can_create
can_edit
can_delete
can_approve
can_export
```

## Audit Logging

Server-side audit logs are written through:

```text
backend/app/Services/AuditLogger.php
```

Use audit logs for create/update/archive/upload/review events where accountability matters. Do not rely on frontend-only logs.

## File Storage

Current private file workflows:

- Contract documents are stored under Laravel local storage.
- Project accomplishment documents are stored under Laravel local storage.
- Engineering plan upload uses `EngineeringPlanFileService`.

Files are not intended to be public assets. Use authenticated download endpoints.

## Docker Commands

Start services:

```powershell
docker compose up -d
```

Run migrations:

```powershell
docker compose exec backend php artisan migrate
```

Clear cache:

```powershell
docker compose exec backend php artisan optimize:clear
```

Regenerate Passport keys:

```powershell
docker compose exec backend php artisan passport:keys --force
```

Run tests:

```powershell
docker compose exec backend php artisan test
```

Run selected tests:

```powershell
docker compose exec backend php artisan test --filter=ContractManagementTest
docker compose exec backend php artisan test --filter=ProjectAccomplishmentTest
```

## Local XAMPP Notes

Docker is preferred. If using XAMPP:

- Use PHP 8.2 or 8.3.
- Enable `sodium`.
- Enable `zip`.
- Configure `.env` for your XAMPP MySQL host and port.
- Do not use `DB_HOST=mysql` outside Docker.

## Backend Development Rules

- Validate all module writes in request/controller layer.
- Use policies/middleware for module access.
- Use soft archive fields where project history matters.
- Keep file uploads private and authenticated.
- Add or update seeders for module MVP data.
- Add feature tests for each new API workflow.
- Keep route names and frontend service methods stable once connected.
