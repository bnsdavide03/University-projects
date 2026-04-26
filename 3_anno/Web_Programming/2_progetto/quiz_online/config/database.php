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

define('DB_HOST', 'localhost'); // O l'IP/hostname del server Oracle dove gira MariaDB
define('DB_USER', 'quiz_user');
define('DB_PASS', '!*5633hH$rU7^t%65@J@');
define('DB_NAME', 'quiz_online');

function getPDO() {
    static $pdo = null; // Connessione statica per riutilizzarla nelle chiamate successive

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            // In un'API reale, logga l'errore e restituisci un errore JSON standard
            error_log("Errore di connessione al database: " . $e->getMessage());
            http_response_code(500); // Internal Server Error
            echo json_encode(['success' => false, 'message' => 'Errore interno del server: Connessione DB fallita.']);
            exit; // Termina lo script
        }
    }
    return $pdo;
}
?>
