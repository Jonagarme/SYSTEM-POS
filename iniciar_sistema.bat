@echo off
setlocal
echo ===================================================
echo   SISTEMA POS PROFESIONAL - INICIANDO SERVIDOR
echo ===================================================
echo.
echo  PHP Version: 7.4 (C:\php74)
echo  Puerto: 8000
echo  URL: http://localhost:8005
echo.
echo  Presiona Ctrl+C para detener el servidor
echo ===================================================
echo.

:: Cambiar al directorio donde reside el archivo .bat
cd /d "%~dp0"

:: Iniciar el servidor embebido de PHP con los drivers necesarios
C:\php74\php.exe -d extension_dir=C:\php74\ext -d extension=pdo_mysql -d extension=openssl -d extension=mbstring -d extension=curl -S localhost:8005

pause
