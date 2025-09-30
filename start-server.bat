@echo off
title Servidor PHP - ProjPHP

REM Muda para a pasta raiz do projeto (onde está este .bat)
cd /d %~dp0

REM Inicia o servidor PHP em um novo processo e espera ele terminar
start /wait php -S localhost:8000 -t public

REM Quando o servidor for encerrado, a execução continua daqui:
echo.
echo Servidor encerrado. Limpando arquivo public\.env...

REM Limpa o conteúdo do .env que está dentro de public
type NUL > public\.env

echo Arquivo .env limpo com sucesso.
pause