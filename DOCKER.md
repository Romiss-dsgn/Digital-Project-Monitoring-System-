# ConTrackPro Docker Development Setup

This Docker setup is for local development. It containerizes the Laravel backend, MySQL, and phpMyAdmin. The Vue frontend can still run locally with `npm run serve`.

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

## Services

```text
backend     Laravel API container
mysql       MySQL 8 database container
phpmyadmin  browser database viewer
```

## Ports

```text
Backend API: http://localhost:8000
phpMyAdmin: http://localhost:8081
MySQL:       127.0.0.1:3307
```

Inside Docker, Laravel connects to MySQL with:

```env
DB_HOST=mysql
DB_PORT=3306
```

From your host machine, external tools connect with:

```text
Host: 127.0.0.1
Port: 3307
User: contrackpro
Password: contrackpro
Database: contrackpro
```

## First-Time Setup

Run this from the repository root:

```powershell
.\scripts\docker-setup.ps1
```

The setup script:

- copies `backend\.env.docker.example` to `backend\.env` if `.env` is missing
- builds and starts containers
- installs Composer dependencies
- generates the Laravel app key
- clears Laravel config
- runs fresh migrations and seeders
- generates Passport keys
- creates the Passport password client

The script runs `migrate:fresh --seed`, so it resets the Docker database.

## Manual Setup

```powershell
Copy-Item backend\.env.docker.example backend\.env
docker compose up -d --build
docker compose exec backend composer install --no-interaction --prefer-dist --optimize-autoloader
docker compose exec backend php artisan key:generate --force --no-interaction
docker compose exec backend php artisan config:clear
docker compose exec backend php artisan migrate:fresh --seed
docker compose exec backend php artisan passport:keys --force
docker compose exec backend php artisan passport:client --password --name="ConTrackPro Password Client" --no-interaction
```

## Daily Commands

Start containers:

```powershell
docker compose up -d
```

Stop containers:

```powershell
docker compose down
```

View backend logs:

```powershell
docker compose logs backend --tail=100
```

Run Artisan inside Docker:

```powershell
docker compose exec backend php artisan migrate
docker compose exec backend php artisan route:list
```

Run Composer inside Docker:

```powershell
docker compose exec backend composer install
```

## Login Test

Use Postman or Thunder Client:

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

Expected result: an OAuth access token.

## DevOps Notes

- Keep secrets out of Git. Commit `.env.docker.example`, never `backend/.env`.
- Run Laravel commands inside the backend container when using Docker.
- Do not mix local XAMPP Artisan commands with Docker database testing unless you intentionally maintain two environments.
- The MySQL data lives in the named Docker volume `contrackpro_mysql_data`.
- The backend source is bind-mounted into the container for fast development feedback.
- The Docker image defines the runtime dependencies; the compose file defines the local service topology.
- The entrypoint bootstraps fresh clones by installing Composer dependencies and generating an app key if needed.
