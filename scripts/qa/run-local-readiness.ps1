param(
    [switch]$Fresh,
    [switch]$SkipFrontendBuild
)

$ErrorActionPreference = "Stop"

Write-Host "Starting Docker services..."
docker compose up -d --build
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

if ($Fresh) {
    Write-Host "Refreshing local database with repeatable QA seed data..."
    docker compose exec -T backend php artisan migrate:fresh --seed --force
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
} else {
    Write-Host "Running pending migrations and idempotent seeders..."
    docker compose exec -T backend php artisan migrate --force
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
    docker compose exec -T backend php artisan db:seed --force
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}

Write-Host "Ensuring Passport keys and personal access client exist..."
docker compose exec -T backend php artisan contrackpro:ensure-passport --no-interaction
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}
docker compose exec -T backend php artisan optimize:clear
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

Write-Host "Running backend test suite..."
docker compose exec -T mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS contrackpro_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON contrackpro_testing.* TO 'contrackpro'@'%'; FLUSH PRIVILEGES;"
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}
docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=contrackpro_testing `
    backend php artisan migrate:fresh --force
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=contrackpro_testing `
    backend php artisan test
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

Write-Host "Restoring local app seed data for browser testing..."
powershell -ExecutionPolicy Bypass -File .\scripts\qa\restore-local-seed.ps1
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

if (-not $SkipFrontendBuild) {
    Write-Host "Building frontend..."
    npm run build --prefix frontend
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}

Write-Host "Local readiness checks completed."
