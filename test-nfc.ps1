# NFC Quick Test Script
# Run this to verify your NFC setup is working

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "NFC/RFID System Quick Test" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Check if in correct directory
$currentDir = Get-Location
if (-not (Test-Path ".\rfid-relay\server.js")) {
    Write-Host "ERROR: Please run this script from the project root directory!" -ForegroundColor Red
    Write-Host "Current directory: $currentDir" -ForegroundColor Yellow
    Write-Host "Expected to find: .\rfid-relay\server.js" -ForegroundColor Yellow
    exit 1
}

Write-Host "✓ Correct directory" -ForegroundColor Green
Write-Host ""

# Check Node.js
Write-Host "Checking Node.js..." -ForegroundColor Yellow
try {
    $nodeVersion = node --version
    Write-Host "✓ Node.js installed: $nodeVersion" -ForegroundColor Green
} catch {
    Write-Host "✗ Node.js not found! Please install Node.js first." -ForegroundColor Red
    Write-Host "  Download from: https://nodejs.org" -ForegroundColor Yellow
    exit 1
}
Write-Host ""

# Check .env file
Write-Host "Checking .env configuration..." -ForegroundColor Yellow
if (Test-Path ".\.env") {
    $envContent = Get-Content ".\.env" -Raw
    
    $broadcastDriver = $envContent -match 'BROADCAST_DRIVER\s*=\s*pusher'
    $pusherAppId = $envContent -match 'PUSHER_APP_ID'
    $pusherKey = $envContent -match 'PUSHER_APP_KEY'
    $pusherSecret = $envContent -match 'PUSHER_APP_SECRET'
    
    if ($broadcastDriver) {
        Write-Host "✓ BROADCAST_DRIVER=pusher" -ForegroundColor Green
    } else {
        Write-Host "⚠ BROADCAST_DRIVER not set to pusher" -ForegroundColor Yellow
        Write-Host "  Add this line to .env: BROADCAST_DRIVER=pusher" -ForegroundColor Yellow
    }
    
    if ($pusherAppId -and $pusherKey -and $pusherSecret) {
        Write-Host "✓ Pusher credentials configured" -ForegroundColor Green
    } else {
        Write-Host "⚠ Pusher credentials missing in .env" -ForegroundColor Yellow
        Write-Host "  Required variables:" -ForegroundColor Yellow
        Write-Host "    PUSHER_APP_ID" -ForegroundColor Yellow
        Write-Host "    PUSHER_APP_KEY" -ForegroundColor Yellow
        Write-Host "    PUSHER_APP_SECRET" -ForegroundColor Yellow
        Write-Host "    PUSHER_APP_CLUSTER" -ForegroundColor Yellow
    }
} else {
    Write-Host "✗ .env file not found!" -ForegroundColor Red
}
Write-Host ""

# Check relay.js dependencies
Write-Host "Checking relay.js dependencies..." -ForegroundColor Yellow
if (Test-Path ".\rfid-relay\node_modules") {
    Write-Host "✓ Dependencies installed" -ForegroundColor Green
} else {
    Write-Host "⚠ Dependencies not installed" -ForegroundColor Yellow
    Write-Host "  Run: cd rfid-relay; npm install" -ForegroundColor Yellow
    
    $install = Read-Host "Install now? (y/n)"
    if ($install -eq 'y') {
        Write-Host "Installing dependencies..." -ForegroundColor Cyan
        Push-Location rfid-relay
        npm install
        Pop-Location
        Write-Host "✓ Dependencies installed" -ForegroundColor Green
    }
}
Write-Host ""

# Check composer dependencies
Write-Host "Checking Laravel Pusher SDK..." -ForegroundColor Yellow
if (Test-Path ".\vendor\pusher") {
    Write-Host "✓ Pusher PHP SDK installed" -ForegroundColor Green
} else {
    Write-Host "⚠ Pusher PHP SDK not found" -ForegroundColor Yellow
    Write-Host "  Run: composer require pusher/pusher-php-server" -ForegroundColor Yellow
}
Write-Host ""

# List available NFC readers
Write-Host "Checking for NFC readers..." -ForegroundColor Yellow
try {
    $readers = Get-PnpDevice | Where-Object { 
        $_.Class -eq 'SmartCardReader' -or 
        $_.FriendlyName -like '*ACR122*' -or 
        $_.FriendlyName -like '*RFID*' -or
        $_.FriendlyName -like '*NFC*'
    }
    
    if ($readers) {
        Write-Host "✓ Found NFC/RFID reader(s):" -ForegroundColor Green
        foreach ($reader in $readers) {
            Write-Host "  - $($reader.FriendlyName) [$($reader.Status)]" -ForegroundColor Cyan
        }
    } else {
        Write-Host "⚠ No NFC readers detected" -ForegroundColor Yellow
        Write-Host "  Please connect your NFC reader via USB" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠ Could not check for readers" -ForegroundColor Yellow
}
Write-Host ""

# Summary
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Test Summary" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Ensure .env has Pusher credentials" -ForegroundColor White
Write-Host "2. Install dependencies if needed:" -ForegroundColor White
Write-Host "   cd rfid-relay && npm install" -ForegroundColor Gray
Write-Host ""
Write-Host "3. Test server.js:" -ForegroundColor White
Write-Host "   For admin desk:" -ForegroundColor Gray
Write-Host "   cd rfid-relay && node server.js" -ForegroundColor Gray
Write-Host ""
Write-Host "   For station 1:" -ForegroundColor Gray
Write-Host "   `$env:STATION_ID='1'; cd rfid-relay; node server.js" -ForegroundColor Gray
Write-Host ""
Write-Host "4. Open browser to test:" -ForegroundColor White
Write-Host "   Admin: http://localhost/admin/rfid" -ForegroundColor Gray
Write-Host "   Kiosk: http://localhost/admin/kiosk/1" -ForegroundColor Gray
Write-Host ""
Write-Host "5. Press F12 in browser and check console for:" -ForegroundColor White
Write-Host "   'Pusher state: connecting → connected'" -ForegroundColor Gray
Write-Host ""
Write-Host "6. Tap an NFC card and watch the console" -ForegroundColor White
Write-Host ""
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "For full setup guide, see:" -ForegroundColor Yellow
Write-Host "NFC_SETUP_GUIDE.md" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
