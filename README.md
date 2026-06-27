# ConTrackPro - Digital Project Monitoring System

ConTrackPro is a web-based contract and infrastructure project monitoring system for BFP Region II. The app is intended to help authorized personnel track projects, contracts, engineering records, project accomplishments, financial movements, approvals, documents, and audit history in one internal portal.

This repository contains:

- `frontend/` - Vue 3 dashboard application.
- `backend/` - Laravel JSON:API backend.
- `docker-compose.yml` - local backend, MySQL, and phpMyAdmin stack.
- `scripts/docker-setup.ps1` - repeatable Docker setup script.

## Current Progress

| Area | Current state |
| --- | --- |
| Authentication | Login, logout, registration/request access, password reset, Passport tokens, profile update. |
| Roles and permissions | Role and permission seeders exist. Backend has module permission middleware for protected module APIs. |
| User management | Admin access request approval and user management endpoints exist. Frontend module is active work. |
| Database schema | Core ConTrackPro schema exists in Laravel migrations. Models exist for major domain tables. |
| Docker | Backend, MySQL 8, and phpMyAdmin are containerized for local development. |
| Contract Management | DB-backed MVP with contracts CRUD/archive, document upload/download/review/archive, summary cards, seed data, audit logging, and tests. |
| Project Accomplishments | DB-backed MVP with accomplishments CRUD/archive/validation, document upload/download, project progress sync, summary cards, seed data, and tests. |
| Infrastructure Plans | DB-backed project register foundation is active. This module owns project creation and baseline project data. |
| Engineering Plans | DB-backed MVP for list, summary cards, pagination, project dropdown, and upload. Review/download/archive workflow is still pending. |
| Cashflows | Frontend module exists, but records are still mostly static. Backend tables/models exist. |
| Variation Orders | Frontend module exists, but records are still mostly static. Backend tables/models exist. |
| Reports and Audit Logs | Frontend modules exist. Audit log table/service exists. Full reporting module is still pending. |
| Notifications | Frontend module exists. Backend tables/models exist. Full notification workflow is still pending. |
| Contractor Performance | Frontend module exists. Backend table/model exists. Full API workflow is still pending. |

## Intended Use

ConTrackPro is not a public-facing app. It is designed as an internal project monitoring portal for authorized Bureau of Fire Protection personnel.

Primary users:

- System Administrator
- Regional Commander / management reviewer
- Contract Monitoring personnel
- Engineering personnel
- Finance / cashflow personnel
- Records personnel

Primary workflows:

- Register/request access and wait for admin approval.
- Maintain project and contract records.
- Upload and review supporting contract and engineering documents.
- Track accomplishment milestones and progress.
- Monitor financial changes caused by cashflows and variation orders.
- Generate reports and preserve audit trails for accountability.

## Recommended MVP Module Structure

For the current MVP, keep the system focused and avoid building too many independent modules at once.

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

Implementation rule:

- Keep `Contract Management`, `Project Accomplishments`, and `User Management` as standalone workflows.
- Merge `Infrastructure Plans` and `Engineering Plans` conceptually as `Project Plans`, but keep separate tabs/screens if needed.
- Merge `Cashflows` and `Variation Orders` conceptually as `Financial Management`, because approved variation orders change contract value and cashflow planning.
- Keep `Reports` and `Audit Logs` separate if role-based access differs.

Project module rule:

- `Infrastructure Plans` owns project creation and project baseline data.
- `Engineering Plans` only uploads/reviews technical documents for existing projects.
- `Project Accomplishments` only records milestone progress and evidence for existing projects.
- Do not add separate "Add Project" flows in Engineering Plans or Project Accomplishments.
- Keep the current sidebar names for now; use page descriptions to clarify each module's purpose instead of renaming routes/menus.

See [PLAN.md](PLAN.md) for the detailed sequence of next modules.

## Project Structure

