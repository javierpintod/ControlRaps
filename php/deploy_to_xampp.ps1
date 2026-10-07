# Script de Despliegue Automático hacia XAMPP
# Copia la aplicación ControlRaps hacia C:\xampp\htdocs\controlraps

$SourceDir = $PSScriptRoot
$TargetDir = "C:\xampp\htdocs\controlraps"

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host " Desplegando ControlRaps en XAMPP Apache Service" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "Origen:  $SourceDir"
Write-Host "Destino: $TargetDir"

if (-not (Test-Path $TargetDir)) {
    New-Item -ItemType Directory -Path $TargetDir -Force | Out-Null
    Write-Host "Directorio destino creado." -ForegroundColor Yellow
}

# Copiar archivos recursivamente
Copy-Item -Path "$SourceDir\*" -Destination $TargetDir -Recurse -Force -Exclude "deploy_to_xampp.ps1"

Write-Host ""
Write-Host "Aplicacion desplegada exitosamente en XAMPP." -ForegroundColor Green
Write-Host "URL Local: http://localhost/controlraps/" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
