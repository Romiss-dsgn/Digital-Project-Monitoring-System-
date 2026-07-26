# ConTrackPro Deployment Workplan

This plan deploys the current monorepo as two services:

- Backend Laravel API on Railway with MySQL
- Frontend Vue app on Vercel

The recommended branch flow is:

- `staging` deploys to the staging environment.
- `main` deploys to the production environment.
- Feature/fix branches must be merged through pull requests.

## 1. Local Readiness Before Every Push

Run the full local readiness script:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\qa\run-local-readiness.ps1 -Fresh
```

If login says `The selected email is invalid`, the local app database has no seeded users. Restore the local QA dataset:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\qa\restore-local-seed.ps1
```

Use `-Fresh` for a repeatable QA dataset. For a non-destructive check against the current local database:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\qa\run-local-readiness.ps1
```

Start the local frontend after checks pass:

```powershell
npm run dev --prefix frontend
```

Open:

- Frontend: `http://localhost:8080`
- Backend health: `http://localhost:8000/api/health`
- Versioned backend health: `http://localhost:8000/api/v2/health`
- phpMyAdmin: `http://localhost:8081`

Default QA login:

```text
admin@contrackpro.test
password
```

## 2. Push to Staging

Do not push directly to protected `staging`.

```powershell
git status --short
git switch -c final-cleanup-or-deploy-fix
git add -A
git commit -m "prepare deployment workflow"
git push -u origin final-cleanup-or-deploy-fix
```

On GitHub:

1. Open a pull request from your branch to `staging`.
2. Wait for `ConTrackPro CI / Backend Laravel Tests`.
3. Wait for `ConTrackPro CI / Frontend Vue Build`.
4. Get the required approval.
5. Merge to `staging`.

Then sync locally:

```powershell
git switch staging
git pull origin staging
```

## 3. Railway Backend Setup

Create one Railway project with these services:

- `contrackpro-backend`
- Railway MySQL database

Backend service settings:

- Source: GitHub repository or Railway CLI deployment
- Root directory for GitHub deploys: `/backend`
- Public networking: generate a domain
- Health check path: `/api/health`
- Pre-deploy command: `sh scripts/release.sh`

Required backend variables:

```env
APP_NAME=ConTrackPro
APP_ORGANIZATION_NAME="LGU Tuao"
APP_REGION="Municipality of Tuao"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-railway-backend-url
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
CACHE_STORE=file
SEED_DEFAULT_DATA=false
```

Generate `APP_KEY` locally once, then copy it to Railway:

```powershell
docker compose exec -T backend php artisan key:generate --show
```

For a staging/demo Railway environment only, set:

```env
SEED_DEFAULT_DATA=true
```

Do not leave `SEED_DEFAULT_DATA=true` for real production unless the client wants demo QA records.

## 4. Vercel Frontend Setup

Create a Vercel project from the same GitHub repository.

Frontend project settings:

- Root directory: `/frontend`
- Framework preset: Vue
- Build command: `npm run build`
- Output directory: `dist`
- Install command: `npm ci`

Required Vercel environment variables:

```env
VUE_APP_BASE_URL=https://your-vercel-frontend-url
VUE_APP_API_BASE_URL=https://your-railway-backend-url/api/v2
VUE_APP_API_KEY=""
VUE_APP_IS_DEMO=0
```

For staging/preview, point `VUE_APP_API_BASE_URL` to the staging Railway backend.

## 5. GitHub CD Setup

The existing `.github/workflows/ci.yml` now has deploy jobs, but they are gated. They will not run until this repository variable exists:

```text
CD_ENABLED=true
```

Create GitHub environments:

- `staging`
- `production`

Add these environment variables to each GitHub environment:

```text
RAILWAY_PROJECT_ID=<Railway project ID>
RAILWAY_BACKEND_SERVICE=<Railway backend service name or ID>
RAILWAY_ENVIRONMENT=<Railway environment name or ID>
```

Add these environment secrets to each GitHub environment:

```text
RAILWAY_TOKEN=<Railway project token>
VERCEL_TOKEN=<Vercel token>
VERCEL_ORG_ID=<Vercel org/team ID>
VERCEL_PROJECT_ID=<Vercel frontend project ID>
```

Recommended environment protections:

- `staging`: no manual approval once CI passes.
- `production`: require manual approval before deployment.

After `CD_ENABLED=true`, this becomes the workflow:

1. PR merges into `staging`.
2. CI runs backend tests and frontend build.
3. If CI passes, backend deploys to Railway staging.
4. If CI passes, frontend deploys to Vercel preview/staging.
5. PR merges from `staging` to `main`.
6. CI runs again.
7. Production deployment waits for GitHub environment approval.
8. Backend deploys to Railway production.
9. Frontend deploys to Vercel production.

## 6. Production Database Rules

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
sh backend/scripts/release.sh
```

## 7. Post-Deploy Smoke Test

After every staging or production deploy:

1. Open backend health:
   - `https://your-backend-url/api/health`
2. Open frontend:
   - `https://your-frontend-url`
3. Login as an admin account.
4. Confirm Dashboard loads.
5. Create an Infrastructure project.
6. Create a Contract with `LGU-TUAO-CON-YYYY-NNN` format.
7. Create a Cashflow period, invoice, and payment.
8. Upload an Engineering Plan linked to a project.
9. Add a Project Accomplishment.
10. Export a report and verify the downloaded file.

## 8. Useful References

- Railway Laravel deployment: https://docs.railway.com/guides/laravel
- Railway CLI deploy: https://docs.railway.com/cli/up
- Railway variables: https://docs.railway.com/variables
- Railway deployments and health checks: https://docs.railway.com/deployments
- Vercel CLI deploy: https://vercel.com/docs/cli/deploy
- Vercel GitHub Actions guide: https://vercel.com/kb/guide/how-can-i-use-github-actions-with-vercel
- Vue CLI environment variables: https://cli.vuejs.org/guide/mode-and-env
- GitHub deployment environments: https://docs.github.com/en/actions/concepts/workflows-and-actions/deployment-environments
