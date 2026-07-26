# LGU Tuao Digital Project Monitoring System Deployment Guide

This is the deployment guide we will follow for the Vue/Laravel/MySQL monorepo.

Recommended hosting:

- Backend Laravel API: Railway
- Database: Railway MySQL
- Frontend Vue app: Vercel
- Automation: GitHub Actions

Default provider URLs are acceptable for MVP/demo deployment. A custom domain can be added later.

## 1. Target Environments

Use two hosted environments:

| Environment | Branch | Purpose | Seed Data |
| --- | --- | --- | --- |
| Local | feature branches | Development and QA | Yes |
| Staging | `staging` | Client testing before release | Optional demo data |
| Production | `main` | Client-ready live system | No demo seed data |

Deployment flow:

```text
feature/fix branch -> PR to staging -> staging deploy -> QA/client test
staging -> PR to main -> production deploy
```

Do not deploy directly to production until the staging URL has been tested.

## 2. Local Readiness Before Any PR

Start from the latest branch:

```powershell
git switch staging
git pull origin staging
git switch -c final-cleanup-or-deploy-fix
```

Run the full local check:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\qa\run-local-readiness.ps1 -Fresh
```

This checks Docker, migrations, seeders, Passport setup, backend tests, and frontend build.

If login fails with `The selected email is invalid`, the local app database has no seeded users. Restore the local QA dataset:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\qa\restore-local-seed.ps1
```

Start the frontend:

```powershell
npm run dev --prefix frontend
```

Local URLs:

- Frontend: `http://localhost:8080`
- Backend health: `http://localhost:8000/api/health`
- Versioned health: `http://localhost:8000/api/v2/health`
- phpMyAdmin: `http://localhost:8081`

Default local QA login:

```text
admin@contrackpro.test
password
```

## 3. Branch Protection

Protect both `staging` and `main`.

Recommended `staging` rule:

- Require pull request before merging
- Require at least 1 approval
- Require status checks before merging
- Required checks:
  - `ConTrackPro CI / Backend Laravel Tests`
  - `ConTrackPro CI / Frontend Vue Build`
- Require conversation resolution before merging
- Do not allow force pushes
- Do not allow deletions

Recommended `main` rule:

- Same as `staging`
- Add production deployment approval through GitHub Environments
- Keep `main` as the client-ready release branch

## 4. GitHub Actions CI

The workflow file is:

```text
.github/workflows/ci.yml
```

It runs on:

- PRs to `staging` or `main`
- Pushes to `staging` or `main`
- Manual workflow dispatch

CI jobs:

- Backend Laravel tests with MySQL
- Frontend Vue production build

Deployment jobs are present but gated by this repository variable:

```text
CD_ENABLED=true
```

Keep `CD_ENABLED` unset or `false` until the Railway and Vercel projects are working manually.

## 5. Backend Staging Deployment on Railway

Create a Railway project for staging first.

Recommended names:

```text
Project: lgu-tuao-dpms-staging
Service: contrackpro-backend
Database: contrackpro-mysql
```

Backend service settings:

- Source: this GitHub repository or Railway CLI deployment
- Root directory if using Railway GitHub deploy: `/backend`
- Dockerfile: `backend/Dockerfile`
- Health check path: `/api/health`
- Pre-deploy command: `sh scripts/release.sh`
- Public networking: enabled

Important: use only one deploy trigger at a time. If GitHub Actions CD is enabled later, avoid also enabling Railway automatic GitHub deploys for the same service.

Required Railway variables for staging:

```env
APP_NAME="LGU Tuao DPMS"
APP_ORGANIZATION_NAME="LGU Tuao"
APP_REGION="Municipality of Tuao"
APP_ENV=production
APP_KEY=base64:GENERATE_AND_SET_THIS
APP_DEBUG=false
APP_URL=https://your-staging-backend-url

LOG_CHANNEL=stderr
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

MAIL_MAILER=log
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
CACHE_DRIVER=file
FILESYSTEM_DISK=local

SEED_DEFAULT_DATA=true
```

Generate the `APP_KEY` locally:

```powershell
docker compose exec -T backend php artisan key:generate --show
```

Copy the generated value into Railway as `APP_KEY`.

After deploying, open:

```text
https://your-staging-backend-url/api/health
https://your-staging-backend-url/api/v2/health
```

Both should return JSON with `status: "ok"`.

## 6. Backend Production Deployment on Railway

Create a separate production Railway project or a separate Railway production environment.

Recommended names:

