<?php

/**
 * Configurazione della connessione al database
 * 
 * Questo file gestisce la connessione al database MySQL utilizzato dall'applicazione.
 * Contiene:
 * - Parametri di connessione (host, nome database, username, password)
 * - Funzione per stabilire la connessione tramite PDO
 * - Gestione degli errori di connessione
 * - Configurazione delle opzioni PDO per una gestione ottimale
 * 
 * Utilizzato da tutti i file che necessitano di interagire con il database.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'quiz_user');
define('DB_PASS', '!*5633hH$rU7^t%65@J@');
define('DB_NAME', 'quiz_online');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Per mostrare eccezioni sugli errori
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Risultati come array associativi
            PDO::ATTR_EMULATE_PREPARES => false, // Miglior sicurezza nelle query
        ]
    );
} catch (PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}
