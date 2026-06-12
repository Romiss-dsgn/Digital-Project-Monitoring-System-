param(
    [switch] $ResetEnv
)

$ErrorActionPreference = "Stop"

$repoRoot = Split-Path -Parent $PSScriptRoot
$backendEnv = Join-Path $repoRoot "backend\.env"
$backendDockerEnv = Join-Path $repoRoot "backend\.env.docker.example"

Set-Location $repoRoot

Write-Host "ConTrackPro Docker setup"
Write-Host "This will rebuild containers and reset the Docker database with seed data."
Write-Host ""

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    throw "Docker was not found. Install Docker Desktop and start it before running this script."
}

if ($ResetEnv -or -not (Test-Path $backendEnv)) {
    Copy-Item $backendDockerEnv $backendEnv
    Write-Host "Copied backend\.env from backend\.env.docker.example"
} else {
    Write-Host "Using existing backend\.env"
    Write-Host "Use -ResetEnv to replace it with Docker database settings."
}

Write-Host ""
Write-Host "Starting Docker services..."
docker compose up -d --build

Write-Host ""
Write-Host "Installing Composer dependencies..."
docker compose exec -T backend composer install --no-interaction --prefer-dist --optimize-autoloader

Write-Host ""
Write-Host "Preparing Laravel application..."
docker compose exec -T backend php artisan key:generate --force --no-interaction
docker compose exec -T backend php artisan optimize:clear

Write-Host ""
Write-Host "Resetting and seeding Docker database..."
docker compose exec -T backend php artisan migrate:fresh --seed --force

Write-Host ""
Write-Host "Preparing Passport OAuth keys and password client..."
docker compose exec -T backend php artisan passport:keys --force
docker compose exec -T backend php artisan passport:client --password --name="ConTrackPro Password Client" --no-interaction

Write-Host ""
Write-Host "Verifying seed data..."
$verificationScript = 'echo "Users: " . \App\Models\User::count() . PHP_EOL; echo "Roles: " . \App\Models\Role::count() . PHP_EOL; echo "Admin exists: " . (\App\Models\User::where("email", "admin@contrackpro.test")->exists() ? "yes" : "no") . PHP_EOL;'
docker compose exec -T backend php artisan tinker --execute="$verificationScript"

Write-Host ""
Write-Host "Docker setup complete."
Write-Host "Backend API: http://localhost:8000"
Write-Host "phpMyAdmin: http://localhost:8081"
Write-Host "Admin login: admin@contrackpro.test / password"
