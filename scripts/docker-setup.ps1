$ErrorActionPreference = "Stop"

$repoRoot = Split-Path -Parent $PSScriptRoot
$backendEnv = Join-Path $repoRoot "backend\.env"
$backendDockerEnv = Join-Path $repoRoot "backend\.env.docker.example"

Set-Location $repoRoot

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    throw "Docker was not found. Install Docker Desktop and start it before running this script."
}

if (-not (Test-Path $backendEnv)) {
    Copy-Item $backendDockerEnv $backendEnv
    Write-Host "Created backend\.env from backend\.env.docker.example"
} else {
    Write-Host "Using existing backend\.env"
}

docker compose up -d --build
docker compose exec -T backend composer install --no-interaction --prefer-dist --optimize-autoloader
docker compose exec -T backend php artisan key:generate --force --no-interaction
docker compose exec -T backend php artisan config:clear
docker compose exec -T backend php artisan migrate:fresh --seed
docker compose exec -T backend php artisan passport:keys --force
docker compose exec -T backend php artisan passport:client --password --name="ConTrackPro Password Client" --no-interaction

Write-Host ""
Write-Host "Docker setup complete."
Write-Host "Backend API: http://localhost:8000"
Write-Host "phpMyAdmin: http://localhost:8081"
Write-Host "Admin login: admin@contrackpro.test / password"
