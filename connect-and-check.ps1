# PowerShell скрипт для подключения к серверу и проверки
# Выполните: .\connect-and-check.ps1

$SERVER_IP = "79.174.12.78"
$SERVER_USER = "mack-user"
$SERVER_PASSWORD = "xR2vY9vE4m"
$DEPLOY_PATH = "/var/www/mack-user/data/www/mack_serv_production/"

Write-Host "=== Подключение к серверу и проверка ===" -ForegroundColor Cyan
Write-Host ""

# Функция для выполнения команд на сервере
function Invoke-SSHCommand {
    param(
        [string]$Command
    )
    
    $sshCommand = "ssh -o StrictHostKeyChecking=no ${SERVER_USER}@${SERVER_IP} `"$Command`""
    
    # Используем plink или ssh с паролем
    # Для Windows может потребоваться установка sshpass или использование ключей
    
    Write-Host "Выполнение: $Command" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Для выполнения вручную подключитесь к серверу:" -ForegroundColor Green
    Write-Host "ssh ${SERVER_USER}@${SERVER_IP}" -ForegroundColor White
    Write-Host "Пароль: $SERVER_PASSWORD" -ForegroundColor White
    Write-Host ""
    Write-Host "Затем выполните команды из файла check-server.sh" -ForegroundColor Yellow
}

Write-Host "Инструкции для проверки сервера:" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Скопируйте файлы на сервер:" -ForegroundColor Yellow
Write-Host "   scp check-server.sh setup-production.sh ${SERVER_USER}@${SERVER_IP}:~/" -ForegroundColor White
Write-Host ""
Write-Host "2. Подключитесь к серверу:" -ForegroundColor Yellow
Write-Host "   ssh ${SERVER_USER}@${SERVER_IP}" -ForegroundColor White
Write-Host "   Пароль: $SERVER_PASSWORD" -ForegroundColor White
Write-Host ""
Write-Host "3. Выполните проверку:" -ForegroundColor Yellow
Write-Host "   cd $DEPLOY_PATH" -ForegroundColor White
Write-Host "   bash ~/check-server.sh" -ForegroundColor White
Write-Host ""
Write-Host "4. Выполните настройку:" -ForegroundColor Yellow
Write-Host "   bash ~/setup-production.sh" -ForegroundColor White
Write-Host ""