```text
.
|-- backend/
|   |-- app/
|   |-- database/
|   |-- routes/
|   |-- docker/
|   |-- Dockerfile
|   |-- .env.docker.example
|   `-- README.md
|-- frontend/
|   |-- public/
|   |-- src/
|   |   |-- router/
|   |   |-- services/
|   |   |-- store/
|   |   `-- views/
|   `-- README.md
|-- scripts/
|   `-- docker-setup.ps1
|-- docker-compose.yml
|-- API.md
|-- DOCKER.md
|-- PLAN.md
|-- CHANGELOG.md
|-- ISSUE_TEMPLATE.md
`-- README.md
```

## Technology Stack

Frontend:

- Vue 3
- Vue Router 4
- Vuex 4
- Bootstrap 5
- Material Dashboard base components
- Google Material Symbols / Material Icons
- Axios
- Vee Validate and Yup
- Chart.js
- SweetAlert2
- Sass

Backend:

- PHP 8.2 in Docker
- Laravel
- Laravel JSON:API
- Laravel Passport
- MySQL 8
- Composer

Local tooling:

- Docker Desktop
- phpMyAdmin
- Postman or Thunder Client
- Node.js and npm for the Vue app

## Software Architecture

ConTrackPro uses a simple client-server architecture for the MVP:

```text
Vue frontend -> Laravel API -> MySQL database
                  |
                  `-> private local file storage
```

Frontend layer:

- Vue screens live in `frontend/src/views/`.
- API calls should go through `frontend/src/services/`.
- Auth state and shared user context should remain in the frontend store.
- The frontend should not hardcode production data once a backend endpoint exists.
- Shared layout items such as the authenticated header/sidebar/footer should live in shared layout components, not inside each module page.

Backend layer:

- API routes are versioned under `/api/v2`.
- Controllers live under `backend/app/Http/Controllers/Api/V2/`.
- Request validation belongs in request classes or controller validation before records are saved.
- Eloquent models own relationships between users, roles, projects, contractors, contracts, documents, and accomplishments.
- Seeders provide MVP demo data that every developer can reproduce.

Security and accountability:

- Laravel Passport issues bearer tokens.
- Module permission checks are based on `roles` and `role_permissions`.
- Private files are downloaded through authenticated endpoints.
- Important create/update/review/archive actions should be written to `audit_logs`.

Docker architecture for local development:

- `backend` container runs Laravel on port `8000`.
- `mysql` container runs MySQL 8 and stores data in a Docker volume.
- `phpmyadmin` container exposes the database UI on port `8081`.
- Vue still runs outside Docker through `npm run serve` during active frontend development.

## Deployment Plan

The current Docker setup is for local development and team onboarding. Treat it as the foundation for staging, not as a final production deployment.

Recommended MVP deployment sequence:

1. Stabilize the `staging` branch with the DB-backed MVP modules.
2. Provision a staging server with PHP 8.2/8.3, MySQL 8, Composer, Node.js, and HTTPS.
3. Configure backend environment variables with production-style values:

```env
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging-domain.example
DB_HOST=<staging-db-host>
DB_DATABASE=contrackpro
DB_USERNAME=<db-user>
DB_PASSWORD=<db-password>
```

4. Build and deploy the frontend as static assets or serve it through a web server.
5. Point the frontend API base URL to the deployed backend `/api/v2`.
6. Run backend deployment commands:

```powershell
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan passport:keys --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

7. Verify login, role permissions, contract records, project accomplishments, uploads, downloads, and admin approval.

Production hardening before real use:

- Use real secrets, not sample `.env` credentials.
- Keep `APP_DEBUG=false`.
- Enable HTTPS.
- Configure backups for MySQL and uploaded files.
- Set storage permissions correctly.
- Keep uploaded documents outside public web access.
- Add queue/scheduler configuration if notifications, reports, or background jobs are implemented.

## Fast Setup With Docker

Use this for the backend, database, and phpMyAdmin:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\docker-setup.ps1
```

