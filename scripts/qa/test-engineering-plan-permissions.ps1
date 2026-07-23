param(
    [string]$BaseUrl = "http://127.0.0.1:8000/api/v2",
    [string]$Password = "password"
)

$ErrorActionPreference = "Stop"

$testUsers = @(
    @{ Role = "System Administrator"; Email = "admin@contrackpro.test"; ExpectedStatus = 200 },
    @{ Role = "Engineer - Planning"; Email = "qa.engineer@contrackpro.test"; ExpectedStatus = 200 },
    @{ Role = "Contract Monitoring Personnel"; Email = "qa.monitor@contrackpro.test"; ExpectedStatus = 403 }
)

function Login {
    param([string]$Email)

    $body = @{ email = $Email; password = $Password } | ConvertTo-Json
    $response = Invoke-RestMethod -Uri "$BaseUrl/login" -Method Post -Body $body -ContentType "application/json"
    return $response.access_token
}

function Get-StatusCode {
    param([string]$Token)

    $headers = @{ Authorization = "Bearer $Token"; Accept = "application/json" }

    try {
        $response = Invoke-WebRequest -Uri "$BaseUrl/admin/engineering-plans" -Method Get -Headers $headers -UseBasicParsing
        return [int]$response.StatusCode
    } catch {
        if ($_.Exception.Response) {
            return [int]$_.Exception.Response.StatusCode
        }

        return -1
    }
}

$failed = $false

foreach ($user in $testUsers) {
    $token = Login -Email $user.Email
    $status = Get-StatusCode -Token $token
    $pass = $status -eq $user.ExpectedStatus

    [pscustomobject]@{
        Role = $user.Role
        Email = $user.Email
        Expected = $user.ExpectedStatus
        Actual = $status
        Result = if ($pass) { "PASS" } else { "FAIL" }
    }

    if (-not $pass) {
        $failed = $true
    }
}

if ($failed) {
    exit 1
}
