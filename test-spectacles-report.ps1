param(
  [string]$BaseUrl = "http://localhost:8000",
  [string]$UserLogin = "ryan",
  [string]$UserPass  = "secret123",
  [string]$AdminLogin = "admin",
  [string]$AdminPass  = "admin123",
  [int]$WaitSeconds = 15  # pour l'expiration
)

# -------- Helpers ----------
function Call-Http {
  param(
    [ValidateSet('GET','POST')] [string]$Method,
    [string]$Path,
    [Microsoft.PowerShell.Commands.WebRequestSession]$Session,
    $Body = $null,
    [string]$ContentType = $null
  )
  $uri = "$BaseUrl$Path"
  try {
    $params = @{ Uri = $uri; Method = $Method; ErrorAction = 'Stop' }
    if ($Session) { $params.WebSession = $Session } else { $params.SessionVariable = 'outSess' }
    if ($Body) { $params.Body = $Body }
    if ($ContentType) { $params.ContentType = $ContentType }

    $resp = Invoke-WebRequest @params
    if (-not $Session) { $Session = $outSess }

    return @{ ok = $true; status = [int]$resp.StatusCode; content = $resp.Content; session = $Session }
  }
  catch {
    $resp = $_.Exception.Response
    $status = if ($resp) { [int]$resp.StatusCode.value__ } else { 0 }
    $text = if ($resp) {
      $reader = New-Object System.IO.StreamReader($resp.GetResponseStream())
      $reader.ReadToEnd()
    } else { $_.Exception.Message }
    return @{ ok = $false; status = $status; content = $text; session = $Session }
  }
}

function Add-TestResult {
  param([string]$Step, [hashtable]$Res, [int]$Expected)
  $ok = $Res.status -eq $Expected
  if ($ok) {
    Write-Host ("[$Step] ✅ OK  (HTTP {0})" -f $Res.status) -ForegroundColor Green
  } else {
    Write-Host ("[$Step] ❌ FAIL (expected {0}, got {1})" -f $Expected,$Res.status) -ForegroundColor Red
  }
  return [PSCustomObject]@{
    Step     = $Step
    Status   = $Res.status
    Expected = $Expected
    Success  = $ok
    Time     = (Get-Date)
  }
}

# -------- Début scénario --------
Write-Host "=== Test Spectacles @ $BaseUrl ===" -ForegroundColor Cyan

$userSess  = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$adminSess = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$results = @()

# A) Public
$r = Call-Http GET "/shows" $null
$results += Add-TestResult "Public: /shows" $r 200

$r = Call-Http GET "/shows/1" $null
$results += Add-TestResult "Public: /shows/1" $r 200

# B) Protégé sans login
$r = Call-Http GET "/profile" $userSess
$results += Add-TestResult "Protégé sans login: /profile" $r 401

# C) Login USER
$r = Call-Http POST "/login" $userSess "username=$UserLogin&password=$UserPass" "application/x-www-form-urlencoded"
$statusOK = ($r.status -ge 200 -and $r.status -le 303)
$results += [PSCustomObject]@{
  Step = "Login USER"; Status = $r.status; Expected = "200-303"; Success = $statusOK; Time = (Get-Date)
}
if (-not $statusOK) { Write-Host "⚠️ Login user failed"; exit 1 }

# D) Réservation
$r = Call-Http POST "/reserve/1" $userSess $null
$results += Add-TestResult "Réserver /reserve/1" $r 201

# E) Profil
$r = Call-Http GET "/profile" $userSess
$results += Add-TestResult "Profil /profile" $r 200

# F) Expiration access token
Write-Host "⏳ Attente $WaitSeconds s pour forcer l'expiration..." -ForegroundColor Yellow
Start-Sleep -Seconds $WaitSeconds

$r = Call-Http GET "/profile" $userSess
$results += Add-TestResult "Après expiration: /profile" $r 401

# G) Refresh
$r = Call-Http POST "/refresh" $userSess $null
$results += Add-TestResult "Refresh /refresh" $r 200

$r = Call-Http GET "/profile" $userSess
$results += Add-TestResult "Profil après refresh" $r 200

# H) Admin
$r = Call-Http POST "/login" $adminSess "username=$AdminLogin&password=$AdminPass" "application/x-www-form-urlencoded"
$statusOK = ($r.status -ge 200 -and $r.status -le 303)
$results += [PSCustomObject]@{
  Step = "Login ADMIN"; Status = $r.status; Expected = "200-303"; Success = $statusOK; Time = (Get-Date)
}

# I) Admin add show
$payload = @{ title="Soirée Jazz"; city="Toulouse"; price=22 } | ConvertTo-Json -Compress
$r = Call-Http POST "/admin/shows" $adminSess $payload "application/json"
$results += Add-TestResult "Admin: POST /admin/shows" $r 201

# J) Admin interdit avec compte user
$payload2 = @{ title="Interdit"; city="Paris"; price=10 } | ConvertTo-Json -Compress
$r = Call-Http POST "/admin/shows" $userSess $payload2 "application/json"
$results += Add-TestResult "Admin interdit (USER)" $r 403

# -------- Rapport --------
$allOK = $results | Where-Object { -not $_.Success } | Measure-Object | Select-Object -ExpandProperty Count
if ($allOK -eq 0) {
  Write-Host "`n🎉 Tous les tests sont PASS" -ForegroundColor Green
} else {
  Write-Host "`n⚠️ Certains tests ont échoué" -ForegroundColor Red
}

# Génération des fichiers rapport
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$jsonPath  = "report_test_$timestamp.json"
$csvPath   = "report_test_$timestamp.csv"

$results | ConvertTo-Json -Depth 3 | Out-File -Encoding UTF8 $jsonPath
$results | Export-Csv -NoTypeInformation -Encoding UTF8 $csvPath

Write-Host "📄 Rapports générés :" -ForegroundColor Cyan
Write-Host " - JSON : $jsonPath"
Write-Host " - CSV  : $csvPath"
