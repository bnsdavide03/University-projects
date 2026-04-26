@echo off
SETLOCAL ENABLEDELAYEDEXPANSION
TITLE Setup e Avvio Progetto Quiz Online - Windows

REM --- CONTROLLO PRIVILEGI AMMINISTRATORE ---
openfiles > NUL 2>&1
IF ERRORLEVEL 1 (
    echo ERRORE: Eseguire come AMMINISTRATORE. & pause & exit /b 1
)

REM --- DEFINIZIONI PERCORSI RELATIVI ALLO SCRIPT ---
REM %~dp0 e' il percorso della directory dello script corrente (es. C:\Users\Prof\Desktop\QuizOnline_Progetto\)
SET SCRIPT_DIR=%~dp0
REM Rimuove lo slash finale se presente, per consistenza
IF "%SCRIPT_DIR:~-1%"=="\" SET SCRIPT_DIR=%SCRIPT_DIR:~0,-1%

REM Le cartelle del progetto sono allo stesso livello dello script
SET DJANGO_DIR=%SCRIPT_DIR%\data_migration_django
SET SERVLET_DIR_BASE=%SCRIPT_DIR%\servlet_java
SET SERVLET_PROJECT_DIR=%SERVLET_DIR_BASE%\migration_bridge
SET SQL_SCRIPT=%SCRIPT_DIR%\risorse_script_windows\crea_db_utente_pg.sql
SET PYTHON_EXE=python
SET PIP_EXE=pip
SET MVN_EXE=mvn
SET PSQL_EXE=psql
SET WAR_FINAL_NAME=migration-servlet-app.war
SET POSTGRES_PORT=5432

REM Variabili da chiedere/determinare
SET POSTGRES_PASSWORD=
SET CHOSEN_CATALINA_HOME=

cls
echo ======================================================================
echo  SETUP E AVVIO PROGETTO QUIZ ONLINE - WINDOWS 10
echo ======================================================================
echo Questo script verra' eseguito dalla cartella: %SCRIPT_DIR%
echo.
echo Assicurarsi di aver letto "ISTRUZIONI_WINDOWS.txt" e
echo che i prerequisiti (Python, JDK, PostgreSQL Server, Maven, Tomcat)
echo siano installati e configurati.
echo.
echo Le cartelle del progetto "data_migration_django", "servlet_java"
echo e "risorse_script_windows" devono trovarsi
echo NELLA STESSA CARTELLA di questo script.
echo.
IF NOT EXIST "%DJANGO_DIR%\manage.py" (
    echo ERRORE: La cartella del progetto Django "%DJANGO_DIR%" o il file manage.py non sono stati trovati.
    echo Assicurarsi che la cartella "data_migration_django" sia allo stesso livello di questo script.
    goto :errore_fatale
)
IF NOT EXIST "%SERVLET_PROJECT_DIR%\pom.xml" (
    echo ERRORE: La cartella del progetto Servlet "%SERVLET_PROJECT_DIR%" o il file pom.xml non sono stati trovati.
    echo Assicurarsi che le cartelle "servlet_java\migration_bridge" siano allo stesso livello di questo script.
    goto :errore_fatale
)
IF NOT EXIST "%SQL_SCRIPT%" (
    echo ERRORE: Lo script SQL "%SQL_SCRIPT%" non e' stato trovato.
    echo Assicurarsi che la cartella "risorse_script_windows" con "crea_db_utente_pg.sql" sia presente.
    goto :errore_fatale
)
pause
cls

REM --- 0. VERIFICA COMANDI BASE ---
echo Fase 0: Verifica comandi base...

echo Verifica Python...
%PYTHON_EXE% --version || (echo ERRORE: '%PYTHON_EXE%' non trovato. & goto :errore_fatale)
echo Verifica Pip...
%PIP_EXE% --version || (echo ERRORE: '%PIP_EXE%' non trovato. & goto :errore_fatale)
echo Verifica Java...
java -version
echo Verifica PostgreSQL...
%PSQL_EXE% --version || (echo ERRORE: '%PSQL_EXE%' non trovato. & goto :errore_fatale)
echo Verifica Maven...
call "%MVN_EXE%" -version
echo Comandi base trovati.
echo.
pause
cls


