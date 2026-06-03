# ConTrackPro - Digital Project Monitoring System

ConTrackPro is a web-based contract and infrastructure project monitoring system. It is intended to help track project records, contract documents, engineering plans, cashflows, variation orders, project accomplishments, contractor performance, notifications, and audit history in one portal.

This repository currently contains a Vue frontend and a Laravel JSON:API backend scaffold. As of this version, the Vue folder has been updated with ConTrackPro screens and routes. The Laravel backend and database schema have not yet been converted to the full ConTrackPro domain model.

## Current Status

| Area | Status |
| --- | --- |
| Vue frontend | Updated with ConTrackPro module pages and navigation routes. |
| Laravel backend | Still the inherited Laravel JSON:API auth/users/profile scaffold. |
| Database | Draft ConTrackPro schema migration has been added; API integration and data workflows are still pending. |
| API integration | Auth/user endpoints exist; ConTrackPro module endpoints are still pending. |
| Documentation | Root docs now describe the actual project scope and current implementation state. |

## Project Structure

```text
.
|-- laravel-json-api/
|   |-- app/
|   |-- database/
|   |-- routes/
|   `-- composer.json
|-- vue-material-dashboard-laravel/
|   |-- public/
|   |-- src/
|   |   |-- router/
|   |   |-- services/
|   |   |-- store/
|   |   `-- views/
|   `-- package.json
|-- CHANGELOG.md
|-- ISSUE_TEMPLATE.md
`-- README.md
```

## Implemented Frontend Modules

The current Vue application includes routes and screens for:

- Dashboard
- Infrastructure Plans Management
- Contract Management
- Cashflow Management
- Engineering Plans Management
- Variation Orders Monitoring
- Project Accomplishments Monitoring
- Reports
- Contractor Performance Rating
- Audit Trail
- Notifications Inbox
- User Management
- Settings
- Login, registration, password reset, profile, and user list screens inherited from the starter project

Main frontend route definitions are in:

```text
vue-material-dashboard-laravel/src/router/index.js
```

ConTrackPro module screens are in:

```text
vue-material-dashboard-laravel/src/views/modules/
```

## Intended System Scope

The planned full system covers the following modules:

- Infrastructure plans and project phase tracking
- Contract records and contractor information management
- Contract document upload, viewing, and download
- Cashflow, invoice, payment, budget, and variance tracking
- Engineering document repository
- Variation order amount, approval, status, and supporting document tracking
- Milestone and project accomplishment monitoring
- Role-based user access for administrative staff, records personnel, contract monitoring personnel, and engineers
- In-system notifications for status changes, approvals, and deadlines
- Non-editable audit trail entries with timestamps and responsible users
- Contractor performance rating based on defined project indicators

These features are partly represented in the Vue UI. Backend persistence, validation, file storage, permissions, reporting logic, and audit trail enforcement still need to be implemented in Laravel and the database.

## Technology Stack

Frontend:

- Vue 3
- Vue Router 4
- Vuex 4
- Bootstrap 5
- Material Dashboard UI components
- Axios
- Vee Validate and Yup
- Chart.js
- SweetAlert2
- Sass

Backend scaffold:

- PHP 8.2 or 8.3 for the current lockfile
- Laravel 11
- Laravel JSON:API
- Laravel Passport
- Laravel Sanctum
- Composer
- PHP sodium extension
- PHP zip extension or a system `7z`/`unzip` command for Composer package downloads
- MySQL or MariaDB

## Frontend Setup

```bash
cd vue-material-dashboard-laravel
npm install
```

Create the frontend environment file:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Typical local values:

```env
VUE_APP_BASE_URL=http://localhost:8080/
VUE_APP_API_BASE_URL=http://localhost:8000/api/v2
VUE_APP_API_KEY=""
VUE_APP_IS_DEMO=1
```

Run the frontend:

```bash
npm run serve
```

Build for production:

```bash
npm run build
```

Run linting:

```bash
npm run lint
```

## Backend Setup

The backend is present but not yet updated for the ConTrackPro modules. Use this setup only when working on auth/users/profile or when starting the Laravel implementation work.

The checked-in `composer.lock` currently expects PHP 8.2 or 8.3. PHP 8.4 will fail on locked packages such as `lcobucci/clock`, `nette/schema`, and `nette/utils` unless the backend dependencies are updated. The Laravel Passport/JWT stack also requires the PHP `sodium` extension to be enabled. Composer also needs the PHP `zip` extension or a system `7z`/`unzip` command to install packages from downloaded archives.

On Windows with XAMPP, make sure Composer uses XAMPP PHP instead of Herd Lite PHP:

```cmd
set PATH=C:\xampp\php;%PATH%
where php
php -v
php --ini
php -m | findstr /i "sodium zip"
```

Expected checks:

- `where php` should list `C:\xampp\php\php.exe` first.
- `php -v` should show PHP 8.2.x or 8.3.x.
- `php --ini` should load `C:\xampp\php\php.ini`.
- The module check should print both `sodium` and `zip`.

```bash
cd laravel-json-api
composer install
cp .env.example .env
php artisan key:generate
```

Configure database values in `.env`, then run:

```bash
php artisan migrate
php artisan passport:install
```

Current API routes are focused on:

- Login
- Logout
- Registration
- Forgot password
- Reset password
- Current user profile
- Users resource

The route file is:

```text
laravel-json-api/routes/api.php
```

The first-pass ConTrackPro schema migration is:

```text
laravel-json-api/database/migrations/2026_06_03_000001_create_contrackpro_schema.php
```

It adds the planned tables for roles, permissions, contractors, projects, contracts, documents, engineering plans, cashflows, invoices, variation orders, accomplishments, contractor ratings, notifications, and audit logs.

## Development Notes

- Treat the Vue module pages as the current active implementation area.
- Do not assume the Laravel backend already exposes ConTrackPro module records through API endpoints.
- Before connecting module screens to real data, create the Laravel models, JSON:API schemas, controllers, policies, seeders, and tests for each module.
- Replace any static frontend data with API-backed services only after the matching backend endpoint exists.
- File upload features will need storage configuration, validation, access rules, and download/view endpoints.
- Audit logs should be generated server-side so users cannot edit or bypass activity history.

## Suggested Next Work

1. Review and finalize the draft ConTrackPro schema migration.
2. Add Laravel models for projects, contracts, contractors, cashflows, documents, variation orders, accomplishments, notifications, audit logs, and ratings.
3. Define roles and permissions for each user type.
4. Implement JSON:API resources and request validation.
5. Connect Vue module screens to backend services.
6. Add file upload, viewing, and download workflows.
7. Add dashboard/report calculations.
8. Add backend and frontend tests for the core workflows.
9. Prepare deployment and user turnover notes.