If your `.env` has old XAMPP settings or the database has no tables:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\docker-setup.ps1 -ResetEnv
```

Docker URLs:

```text
Backend API: http://localhost:8000/api/v2
Laravel root: http://localhost:8000
phpMyAdmin:  http://localhost:8081
MySQL host:  127.0.0.1:3307
```

phpMyAdmin credentials:

```text
Server: mysql
Username: contrackpro
Password: contrackpro
Database: contrackpro
```

Admin account:

```text
Email: admin@contrackpro.test
Password: password
```

More Docker details are in [DOCKER.md](DOCKER.md).

## Frontend Setup

Run the Vue app outside Docker:

```powershell
cd frontend
npm install
Copy-Item .env.example .env -Force
npm run serve
```

Expected frontend URL:

```text
http://localhost:8080
```

Expected `frontend/.env` API value:

```env
VUE_APP_API_BASE_URL=http://127.0.0.1:8000/api/v2
```

After changing `.env`, restart `npm run serve`.

## Backend Commands

When using Docker, run backend commands inside the backend container:

```powershell
docker compose exec backend php artisan route:list --path=api/v2
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed --force
docker compose exec backend php artisan optimize:clear
docker compose exec backend php artisan test
```

Run selected module seeders:

```powershell
docker compose exec backend php artisan db:seed --class=ProjectsSeeder --force
docker compose exec backend php artisan db:seed --class=EngineeringPlansSeeder --force
docker compose exec backend php artisan db:seed --class=ContractManagementSeeder --force
docker compose exec backend php artisan db:seed --class=ProjectAccomplishmentsSeeder --force
```

Reset and reseed the Docker database:

```powershell
docker compose exec backend php artisan migrate:fresh --seed --force
docker compose exec backend php artisan passport:keys --force
docker compose exec backend php artisan passport:client --personal --name="ConTrackPro Personal Access Client" --no-interaction
```

## API Smoke Test

Login:

```http
POST http://localhost:8000/api/v2/login
Content-Type: application/json
```

```json
{
  "email": "admin@contrackpro.test",
  "password": "password"
}
```

Use the returned access token as:

```text
Authorization: Bearer <token>
```

Useful checks:

```http
GET http://localhost:8000/api/v2/me
GET http://localhost:8000/api/v2/contracts
GET http://localhost:8000/api/v2/contract-management/summary
GET http://localhost:8000/api/v2/admin/projects
GET http://localhost:8000/api/v2/admin/engineering-plans
GET http://localhost:8000/api/v2/project-accomplishments
GET http://localhost:8000/api/v2/project-accomplishments/summary
```

## Git Branching Rules

Recommended branch flow:

```text
main       production-like stable branch
staging    integration branch for finished module work
feature/*  short-lived module branches
```

Current module branch examples:

```text
contract-management
engineering-plans
user-management
logout
```

Rules:

- Branch from latest `staging`, not from old feature branches.
- Keep one module or fix per branch.
- Before opening a PR, merge latest `staging` into your branch locally and fix conflicts.
- Do not push directly to `main` unless the team agreed.
- Use clear commit messages such as `add contract document upload` or `fix engineering plan merge conflict`.

Common commands:

```powershell
git fetch origin
git switch staging
git pull origin staging
git switch -c feature/my-module
```

Update a feature branch with staging:

```powershell
git fetch origin
git switch feature/my-module
git merge origin/staging
```

After resolving conflicts:

```powershell
git status
git add -A
git commit -m "merge staging into my module"
git push origin feature/my-module
```

Delete a remote branch after merge:

```powershell
git push origin --delete feature/my-module
git fetch --prune
```

## Pull Request Rules

Before creating a PR into `staging`:

1. Run backend tests if backend code changed.
2. Run frontend build if frontend code changed.
3. Confirm Docker setup still works if Docker files changed.
4. Confirm migrations and seeders run.
5. Add screenshots for UI changes.
6. Mention affected routes, tables, and seeders in the PR description.

Useful checks:

```powershell
docker compose exec backend php artisan test
cd frontend
npm run build
```

## Troubleshooting

Frontend calls `/api/login` or returns 404:

- Check `frontend/.env`.
- Use `VUE_APP_API_BASE_URL=http://127.0.0.1:8000/api/v2`.
- Restart `npm run serve`.

phpMyAdmin works but no tables show:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\docker-setup.ps1 -ResetEnv
```

Backend says `Invalid key supplied`:

```powershell
docker compose exec backend php artisan passport:keys --force
docker compose exec backend php artisan optimize:clear
```

Backend cannot connect to MySQL:

- In Docker, `DB_HOST=mysql`.
- From host tools, use `127.0.0.1:3307`.
- Do not use `DB_HOST=mysql` from XAMPP/local PHP.

Composer/PHP version problems outside Docker:

- Use PHP 8.2 or 8.3 for this lockfile.
- Enable `sodium` and `zip`.
- Docker is preferred for backend work.

## Documentation Map

- [API.md](API.md) - current API routes, planned endpoints, request examples, and API testing rules.
- [DOCKER.md](DOCKER.md) - Docker setup and troubleshooting.
- [backend/README.md](backend/README.md) - Laravel backend routes, tables, seeders, and tests.
- [frontend/README.md](frontend/README.md) - Vue frontend setup and module integration rules.
- [PLAN.md](PLAN.md) - recommended module implementation order.
- [CHANGELOG.md](CHANGELOG.md) - notable project changes.
- [ISSUE_TEMPLATE.md](ISSUE_TEMPLATE.md) - issue report format.
