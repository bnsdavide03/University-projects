<?php

/**
 * API per la gestione degli utenti.
 * 
 * Questo file gestisce le operazioni relative agli utenti del sistema.
 * Funzionalità principali:
 * - Registrazione di nuovi utenti;
 * - Autenticazione degli utenti;
 * - Modifica dei dati del profilo;
 * - Recupero delle informazioni dell'utente;
 * - Recupero dei quiz creati da un utente;
 * - Recupero delle partecipazioni di un utente;
 */

// --- Inizializzazione della sessione ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php'; // Assicurati che $pdo sia disponibile
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// --- Gestione delle richieste API ---
switch ($method) {

    // --- Creazione utente ---
    case 'POST':
        // Creazione di un nuovo utente.
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['nomeUtente']) || !isset($data['nome']) || !isset($data['cognome']) || !isset($data['eMail'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Dati incompleti']);
            break;
        }

        $nomeUtente = trim($data['nomeUtente']);
        $nome = trim($data['nome']);
        $cognome = trim($data['cognome']);
        $eMail = trim($data['eMail']);

        // Validazione.
        if (empty($nomeUtente) || empty($nome) || empty($cognome) || empty($eMail)) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Tutti i campi sono obbligatori']);
            break;
        }

        if (!filter_var($eMail, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Email non valida']);
            break;
        }

        try {
            // Verifica se il nome utente e/o l'email sono già registrati con una singola query.
            $stmt = $pdo->prepare("
                SELECT 
                    EXISTS(SELECT 1 FROM Utente WHERE nomeUtente = :nomeUtente) as username_exists,
                    EXISTS(SELECT 1 FROM Utente WHERE eMail = :eMail) as email_exists
            ");
            $stmt->bindParam(':nomeUtente', $nomeUtente);
            $stmt->bindParam(':eMail', $eMail);
            $stmt->execute();
            $existence = $stmt->fetch(PDO::FETCH_ASSOC);

            $usernameExists = (bool) $existence['username_exists'];
            $emailExists = (bool) $existence['email_exists'];

            $errorMessages = [];
            if ($usernameExists) {
                $errorMessages[] = 'Nome utente già registrato';
            }
            if ($emailExists) {
                $errorMessages[] = 'Email già registrata';
            }

            if (!empty($errorMessages)) {
                http_response_code(409); // Conflict
                echo json_encode(['status' => 'error', 'message' => implode('. ', $errorMessages) . '.']);
                break;
            }

            // Inserimento del nuovo utente.
            $stmt = $pdo->prepare("INSERT INTO Utente (nomeUtente, nome, cognome, eMail) VALUES (:nomeUtente, :nome, :cognome, :eMail)");
            $stmt->bindParam(':nomeUtente', $nomeUtente);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':cognome', $cognome);
            $stmt->bindParam(':eMail', $eMail);

            if ($stmt->execute()) {
                http_response_code(201); // Created.
                echo json_encode(['status' => 'success', 'message' => 'Utente registrato con successo']);
            } else {
                http_response_code(500); // Internal Server Error
                // Log dell'errore per debug
                error_log('API Errore registrazione DB: Impossibile eseguire INSERT');
                echo json_encode(['status' => 'error', 'message' => 'Errore durante la registrazione']);
            }
        } catch (PDOException $e) {
            http_response_code(500); // Internal Server Error
            // Log dell'errore $e->getMessage() per debug
            error_log('API Errore DB: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Errore del database. Riprova più tardi.']);
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed.
        echo json_encode(['status' => 'error', 'message' => 'Metodo non consentito']);
}
?>