```text
Project: lgu-tuao-dpms-production
Service: contrackpro-backend
Database: contrackpro-mysql
```

Use the same backend settings as staging, but production variables must differ:

```env
APP_URL=https://your-production-backend-url
APP_DEBUG=false
LOG_LEVEL=warning
SEED_DEFAULT_DATA=false
```

Never run this in production:

```bash
php artisan migrate:fresh --seed --force
```

Production deploys must use:

```bash
php artisan migrate --force
php artisan contrackpro:ensure-passport --no-interaction
```

The release script already does this:

```bash
sh scripts/release.sh
```

## 7. Uploaded Document Storage

The app currently stores uploaded documents through Laravel storage:

- Most module documents use the `local` disk.
- Engineering plan files use the `public` disk.

For short staging demos, container storage is acceptable only if losing uploaded files after redeploy is acceptable.

For real client production, configure persistent storage before accepting real documents:

- Preferred MVP option on Railway: attach a Railway Volume to the backend service.
- Mount path for this Docker image: `/var/www/html/storage`
- This preserves uploaded documents and Passport key files across redeploys.

After adding a volume, redeploy and retest:

1. Upload a contract or engineering document.
2. Download it successfully.
3. Redeploy the backend.
4. Download the same document again.

If the file disappears after redeploy, persistent storage is not configured correctly.

## 8. Frontend Staging Deployment on Vercel

Create a Vercel project from the same GitHub repository.

Recommended staging project:

```text
lgu-tuao-dpms-frontend-staging
```

Frontend settings:

- Root directory: `/frontend`
- Framework preset: Vue
- Install command: `npm ci`
- Build command: `npm run build`
- Output directory: `dist`

Required Vercel staging environment variables:

```env
VUE_APP_BASE_URL=https://your-staging-frontend-url
VUE_APP_API_BASE_URL=https://your-staging-backend-url/api/v2
VUE_APP_API_KEY=""
VUE_APP_IS_DEMO=0
```

Deploy the frontend only after the staging backend health check works.

Then open the staging frontend URL and login using the seeded staging admin account.

## 9. Frontend Production Deployment on Vercel

Create a separate Vercel project for production or configure the production environment of the same project.

Recommended production project:

```text
lgu-tuao-dpms-frontend-production
```

Required production variables:

```env
VUE_APP_BASE_URL=https://your-production-frontend-url
VUE_APP_API_BASE_URL=https://your-production-backend-url/api/v2
VUE_APP_API_KEY=""
VUE_APP_IS_DEMO=0
```

Production frontend must point to the production backend, not the staging backend.

## 10. Enable GitHub CD After Manual Staging Works

Only enable CD after:

1. Railway staging backend works.
2. Vercel staging frontend works.
3. Login works from hosted frontend to hosted backend.
4. File upload/download has been tested.
5. The staging database is correct.

Create GitHub Environments:

```text
staging
production
```

Recommended environment protection:

- `staging`: no manual deployment approval, or approval optional.
- `production`: require manual approval before deploy.

Add these GitHub environment variables to both `staging` and `production`:

```text
RAILWAY_PROJECT_ID=<Railway project ID>
RAILWAY_BACKEND_SERVICE=<Railway backend service name or ID>
RAILWAY_ENVIRONMENT=<Railway environment name or ID>
```

Add these GitHub environment secrets to both `staging` and `production`:

```text
RAILWAY_TOKEN=<Railway project token>
VERCEL_TOKEN=<Vercel token>
VERCEL_ORG_ID=<Vercel org or team ID>
VERCEL_PROJECT_ID=<Vercel project ID for that environment>
```

Then add this repository variable:

```text
CD_ENABLED=true
```

After that, pushes merged into `staging` or `main` will run CI first. If CI passes, deployment jobs will run.

## 11. Normal Release Workflow

For a fix or feature:

```powershell
git switch staging
git pull origin staging
git switch -c fix-short-description
```

