param(
    [string]$BaseUrl = "http://127.0.0.1:8000/api/v2",
    [string]$Email = "admin@contrackpro.test",
    [string]$Password = "password"
)

$ErrorActionPreference = "Stop"

function Invoke-ApiLogin {
    $body = @{ email = $Email; password = $Password } | ConvertTo-Json
    return Invoke-RestMethod -Uri "$BaseUrl/login" -Method Post -ContentType "application/json" -Body $body
}

function Test-Endpoint {
    param(
        [hashtable]$Headers,
        [string]$Name,
        [string]$Path
    )

    try {
        $response = Invoke-WebRequest -Uri "$BaseUrl$Path" -Method Get -Headers $Headers -UseBasicParsing
        [pscustomobject]@{ Name = $Name; Status = [int]$response.StatusCode; Result = "OK" }
    } catch {
        $status = "ERR"
        if ($_.Exception.Response) {
            $status = [int]$_.Exception.Response.StatusCode
        }
        [pscustomobject]@{ Name = $Name; Status = $status; Result = "FAIL" }
    }
}

$login = Invoke-ApiLogin
$headers = @{ Authorization = "Bearer $($login.access_token)"; Accept = "application/json" }

$checks = @(
    @{ Name = "me"; Path = "/me" },
    @{ Name = "dashboard summary"; Path = "/admin/dashboard/summary" },
    @{ Name = "projects"; Path = "/admin/projects" },
    @{ Name = "project options"; Path = "/admin/projects/options" },
    @{ Name = "engineering plans"; Path = "/admin/engineering-plans" },
    @{ Name = "contract summary"; Path = "/contract-management/summary" },
    @{ Name = "contracts"; Path = "/contracts" },
    @{ Name = "contract options"; Path = "/contract-management/options" },
    @{ Name = "cashflow periods"; Path = "/cashflow-periods" },
    @{ Name = "variation orders"; Path = "/variation-orders" },
    @{ Name = "accomplishments"; Path = "/project-accomplishments" },
    @{ Name = "reports project status"; Path = "/admin/reports/project-status" },
    @{ Name = "audit logs"; Path = "/admin/audit-logs" }
)

$results = foreach ($check in $checks) {
    Test-Endpoint -Headers $headers -Name $check.Name -Path $check.Path
}

$results | Format-Table -AutoSize

if ($results.Result -contains "FAIL") {
    exit 1
}
