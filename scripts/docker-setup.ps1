param(
    [switch] $ResetEnv
)

$ErrorActionPreference = "Stop"

$repoRoot = Split-Path -Parent $PSScriptRoot
$backendEnv = Join-Path $repoRoot "backend\.env"
$backendDockerEnv = Join-Path $repoRoot "backend\.env.docker.example"

Set-Location $repoRoot

function Invoke-Compose {
    param(
        [Parameter(ValueFromRemainingArguments = $true)]
        [string[]] $Arguments
    )

    docker compose @Arguments

    if ($LASTEXITCODE -ne 0) {
        throw "docker compose $($Arguments -join ' ') failed with exit code $LASTEXITCODE"
    }
}

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
Invoke-Compose up -d --build

Write-Host ""
Write-Host "Installing Composer dependencies..."
Invoke-Compose exec -T backend composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress

Write-Host ""
Write-Host "Preparing Laravel application..."
Invoke-Compose exec -T backend php artisan key:generate --force --no-interaction
Invoke-Compose exec -T backend php artisan optimize:clear

Write-Host ""
Write-Host "Resetting and seeding Docker database..."
Invoke-Compose exec -T backend php artisan migrate:fresh --seed --force

Write-Host ""
Write-Host "Preparing Passport OAuth keys and password client..."
Invoke-Compose exec -T backend php artisan passport:keys --force
Invoke-Compose exec -T backend php artisan passport:client --password --name="ConTrackPro Password Client" --no-interaction

Write-Host ""
Write-Host "Verifying seed data..."
$seedCheckQuery = "SELECT 'users' AS item, COUNT(*) AS count FROM users UNION ALL SELECT 'roles', COUNT(*) FROM roles UNION ALL SELECT 'admin_exists', COUNT(*) FROM users WHERE email = 'admin@contrackpro.test';"
Invoke-Compose exec -T mysql mysql -ucontrackpro -pcontrackpro contrackpro --batch --execute="$seedCheckQuery"

Write-Host ""
Write-Host "Docker setup complete."
Write-Host "Backend API: http://localhost:8000"
Write-Host "phpMyAdmin: http://localhost:8081"
Write-Host "Admin login: admin@contrackpro.test / password"
