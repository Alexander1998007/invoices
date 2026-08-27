$ErrorActionPreference = 'Stop'

Write-Host 'Checking Node.js...' -ForegroundColor Cyan
node --version
npm --version

Write-Host 'Installing frontend dependencies locally so the IDE can resolve Nuxt/TypeScript types...' -ForegroundColor Cyan
Push-Location "$PSScriptRoot\..\frontend"
npm install
Pop-Location

Write-Host 'Starting Docker services...' -ForegroundColor Cyan
Push-Location "$PSScriptRoot\.."
docker compose up --build
Pop-Location
