# serve.ps1
# Starts both the Laravel website and SMTP service in a coordinated way

param(
    [switch]$Stop
)

if ($Stop) {
    Write-Host "Stopping services..." -ForegroundColor Yellow
    
    # Stop Laravel website process
    $laravelProcess = Get-Process -Name "php" -ErrorAction SilentlyContinue | Where-Object { $_.CommandLine -like "*artisan serve*" }
    if ($laravelProcess) {
        Stop-Process -Id $laravelProcess.Id -Force
        Write-Host "[OK] Stopped Laravel website (PID: $($laravelProcess.Id))" -ForegroundColor Green
    }
    
    # Stop Docker services
    Set-Location "libver-smtp"
    docker-compose down
    Set-Location ..
    Write-Host "[OK] Stopped Docker SMTP Service" -ForegroundColor Green
    exit 0
}

Write-Host "[START] Starting Libver Services..." -ForegroundColor Cyan
Write-Host ""

# Check prerequisites
Write-Host "Checking prerequisites..." -ForegroundColor Cyan

# Check if MySQL is accessible
$mysqlConnection = New-Object System.Data.SqlClient.SqlConnection
try {
    Write-Host "[WAIT] Waiting for MySQL to be accessible on 127.0.0.1:3306..." -ForegroundColor Yellow
    $attempts = 0
    $maxAttempts = 10
    while ($attempts -lt $maxAttempts) {
        try {
            $tcpClient = New-Object System.Net.Sockets.TcpClient
            $async = $tcpClient.BeginConnect("127.0.0.1", 3306, $null, $null)
            $wait = $async.AsyncWaitHandle.WaitOne(3000)
            if ($wait -and $tcpClient.Connected) {
                $tcpClient.Close()
                Write-Host "[OK] MySQL is accessible" -ForegroundColor Green
                break
            }
            $attempts++
            if ($attempts -lt $maxAttempts) {
                Start-Sleep -Seconds 1
            }
        } catch {
            $attempts++
        }
    }
    if ($attempts -eq $maxAttempts) {
        Write-Host "[WARN] MySQL doesn't seem to be running on 127.0.0.1:3306" -ForegroundColor Yellow
        Write-Host "   Please ensure MySQL service is running. Continuing anyway..." -ForegroundColor Yellow
    }
} catch {
    Write-Host "[WARN] Could not verify MySQL connection. Continuing anyway..." -ForegroundColor Yellow
}

# Check if Docker is available
Write-Host "Checking Docker availability..." -ForegroundColor Yellow
docker --version | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Docker is not installed or not running. Please start Docker Desktop." -ForegroundColor Red
    exit 1
}
Write-Host "[OK] Docker is available" -ForegroundColor Green

Write-Host ""
Write-Host "Starting Libver SMTP Service..." -ForegroundColor Cyan
Set-Location "libver-smtp"
docker-compose up -d --build
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Failed to start SMTP Service." -ForegroundColor Red
    Set-Location ..
    exit $LASTEXITCODE
}
Set-Location ..

# Wait for SMTP service to be ready
Write-Host "[WAIT] Waiting for SMTP Service to be ready..." -ForegroundColor Yellow
$smtpReady = $false
$attempts = 0
while ($attempts -lt 30) {
    try {
        $tcpClient = New-Object System.Net.Sockets.TcpClient
        $async = $tcpClient.BeginConnect("127.0.0.1", 2525, $null, $null)
        $wait = $async.AsyncWaitHandle.WaitOne(2000)
        if ($wait -and $tcpClient.Connected) {
            $tcpClient.Close()
            $smtpReady = $true
            break
        }
        $attempts++
        Start-Sleep -Seconds 1
    } catch {
        $attempts++
        Start-Sleep -Seconds 1
    }
}

if ($smtpReady) {
    Write-Host "[OK] SMTP Service is running on port 2525" -ForegroundColor Green
    Write-Host "[OK] Health check available at http://localhost:3000/health" -ForegroundColor Green
} else {
    Write-Host "[WARN] SMTP Service started but health check timed out. It may still be initializing..." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Starting Laravel Website..." -ForegroundColor Cyan

# Start Laravel in a separate process (non-blocking)
$laravelProcess = Start-Process -FilePath "php" -ArgumentList "artisan", "serve" -PassThru -NoNewWindow
Write-Host "[OK] Laravel Website started (PID: $($laravelProcess.Id))" -ForegroundColor Green
Write-Host "[WEB] Website available at http://127.0.0.1:8000" -ForegroundColor Green

Write-Host ""
Write-Host "====================================" -ForegroundColor Cyan
Write-Host "[OK] All services are running!" -ForegroundColor Green
Write-Host "====================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "To STOP all services, run: .\serve.ps1 -Stop" -ForegroundColor Yellow
Write-Host ""
Write-Host "Services running:" -ForegroundColor Cyan
Write-Host "  [SMTP] Service     : http://127.0.0.1:2525" -ForegroundColor White
Write-Host "  [SMTP] Health API : http://127.0.0.1:3000/health" -ForegroundColor White
Write-Host "  [WEB]  Website    : http://127.0.0.1:8000" -ForegroundColor White
Write-Host "  [DB]   MySQL      : 127.0.0.1:3306 (libver_registration)" -ForegroundColor White
Write-Host ""

# Keep the script running and display status
while ($true) {
    if (-not (Get-Process -Id $laravelProcess.Id -ErrorAction SilentlyContinue)) {
        Write-Host "[WARNING] Laravel process has stopped" -ForegroundColor Yellow
        break
    }
    Start-Sleep -Seconds 60
}
