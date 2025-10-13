@echo off
echo ====================================
echo   Sistema de Lembretes - DEV MODE
echo ====================================
echo.
echo Iniciando scheduler em modo desenvolvimento...
echo O comando sera executado a cada 5 minutos.
echo Pressione Ctrl+C para parar.
echo.
php artisan schedule:work
