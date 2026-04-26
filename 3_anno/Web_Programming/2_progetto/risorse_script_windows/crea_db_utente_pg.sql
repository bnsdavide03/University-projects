-- 1. Crea l'utente quiz_user se non esiste
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT FROM pg_catalog.pg_roles WHERE rolname = 'quiz_user'
    ) THEN
        CREATE ROLE quiz_user WITH LOGIN PASSWORD '!*5633hH$rU7^t%65@J@';
    ELSE
        RAISE NOTICE 'Ruolo quiz_user già esistente.';
    END IF;
END
$$;

-- 2. Crea il database quiz_online se non esiste (fuori dal blocco DO)
SELECT 'CREATE DATABASE quiz_online WITH OWNER = quiz_user ENCODING = ''UTF8'' LC_COLLATE = ''en_US.UTF-8'' LC_CTYPE = ''en_US.UTF-8'' TEMPLATE = template0'
WHERE NOT EXISTS (
    SELECT FROM pg_database WHERE datname = 'quiz_online'
)
\gexec
