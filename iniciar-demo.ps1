# Arranca la app y, en otra ventana, el revisor que manda los correos de olvido de fichar.
# Uso: .\iniciar-demo.ps1

Set-Location $PSScriptRoot

Start-Process powershell -ArgumentList '-NoExit', '-Command', "Set-Location '$PSScriptRoot'; Write-Host 'Revisor de correos (cada 5 minutos). No cierres esta ventana.'; php artisan schedule:work"

php artisan serve
