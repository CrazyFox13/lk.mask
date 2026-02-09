# PowerShell скрипт для настройки SSH ключа на сервере
# Запустите этот скрипт в PowerShell на Windows

Write-Host "=== Настройка SSH ключа для автоматического деплоя ===" -ForegroundColor Cyan
Write-Host ""

# Параметры сервера
$SERVER_IP = "79.174.12.78"
$SERVER_USER = "mack-user"
$SERVER_PASSWORD = "xR2vY9vE4m"
$DEPLOY_PATH = "/var/www/mack-user/data/www/mack_serv_production/"

# Путь к SSH ключу
$SSH_KEY_PATH = "$env:USERPROFILE\.ssh\github_actions_deploy"

# Генерация SSH ключа, если его нет
if (-not (Test-Path $SSH_KEY_PATH)) {
    Write-Host "Создание нового SSH ключа..." -ForegroundColor Yellow
    ssh-keygen -t ed25519 -C "github-actions-deploy" -f $SSH_KEY_PATH -N '""'
    Write-Host "SSH ключ создан: $SSH_KEY_PATH" -ForegroundColor Green
} else {
    Write-Host "Использование существующего SSH ключа: $SSH_KEY_PATH" -ForegroundColor Yellow
}

# Чтение публичного ключа
$PUBLIC_KEY = Get-Content "$SSH_KEY_PATH.pub" -Raw

Write-Host ""
Write-Host "Копирование публичного ключа на сервер..." -ForegroundColor Yellow

# Использование plink или ssh для копирования ключа
$tempScript = [System.IO.Path]::GetTempFileName()
$scriptContent = @"
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo '$PUBLIC_KEY' >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
"@
$scriptContent | Out-File -FilePath $tempScript -Encoding utf8

# Копирование через SSH с паролем
$pass = $SERVER_PASSWORD | ConvertTo-SecureString -AsPlainText -Force
$cred = New-Object System.Management.Automation.PSCredential($SERVER_USER, $pass)

# Альтернативный способ через ssh
Write-Host "Выполните вручную следующую команду:" -ForegroundColor Yellow
Write-Host "ssh $SERVER_USER@$SERVER_IP 'mkdir -p ~/.ssh && chmod 700 ~/.ssh && echo \"$PUBLIC_KEY\" >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys'" -ForegroundColor White

Write-Host ""
Write-Host "=== Настройка завершена ===" -ForegroundColor Green
Write-Host ""
Write-Host "Приватный ключ находится в: $SSH_KEY_PATH" -ForegroundColor Cyan
Write-Host "Скопируйте содержимое этого файла в GitHub Secrets как SSH_PRIVATE_KEY" -ForegroundColor Yellow
Write-Host ""
Write-Host "Для просмотра ключа выполните:" -ForegroundColor Cyan
Write-Host "Get-Content $SSH_KEY_PATH" -ForegroundColor White