REM --- 1. RICHIESTA PASSWORD POSTGRES ---
:chiedi_postgres_pwd
echo Fase 1: Configurazione PostgreSQL
set /p "POSTGRES_PASSWORD=Password utente 'postgres' di PostgreSQL: "
IF "%POSTGRES_PASSWORD%"=="" (
    echo ERRORE: Password vuota.
    goto :chiedi_postgres_pwd
)

set /p "POSTGRES_PORT=Porta PostgreSQL [default: 5432]: "
IF "%POSTGRES_PORT%"=="" SET POSTGRES_PORT=5432

echo Password e porta registrate. & echo. & pause & cls

REM --- 2. CONFIGURAZIONE TOMCAT ---
echo Fase 2: Configurazione Tomcat

IF NOT DEFINED CATALINA_HOME (
    echo ERRORE: La variabile d'ambiente CATALINA_HOME non è definita.
    echo Definire CATALINA_HOME tra le variabili di sistema.
    goto :errore_fatale
)

SET CHOSEN_CATALINA_HOME=%CATALINA_HOME%
SET CHOSEN_CATALINA_HOME=%CHOSEN_CATALINA_HOME:"=%

IF NOT EXIST "%CHOSEN_CATALINA_HOME%\bin\startup.bat" (
    echo ERRORE: "%CHOSEN_CATALINA_HOME%\bin\startup.bat" non trovato.
    echo La variabile CATALINA_HOME non punta a una directory Tomcat valida.
    goto :errore_fatale
)
IF NOT EXIST "%CHOSEN_CATALINA_HOME%\webapps" (
    echo ERRORE: Cartella "%CHOSEN_CATALINA_HOME%\webapps" non trovata.
    echo La variabile CATALINA_HOME non punta a una directory Tomcat valida.
    goto :errore_fatale
)

echo Utilizzo TOMCAT: %CHOSEN_CATALINA_HOME%
echo. & pause & cls


REM --- 3. CONFIGURAZIONE AMBIENTE PYTHON/DJANGO ---
echo Fase 3: Configurazione Ambiente Python e Django
echo Progetto Django in: %DJANGO_DIR%

