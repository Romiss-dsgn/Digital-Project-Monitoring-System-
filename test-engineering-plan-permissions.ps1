# test-engineering-plan-permissions.ps1
# Tests RBAC enforcement on the Engineering Plans module across all roles.
# Compatible with Windows PowerShell 5.1 (no ??, no -SkipHttpErrorCheck).

$baseUrl = "http://127.0.0.1:8000/api/v2"

# TODO: replace CHANGE_ME with the real seeded password (check UsersSeeder.php)
$seededPassword = "password"

$testUsers = @(
    @{ Role = "System Administrator";          Email = "admin@contrackpro.test";       Password = $seededPassword },
    @{ Role = "Engineer - Planning";           Email = "mark.villanueva@bfp.gov.ph";   Password = $seededPassword },
    @{ Role = "Engineer - Supervision";        Email = "grace.lim@bfp.gov.ph";         Password = $seededPassword },
    @{ Role = "Engineer - Monitoring";         Email = "paolo.garcia@bfp.gov.ph";      Password = $seededPassword },
    @{ Role = "Records Management Personnel";  Email = "jose.delacruz@bfp.gov.ph";     Password = $seededPassword }
)

$testPlanId = 1

$expected = @{
    "System Administrator"          = @{ View = $true;  Create = $true;  Approve = $true;  Delete = $true }
    "Engineer - Planning"           = @{ View = $true;  Create = $true;  Approve = $true;  Delete = $true }
    "Engineer - Supervision"        = @{ View = $true;  Create = $false; Approve = $true;  Delete = $false }
    "Engineer - Monitoring"         = @{ View = $true;  Create = $false; Approve = $false; Delete = $false }
    "Records Management Personnel"  = @{ View = $true;  Create = $false; Approve = $false; Delete = $false }
}

function Login {
    param($email, $password)

    try {
        $body = @{ email = $email; password = $password } | ConvertTo-Json
        $response = Invoke-RestMethod -Uri "$baseUrl/login" -Method Post -Body $body -ContentType "application/json"

        if ($response.access_token) {
            return $response.access_token
        }
        if ($response.token) {
            return $response.token
        }
        if ($response.data.token) {
            return $response.data.token
        }
        return $null
    } catch {
        Write-Host "  LOGIN FAILED for $email : $($_.Exception.Message)" -ForegroundColor Red
        return $null
    }
}

function TestEndpoint {
    param($token, $method, $path, $body = $null)

    $headers = @{ Authorization = "Bearer $token" }

    try {
        if ($body) {
            $jsonBody = $body | ConvertTo-Json
            $response = Invoke-WebRequest -Uri "$baseUrl$path" -Method $method -Headers $headers -Body $jsonBody -ContentType "application/json" -UseBasicParsing
        } else {
            $response = Invoke-WebRequest -Uri "$baseUrl$path" -Method $method -Headers $headers -UseBasicParsing
        }
        return $response.StatusCode
    } catch {
        if ($_.Exception.Response) {
            return [int]$_.Exception.Response.StatusCode
        }
        Write-Host "    (raw error: $($_.Exception.Message))" -ForegroundColor DarkGray
        return -1
    }
}

function CheckResult {
    param($label, $expectedAllowed, $statusCode)

    $actualAllowed = $statusCode -lt 400
    $pass = $actualAllowed -eq $expectedAllowed

    $expectedText = "BLOCK (403)"
    if ($expectedAllowed) {
        $expectedText = "ALLOW"
    }

    $color = "Red"
    $resultText = "FAIL"
    if ($pass) {
        $color = "Green"
        $resultText = "PASS"
    }

    Write-Host ("    [{0}] {1,-10} expected={2,-12} got={3}" -f $resultText, $label, $expectedText, $statusCode) -ForegroundColor $color
}

Write-Host ""
Write-Host "=== Engineering Plans RBAC Test ===" -ForegroundColor Cyan
Write-Host ""

foreach ($user in $testUsers) {
    $role = $user.Role
    Write-Host "Role: $role ($($user.Email))" -ForegroundColor Yellow

    $token = Login -email $user.Email -password $user.Password
    if (-not $token) {
        Write-Host "  Skipping (no token)"
        Write-Host ""
        continue
    }

    $exp = $expected[$role]

    $status = TestEndpoint -token $token -method "GET" -path "/admin/engineering-plans"
    CheckResult -label "View" -expectedAllowed $exp.View -statusCode $status

    $createBody = @{ plan_title = "RBAC Test"; project_id = 1; plan_type = "Architectural"; status = "for_review" }
    $status = TestEndpoint -token $token -method "POST" -path "/admin/engineering-plans" -body $createBody
    CheckResult -label "Create" -expectedAllowed $exp.Create -statusCode $status

    $approveBody = @{ status = "approved" }
    $status = TestEndpoint -token $token -method "PATCH" -path "/admin/engineering-plans/$testPlanId/status" -body $approveBody
    CheckResult -label "Approve" -expectedAllowed $exp.Approve -statusCode $status

    $status = TestEndpoint -token $token -method "DELETE" -path "/admin/engineering-plans/$testPlanId"
    CheckResult -label "Delete" -expectedAllowed $exp.Delete -statusCode $status

    Write-Host ""
}

Write-Host "=== Done ===" -ForegroundColor Cyan
Write-Host ""