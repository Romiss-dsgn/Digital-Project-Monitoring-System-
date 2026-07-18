# ConTrackPro Frontend

This folder contains the Vue 3 frontend for ConTrackPro. The UI is based on a Material Dashboard template, but the active implementation is now customized for BFP Region II project and contract monitoring.

## Runtime

- Vue 3
- Vue CLI
- Vue Router
- Vuex
- Axios
- Bootstrap 5
- Material Dashboard components
- Google Material Icons / Material Symbols

## Setup

```powershell
cd frontend
npm install
Copy-Item .env.example .env -Force
npm run serve
```

Frontend URL:

```text
http://localhost:8080
```

Build check:

```powershell
npm run build
```

## Environment

Expected `frontend/.env` for Docker backend:

```env
VUE_APP_BASE_URL=http://localhost:8080/
VUE_APP_API_BASE_URL=http://127.0.0.1:8000/api/v2
VUE_APP_API_KEY=""
VUE_APP_IS_DEMO=1
```

After editing `.env`, restart `npm run serve`.

## Important Files

```text
frontend/src/
|-- router/index.js
|-- services/
|-- store/
|-- examples/
|   |-- Navbars/
|   `-- Sidenav/
|-- views/
|   |-- examples-api/
|   `-- modules/
`-- assets/
```

## Module Screens

Current module files:

```text
frontend/src/views/Dashboard.vue
frontend/src/views/modules/InfrastructurePlans.vue
frontend/src/views/modules/ContractManagement.vue
frontend/src/views/modules/CashflowManagement.vue
frontend/src/views/modules/EngineeringPlans.vue
frontend/src/views/modules/VariationOrders.vue
frontend/src/views/modules/Accomplishments.vue
frontend/src/views/modules/Reports.vue
frontend/src/views/modules/AuditTrail.vue
frontend/src/views/modules/NotificationsInbox.vue
frontend/src/views/modules/UserManagement.vue
frontend/src/views/modules/Settings.vue
frontend/src/views/modules/ContractorPerformance.vue
```

## Current Frontend Progress

| Module | Current state |
| --- | --- |
| Auth/Login | Connected to backend login. Uses Passport token response. |
| Register/Request Access | Connected to backend request-access registration flow. |
| Profile | Connected to backend `/me` profile endpoints. |
| Layout | ConTrackPro header/sidebar branding applied. Shared ConTrackPro footer is enabled for authenticated module pages. |
| Dashboard | Connected to dashboard summary/export API. |
| Infrastructure Plans | Connected to DB-backed project API. Create/edit/archive, filters, summary cards, regional distribution, recent updates, and aligned page header are active. |
| Contract Management | Connected to DB-backed contract API. Create/update/archive, summary, filters, document workflow. |
| Engineering Plans | Connected to DB-backed engineering plans API for list, stats, pagination, project dropdown, upload, download, review status changes, and archive actions. |
| Project Accomplishments | Connected to DB-backed accomplishments API. Create/update/archive/validate, summary, documents. |
| User Management | Connected to admin users, roles, stats, and access request endpoints. |
| Cashflows | Connected to cashflow periods, invoices, payments, summaries, options, and document endpoints. |
| Variation Orders | Connected to VO list/create/update/submit/review/archive/document endpoints. |
| Reports | Connected to admin report endpoints. |
| Audit Logs | Connected to read-only admin audit log endpoints and export. |
| Notifications | Out of MVP for now; legacy route redirects to Dashboard. |
| Contractor Performance | Out of MVP for now; legacy route redirects to Dashboard. |

## Services

Current API service files:

```text
frontend/src/services/auth.service.js
frontend/src/services/profile.service.js
frontend/src/services/user.service.js
frontend/src/services/project.service.js
frontend/src/services/contract.service.js
frontend/src/services/accomplishment.service.js
frontend/src/services/engineering-plan.service.js
frontend/src/services/dashboard.service.js
frontend/src/services/cashflow.service.js
frontend/src/services/variation-order.service.js
frontend/src/services/report.service.js
frontend/src/services/audit.service.js
frontend/src/services/api-base.js
frontend/src/services/auth-header.js
```

Service rules:

- Put backend calls in `frontend/src/services/`.
- Use `api-base.js` for the API root.
- Use `auth-header.js` for protected requests.
- Do not hard-code `http://localhost:8000` inside components.
- Do not add static module data for connected MVP screens.

## Backend Connection Test

1. Start backend Docker stack from repo root:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\docker-setup.ps1
```

2. Start frontend:

```powershell
cd frontend
npm run serve
```

3. Visit:

```text
http://localhost:8080/login
```

4. Login:

```text
admin@contrackpro.test
password
```

5. Check browser DevTools Network:

```text
POST http://127.0.0.1:8000/api/v2/login
GET  http://127.0.0.1:8000/api/v2/me
GET  http://127.0.0.1:8000/api/v2/admin/dashboard/summary
```

## Common Frontend Issues

Login sends request to `/api/login` or returns 404:

- `frontend/.env` is wrong or the frontend server was not restarted.
- Set `VUE_APP_API_BASE_URL=http://127.0.0.1:8000/api/v2`.
- Stop and rerun `npm run serve`.

Login works in Postman but not frontend:

- Check frontend `.env`.
- Check browser console and Network tab.
- Confirm token is stored after login.
- Confirm backend allows the current route.

Contract save returns 422:

- Use contract number format `BFP-R2-CON-YYYY-NNN`.
- Select an existing project and contractor.
- Enter a valid budget amount.
- If dates are filled, end date must be after or equal to start date.

Build has warnings:

- Bundle-size and Sass deprecation warnings currently come from the inherited dashboard theme.
- They are not blocking unless the build exits with an error.

## Frontend Development Rules

- Keep pages focused on operational workflows, not landing-page content.
- Use existing BFP/ConTrackPro layout patterns.
- Do not add new static mock data once a backend endpoint exists.
- Add loading, empty, and error states for API-backed tables.
- Make all write actions permission-aware when backend permissions are available.
- Keep module tables dense, readable, and easy to scan.
- Prefer one service file per backend domain.

## Recommended Integration Order

1. Keep the QA seed small, repeatable, and file-free.
2. Run API smoke, backend tests, and frontend build before merging to `staging`.
3. Add tests around dashboard summary/export, reports, and VO financial impact.
4. Keep Notifications and Contractor Performance hidden until they are intentionally added to MVP.
5. Continue UI polish on connected table actions, empty states, loading states, and permission states.