echo   3.1 Creazione/Verifica ambiente virtuale 'venv'...
cd /D "%DJANGO_DIR%"
IF NOT EXIST "venv" (
    %PYTHON_EXE% -m venv venv || (echo ERRORE creazione venv. & goto :errore_script)
    echo Ambiente virtuale creato.
) ELSE ( echo Ambiente virtuale 'venv' gia' esistente. )

echo   3.2 Installazione dipendenze Python...
call "%DJANGO_DIR%\venv\Scripts\activate.bat"
%PIP_EXE% install -r requirements.txt || (
    echo ERRORE installazione dipendenze Python.
    call "%DJANGO_DIR%\venv\Scripts\deactivate.bat" & goto :errore_script
)
echo Dipendenze Python installate.
call "%DJANGO_DIR%\venv\Scripts\deactivate.bat"
echo. & pause & cls

REM --- 4. CONFIGURAZIONE DATABASE POSTGRESQL ---
echo Fase 4: Configurazione Database PostgreSQL
echo Query eseguita:
set PGPASSWORD=%POSTGRES_PASSWORD%
"%PSQL_EXE%" -U postgres -p %POSTGRES_PORT% -d postgres -a -f "%SQL_SCRIPT%" || (
    echo ERRORE esecuzione script SQL. Controllare output/password/PostgreSQL Server.
    set PGPASSWORD= & goto :errore_script
)
set PGPASSWORD=
echo Database PostgreSQL configurato.
echo. & pause & cls

REM --- 5. MIGRAZIONI DJANGO ---
echo Fase 5: Esecuzione Migrazioni Django
cd /D "%DJANGO_DIR%"
call "%DJANGO_DIR%\venv\Scripts\activate.bat"
echo   5.1 makemigrations importer...
%PYTHON_EXE% manage.py makemigrations importer
echo   5.2 makemigrations (generale)...
%PYTHON_EXE% manage.py makemigrations
echo   5.3 migrate...
%PYTHON_EXE% manage.py migrate || (
    echo ERRORE durante 'migrate'. Controllare output.
    call "%DJANGO_DIR%\venv\Scripts\deactivate.bat" & goto :errore_script
)
call "%DJANGO_DIR%\venv\Scripts\deactivate.bat"
echo Migrazioni Django completate.
echo. & pause & cls

REM --- 6. BUILD E DEPLOY SERVLET ---
echo Fase 6: Build e Deploy Servlet Java
echo Progetto Servlet in: %SERVLET_PROJECT_DIR%

echo   6.1 Compilazione Servlet con Maven...
cd /D "%SERVLET_PROJECT_DIR%"
call %MVN_EXE% clean package -Dmaven.test.skip=true || (
    echo ERRORE compilazione Maven. Controllare output. & goto :errore_script
)
echo Compilazione Maven completata.

SET WAR_SOURCE_PATH=%SERVLET_PROJECT_DIR%\target\%WAR_FINAL_NAME%
IF NOT EXIST "%WAR_SOURCE_PATH%" (
    echo ERRORE: %WAR_FINAL_NAME% non in %SERVLET_PROJECT_DIR%\target. Verificare pom.xml.
    goto :errore_script
)

SET TOMCAT_WEBAPPS_DIR_FINAL=%CATALINA_HOME%\webapps
echo   6.2 Deploy del file .war in %TOMCAT_WEBAPPS_DIR_FINAL% ...
IF EXIST "%TOMCAT_WEBAPPS_DIR_FINAL%\%WAR_FINAL_NAME%" (
    echo Rimozione versione precedente di %WAR_FINAL_NAME%...
    del "%TOMCAT_WEBAPPS_DIR_FINAL%\%WAR_FINAL_NAME%"
)
SET EXPLODED_WAR_DIR_NAME=%WAR_FINAL_NAME:.war=%
IF EXIST "%TOMCAT_WEBAPPS_DIR_FINAL%\%EXPLODED_WAR_DIR_NAME%" (
    echo Rimozione cartella esplosa precedente...
    rmdir /S /Q "%TOMCAT_WEBAPPS_DIR_FINAL%\%EXPLODED_WAR_DIR_NAME%"
)

copy "%WAR_SOURCE_PATH%" "%TOMCAT_WEBAPPS_DIR_FINAL%" || (
    echo ERRORE copia .war in %TOMCAT_WEBAPPS_DIR_FINAL%. Verificare permessi.
    goto :errore_script
)
echo File .war copiato con successo.
echo. & pause & cls

REM --- 7. AVVIO SERVER ---
echo Fase 7: Avvio dei Server
echo Verranno aperte due nuove finestre del Prompt dei Comandi (tomcat e django).
echo.

echo   7.1 Avvio Tomcat...
echo      Tomcat Home (CATALINA_HOME): %CATALINA_HOME%
echo      Java Home (JAVA_HOME): %JAVA_HOME%
call "%CATALINA_HOME%\bin\shutdown.bat"
timeout /t 5 /nobreak >nul

echo      Avviando Tomcat...
start "Tomcat Server (%CATALINA_HOME%)" cmd /K ""%CATALINA_HOME%\bin\startup.bat" run & echo Tomcat sta usando JAVA_HOME=%JAVA_HOME% & echo CATALINA_HOME=%CATALINA_HOME%"
echo      Tomcat avviato in una nuova finestra. Attendere il deploy della servlet.
timeout /t 10 /nobreak >nul

echo   7.2 Avvio Server Django...
cd /D "%DJANGO_DIR%"
start "Django Development Server" cmd /K "call .\venv\Scripts\activate.bat && %PYTHON_EXE% manage.py runserver && call .\venv\Scripts\deactivate.bat"
echo      Server Django avviato in una nuova finestra.

echo.
echo ======================================================================
echo  CONFIGURAZIONE E AVVIO COMPLETATI
echo ======================================================================
echo Riepilogo:
echo - Server Django (http://127.0.0.1:8000/)
echo - Servlet Tomcat (http://localhost:8080/%WAR_FINAL_NAME:.war=%/migrate): copiare nel browser per avviare l'importazione.
echo.
echo Controllare le nuove finestre per eventuali errori.
echo Per fermare i server chiudere le rispettive finestre.
echo.
goto :fine

:errore_script
echo. & echo !!! ERRORE DURANTE L'ESECUZIONE DELLO SCRIPT !!! & goto :fine_con_errore
:errore_fatale
echo. & echo !!! ERRORE FATALE - IMPOSSIBILE CONTINUARE !!! & goto :fine_con_errore
:fine_con_errore
echo Controllare i messaggi e i prerequisiti in "ISTRUZIONI_WINDOWS.md".
echo. & pause & ENDLOCAL & exit /b 1
:fine
echo Script completato. & echo. & pause & ENDLOCAL & exit /b 0