# ConTrackPro Docker Guide

This Docker setup is for local backend development. It runs Laravel, MySQL, and phpMyAdmin in containers. The Vue frontend normally runs on the host machine with `npm run serve`.

## Services

```text
backend     Laravel API
mysql       MySQL 8 database
phpmyadmin  browser database viewer
```

## Ports

```text
Backend API: http://localhost:8000/api/v2
Laravel root: http://localhost:8000
phpMyAdmin:  http://localhost:8081
MySQL host:  127.0.0.1:3307
```

Inside Docker, Laravel connects to MySQL with:

```env
DB_HOST=mysql
DB_PORT=3306
```

From your host machine or MySQL Workbench:

```text
Host: 127.0.0.1
Port: 3307
User: contrackpro
Password: contrackpro
Database: contrackpro
```

phpMyAdmin:

```text
Server: mysql
Username: contrackpro
Password: contrackpro
```

## File Layout

```text
.
|-- docker-compose.yml
|-- DOCKER.md
|-- scripts/
|   `-- docker-setup.ps1
|-- backend/
|   |-- Dockerfile
|   |-- .dockerignore
|   |-- .env.docker.example
|   |-- docker/
|   |   `-- entrypoint.sh
|   `-- ...
`-- frontend/
    `-- ...
```

## First-Time Setup

Run from the repository root:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\docker-setup.ps1
```

If you already have a `backend/.env` from XAMPP or a previous local setup:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\docker-setup.ps1 -ResetEnv
```

The script does the following:

1. Copies `backend/.env.docker.example` to `backend/.env` when needed.
2. Builds and starts containers.
3. Installs Composer dependencies in the backend container.
4. Generates the Laravel app key.
5. Clears Laravel caches.
6. Runs `migrate:fresh --seed --force`.
7. Generates Passport keys.
8. Creates the Passport personal access client.
9. Verifies seed data.

Important: the script resets the Docker database.

## Daily Commands

Start:

```powershell
docker compose up -d
```

Stop without deleting data:

```powershell
docker compose down
```

Stop and rebuild backend:

```powershell
docker compose up -d --build backend
```

Force recreate backend after Dockerfile changes:

```powershell
docker compose up -d --build --force-recreate backend
```

Logs:

```powershell
docker compose logs backend --tail=100
docker compose logs mysql --tail=100
```

Container status:

```powershell
docker compose ps
```

## Migration and Seeder Commands

Run pending migrations:

```powershell
docker compose exec backend php artisan migrate
```

Reset and seed:

```powershell
docker compose exec backend php artisan migrate:fresh --seed --force
```

Run all seeders:

```powershell
docker compose exec backend php artisan db:seed --force
```

Run selected seeders:

```powershell
docker compose exec backend php artisan db:seed --class=RolesSeeder --force
docker compose exec backend php artisan db:seed --class=RolePermissionsSeeder --force
docker compose exec backend php artisan db:seed --class=UsersSeeder --force
docker compose exec backend php artisan db:seed --class=ContractManagementSeeder --force
docker compose exec backend php artisan db:seed --class=ProjectAccomplishmentsSeeder --force
docker compose exec backend php artisan db:seed --class=ProjectsSeeder --force
```

Clear Laravel caches:

```powershell
docker compose exec backend php artisan optimize:clear
```

Passport keys:

```powershell
docker compose exec backend php artisan passport:keys --force
docker compose exec backend php artisan passport:client --personal --name="ConTrackPro Personal Access Client" --no-interaction
```

## Database Verification

Show tables:

```powershell
docker compose exec -T mysql mysql -ucontrackpro -pcontrackpro contrackpro -e "SHOW TABLES;"
```

Check seed counts:

```powershell
docker compose exec -T mysql mysql -ucontrackpro -pcontrackpro contrackpro --batch --execute="SELECT 'users' AS item, COUNT(*) AS count FROM users UNION ALL SELECT 'roles', COUNT(*) FROM roles UNION ALL SELECT 'contracts', COUNT(*) FROM contracts UNION ALL SELECT 'projects', COUNT(*) FROM projects UNION ALL SELECT 'accomplishments', COUNT(*) FROM project_accomplishments;"
```

Check admin account:

```powershell
docker compose exec -T mysql mysql -ucontrackpro -pcontrackpro contrackpro --batch --execute="SELECT id, email, name, is_active FROM users WHERE email='admin@contrackpro.test';"
```

## API Smoke Test

Login:

```text
POST http://localhost:8000/api/v2/login
```

Body:

```json
{
  "email": "admin@contrackpro.test",
  "password": "password"
}
```

Use the returned token:

```text
Authorization: Bearer <token>
```

Then test:

```text
GET http://localhost:8000/api/v2/me
GET http://localhost:8000/api/v2/contracts
GET http://localhost:8000/api/v2/project-accomplishments
```

## Frontend Connection

The frontend should run separately:

```powershell
cd frontend
npm run serve
```

Set `frontend/.env`:

```env
VUE_APP_API_BASE_URL=http://127.0.0.1:8000/api/v2
```

Restart the frontend after changing `.env`.

## What `docker compose down` Deletes

This stops and removes containers and the default network:

```powershell
docker compose down
```

It does not delete named volumes, so MySQL data remains.

This deletes database/vendor volumes:

```powershell
docker compose down -v
```

Use `-v` only when you intentionally want a clean Docker database and clean dependency volumes.

## Troubleshooting

### phpMyAdmin opens but no tables show

Select the `contrackpro` database in the left sidebar.

If it is empty, reseed:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\docker-setup.ps1 -ResetEnv
```

Or:

```powershell
docker compose exec backend php artisan migrate:fresh --seed --force
```

### Backend says `Could not open input file: artisan`

The backend container is not running from `/var/www/html` or the bind mount is wrong. Recreate it:

```powershell
docker compose up -d --build --force-recreate backend
docker compose logs backend --tail=100
```

### Backend shows `Invalid key supplied`

Regenerate Passport keys:

```powershell
docker compose exec backend php artisan passport:keys --force
docker compose exec backend php artisan optimize:clear
```

### Frontend gets 404 on login

Check the frontend API URL. It should include `/api/v2`:

```env
VUE_APP_API_BASE_URL=http://127.0.0.1:8000/api/v2
```

Restart:

```powershell
cd frontend
npm run serve
```

### Backend cannot resolve MySQL host

Inside Docker:

```env
DB_HOST=mysql
DB_PORT=3306
```

Outside Docker:

```env
DB_HOST=127.0.0.1
DB_PORT=3307
```

### Composer install is slow or times out

The project is inside OneDrive, so dependency installs can be slower. The Docker setup uses a named vendor volume. If the volume becomes bad:

```powershell
docker compose down
docker volume rm digital-project-monitoring-system-_backend_vendor
docker compose up -d --build
docker compose exec -T backend composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress
```

### Backend works in Postman but frontend still fails

Most likely frontend `.env` is stale.

1. Confirm Postman uses `http://localhost:8000/api/v2/login`.
2. Confirm frontend uses `VUE_APP_API_BASE_URL=http://127.0.0.1:8000/api/v2`.
3. Restart `npm run serve`.
4. Hard refresh browser.

## DevOps Rules

- Commit `.env.docker.example`, never commit `backend/.env`.
- Run Laravel commands inside the backend container when using Docker.
- Keep migrations idempotent and seeders repeatable.
- Use named volumes for MySQL and Composer vendor dependencies.
- Prefer `migrate:fresh --seed` only for local development, not production.
- Document any new service, port, volume, or setup script change in this file.
