param(
    [string]$Database = "contrackpro_testing"
)

$ErrorActionPreference = "Stop"

Write-Host "Creating test database '$Database' when missing..."
docker compose exec -T mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS $Database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON $Database.* TO 'contrackpro'@'%'; FLUSH PRIVILEGES;"

Write-Host "Migrating isolated test database..."
docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=$Database `
    backend php artisan migrate:fresh --force
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

Write-Host "Running backend tests against '$Database'..."
docker compose exec -T `
    -e APP_ENV=testing `
    -e DB_DATABASE=$Database `
    backend php artisan test
exit $LASTEXITCODE
