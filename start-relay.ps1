# Quick NFC Relay Launcher
# This script helps you quickly start the NFC relay for different stations

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet('admin', '1', '2', '3', '4', 'reg')]
    [string]$Mode = 'admin',
    
    [Parameter(Mandatory=$false)]
    [string]$RegId = '1'
)

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "NFC Relay Launcher" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Navigate to relay directory
if (-not (Test-Path ".\rfid-relay\relay.js")) {
    Write-Host "ERROR: relay.js not found!" -ForegroundColor Red
    Write-Host "Please run this from the project root directory." -ForegroundColor Yellow
    exit 1
}

Push-Location rfid-relay

# Check dependencies
if (-not (Test-Path ".\node_modules")) {
    Write-Host "Installing dependencies..." -ForegroundColor Yellow
    npm install
    Write-Host ""
}

# Set environment and start relay
switch ($Mode) {
    'admin' {
        Write-Host "Starting relay for ADMIN DESK..." -ForegroundColor Green
        Write-Host "Channel: rfid (general)" -ForegroundColor Cyan
        Write-Host ""
        node relay.js
    }
    '1' {
        Write-Host "Starting relay for STATION 1..." -ForegroundColor Green
        Write-Host "Channel: rfid-station-1" -ForegroundColor Cyan
        Write-Host ""
        $env:STATION_ID = '1'
        node relay.js
    }
    '2' {
        Write-Host "Starting relay for STATION 2..." -ForegroundColor Green
        Write-Host "Channel: rfid-station-2" -ForegroundColor Cyan
        Write-Host ""
        $env:STATION_ID = '2'
        node relay.js
    }
    '3' {
        Write-Host "Starting relay for STATION 3..." -ForegroundColor Green
        Write-Host "Channel: rfid-station-3" -ForegroundColor Cyan
        Write-Host ""
        $env:STATION_ID = '3'
        node relay.js
    }
    '4' {
        Write-Host "Starting relay for STATION 4..." -ForegroundColor Green
        Write-Host "Channel: rfid-station-4" -ForegroundColor Cyan
        Write-Host ""
        $env:STATION_ID = '4'
        node relay.js
    }
    'reg' {
        Write-Host "Starting relay for REGISTRATION DESK $RegId..." -ForegroundColor Green
        Write-Host "Channel: rfid-reg-$RegId" -ForegroundColor Cyan
        Write-Host ""
        $env:REG_ID = $RegId
        node relay.js
    }
}

Pop-Location

Write-Host ""
Write-Host "Relay stopped." -ForegroundColor Yellow