Make the change, then run:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\qa\run-local-readiness.ps1 -Fresh
git status --short
git add -A
git commit -m "fix short description"
git push -u origin fix-short-description
```

On GitHub:

1. Open PR from `fix-short-description` to `staging`.
2. Wait for CI checks.
3. Get approval.
4. Merge to `staging`.
5. Confirm staging deployment.
6. Run the staging smoke test.

To release to production:

1. Open PR from `staging` to `main`.
2. Wait for CI checks.
3. Get approval.
4. Merge to `main`.
5. Approve the production deployment environment.
6. Run the production smoke test.

Sync local branches after merge:

```powershell
git switch main
git pull origin main
git switch staging
git pull origin staging
git merge main
git push origin staging
```

## 12. Post-Deploy Smoke Test

Run this after every staging and production deployment.

Backend:

1. Open `/api/health`.
2. Open `/api/v2/health`.
3. Confirm database status is healthy.

Frontend:

1. Open the frontend URL.
2. Login.
3. Confirm Dashboard loads without console errors.
4. Confirm the sidebar shows the correct modules for the user role.

Core module flow:

1. Infrastructure Plans: create a project.
2. Engineering Plans: upload a plan linked to that project.
3. Contract Management: create a contract linked to the project and contractor.
4. Variation Orders: create a draft, submit it, then review/approve it.
5. Cashflow Management: create a period, invoice, and payment.
6. Project Accomplishments: create and validate an accomplishment.
7. Reports/Dashboard: export a report or dashboard summary.
8. Audit Logs: confirm actions were recorded.

Browser checks:

1. Open DevTools Console.
2. Confirm no repeated `500`, `401`, `403`, or CORS errors.
3. Open DevTools Network.
4. Confirm API calls use the hosted backend URL.
5. Refresh the dashboard several times and confirm the role/sidebar does not switch.

File checks:

1. Upload at least one PDF/document.
2. Download it.
3. Redeploy backend.
4. Download it again.

## 13. Troubleshooting

### Login says `The selected email is invalid`

The database has no matching user.

Local fix:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\qa\restore-local-seed.ps1
```

Staging fix if staging should contain demo users:

```bash
php artisan db:seed --force
php artisan contrackpro:ensure-passport --no-interaction
php artisan optimize:clear
```

Production fix:

- Do not seed demo users blindly.
- Create or approve the correct production admin account.
- Confirm the frontend points to the production backend.

### Error says `Personal access client not found`

Run:

```bash
php artisan contrackpro:ensure-passport --no-interaction
php artisan optimize:clear
```

The release script and Docker entrypoint already run this.

### Hosted frontend cannot call backend

Check:

- `VUE_APP_API_BASE_URL` includes `/api/v2`.
- The backend URL is reachable at `/api/health`.
- Browser Network tab is not calling `localhost`.
- Railway backend public networking is enabled.

### Uploaded documents disappear after redeploy

Persistent storage is missing or mounted to the wrong path.

Expected Railway volume mount path:

```text
/var/www/html/storage
```

### Production deploy failed after migration

Do not run `migrate:fresh`.

Use one of these recovery paths:

1. Redeploy the previous Railway deployment.
2. Restore the database backup.
3. Revert the merge commit with a new PR.
4. Run a corrective migration.

## 14. Rollback Plan

Frontend rollback:

- Use Vercel dashboard rollback to a previous deployment.
- Or revert the commit and merge the revert PR.

Backend rollback:

- Use Railway dashboard to redeploy a previous deployment.
- Or revert the commit and merge the revert PR.

Database rollback:

- Restore from backup if a migration damaged production data.
- Prefer corrective migrations for small schema/data fixes.

Before any risky production migration, create a database backup.

## 15. Final Go-Live Checklist

Before giving the system to the client:

- `staging` deployment tested end to end.
- `main` deployment tested end to end.
- Production `APP_DEBUG=false`.
- Production `SEED_DEFAULT_DATA=false`.
- Production frontend points to production backend.
- Production backend points to production database.
- Backend health check passes.
- Admin account exists and can login.
- Role-based modules display correctly.
- File upload/download survives redeploy.
- GitHub branch protection is enabled.
- GitHub Actions CI is passing.
- Production deployment requires approval.
- Database backup process is known.

## 16. References

- Railway Laravel guide: https://docs.railway.com/guides/laravel
- Railway CLI deployments: https://docs.railway.com/cli/deploying
- Railway deployments: https://docs.railway.com/deployments/reference
- Railway volumes: https://docs.railway.com/volumes
- Vercel deployments: https://vercel.com/docs/deployments
- Vercel CLI deploy: https://vercel.com/docs/cli/deploy
- Vercel environment variables: https://vercel.com/docs/environment-variables
- Vercel GitHub Actions guide: https://vercel.com/kb/guide/how-can-i-use-github-actions-with-vercel
- Vue CLI environment variables: https://cli.vuejs.org/guide/mode-and-env
- GitHub Actions deployment environments: https://docs.github.com/en/actions/concepts/workflows-and-actions/deployment-environments
- GitHub branch protection: https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches
