$ErrorActionPreference = "Stop"

Write-Host "Restoring local app database seed data..."
docker compose exec -T backend php artisan migrate --force
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

docker compose exec -T backend php artisan db:seed --force
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

docker compose exec -T backend php artisan contrackpro:ensure-passport --no-interaction
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

docker compose exec -T backend php artisan optimize:clear
exit $LASTEXITCODE
