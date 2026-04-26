<?php
/**
 * API per la gestione delle domande dei quiz.
 * 
 * Questo file gestisce tutte le operazioni CRUD relative alle domande dei quiz.
 * Funzionalità implementate:
 * - Creazione di nuove domande per un quiz;
 * - Modifica di domande esistenti;
 * - Eliminazione di domande;
 * - Recupero di tutte le domande associate a un quiz specifico;
 * - Recupero dei dettagli di una singola domanda;
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php'; // Assicurati che il percorso sia corretto
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// Verifica se l'utente è autenticato.
function isAuthenticated()
{
    return isset($_SESSION['user']);
}

// Funzione per verificare se un quiz appartiene all'utente.
// $idQuiz è Quiz.codice, $nomeUtente è Utente.nomeUtente
function isOwnerOfQuiz($pdo, $idQuiz, $nomeUtente)
{
    $stmt = $pdo->prepare("SELECT codice FROM Quiz WHERE codice = :idQuiz AND creatore = :nomeUtente");
    $stmt->bindParam(':idQuiz', $idQuiz, PDO::PARAM_INT);
    $stmt->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

switch ($method) {
    case 'POST':
        if (!isAuthenticated()) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Non autenticato. Effettua il login per continuare.']);
            exit;
        }

        $data = $_POST;

        if (!isset($data['quiz_id']) || empty($data['quiz_id'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'ID del quiz (quiz_id) mancante o non valido.']);
            exit;
        }
        if (!isset($data['questions']) || !is_array($data['questions']) || empty($data['questions'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Nessuna domanda fornita o formato dati non valido.']);
            exit;
        }

        $idQuizCorrente = filter_var($data['quiz_id'], FILTER_VALIDATE_INT);
        if ($idQuizCorrente === false) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'ID del quiz (quiz_id) non è un numero intero valido.']);
            exit;
        }

        $questions_array_from_form = $data['questions'];
        $nomeUtenteLoggato = $_SESSION['user']['nomeUtente'];

        if (!isOwnerOfQuiz($pdo, $idQuizCorrente, $nomeUtenteLoggato)) {
            http_response_code(403); // Forbidden
            echo json_encode(['status' => 'error', 'message' => 'Non sei autorizzato ad aggiungere domande a questo quiz.']);
            exit;
        }

        try {
            $pdo->beginTransaction();
            $numeroDomandaProgressivo = 1; // Per Domanda.numero

            foreach ($questions_array_from_form as $q_key => $question_data) {
                // Validazione dati domanda
                if (!isset($question_data['text']) || trim($question_data['text']) === '') {
                    throw new Exception("Il testo per la domanda " . $numeroDomandaProgressivo . " non può essere vuoto.");
                }
                if (!isset($question_data['answers']) || !is_array($question_data['answers']) || empty($question_data['answers'])) {
                    throw new Exception("La domanda " . $numeroDomandaProgressivo . " ('" . htmlspecialchars(substr(trim($question_data['text']), 0, 30)) . "...') non ha risposte.");
                }

                $testoDomanda = trim($question_data['text']);

                $stmtDomanda = $pdo->prepare("INSERT INTO Domanda (quiz, numero, testo) VALUES (:quiz_fk, :numero_dom, :testo_dom)");
                $stmtDomanda->bindParam(':quiz_fk', $idQuizCorrente, PDO::PARAM_INT);
                $stmtDomanda->bindParam(':numero_dom', $numeroDomandaProgressivo, PDO::PARAM_INT);
                $stmtDomanda->bindParam(':testo_dom', $testoDomanda, PDO::PARAM_STR);

                if (!$stmtDomanda->execute()) {
                    throw new Exception("Errore durante l'inserimento della domanda n." . $numeroDomandaProgressivo . ": " . implode(" ", $stmtDomanda->errorInfo()));
                }

                $numeroRispostaProgressivo = 1; // Per Risposta.numero
                $almenoUnaCorrettaTrovata = false;

                foreach ($question_data['answers'] as $a_key => $answer_data) {
                    if (!isset($answer_data['text']) || trim($answer_data['text']) === '') {
                        throw new Exception("Il testo per la risposta " . $numeroRispostaProgressivo . " della domanda " . $numeroDomandaProgressivo . " non può essere vuoto.");
                    }
                    if (!isset($answer_data['type']) || !in_array($answer_data['type'], ['Corretta', 'Sbagliata'])) {
                        throw new Exception("Tipo non valido per la risposta " . $numeroRispostaProgressivo . " della domanda " . $numeroDomandaProgressivo . ".");
                    }

                    $testoRisposta = trim($answer_data['text']);
                    $tipoRisposta = $answer_data['type'];
                    $punti = 0; // Default

                    if ($tipoRisposta === 'Corretta') {
                        $almenoUnaCorrettaTrovata = true;
                        if (isset($answer_data['points']) && is_numeric($answer_data['points']) && $answer_data['points'] >= 0) {
                            $punti = (int) $answer_data['points'];
                        } else {
                            $punti = 1; // Default per risposta corretta se punti non validi o mancanti
                        }
                    }

                    $stmtRisposta = $pdo->prepare("
                        INSERT INTO Risposta (quiz, domanda, numero, testo, tipo, punteggio) 
                        VALUES (:quiz_fk_risp, :domanda_fk_risp, :numero_risp, :testo_risp, :tipo_risp, :punteggio_risp)
                    ");
                    $stmtRisposta->bindParam(':quiz_fk_risp', $idQuizCorrente, PDO::PARAM_INT);
                    $stmtRisposta->bindParam(':domanda_fk_risp', $numeroDomandaProgressivo, PDO::PARAM_INT); // Questo è Domanda.numero
                    $stmtRisposta->bindParam(':numero_risp', $numeroRispostaProgressivo, PDO::PARAM_INT);
                    $stmtRisposta->bindParam(':testo_risp', $testoRisposta, PDO::PARAM_STR);
                    $stmtRisposta->bindParam(':tipo_risp', $tipoRisposta, PDO::PARAM_STR);
                    $stmtRisposta->bindParam(':punteggio_risp', $punti, PDO::PARAM_INT);

                    if (!$stmtRisposta->execute()) {
                        throw new Exception("Errore durante l'inserimento della risposta n." . $numeroRispostaProgressivo . " per la domanda n." . $numeroDomandaProgressivo . ": " . implode(" ", $stmtRisposta->errorInfo()));
                    }
                    $numeroRispostaProgressivo++;
                }

                if (!$almenoUnaCorrettaTrovata) {
                    throw new Exception("La domanda n." . $numeroDomandaProgressivo . " ('" . htmlspecialchars(substr($testoDomanda, 0, 30)) . "...') deve avere almeno una risposta contrassegnata come 'Corretta'.");
                }
                $numeroDomandaProgressivo++;
            }
            $pdo->commit();

            http_response_code(201); // Created
            echo json_encode(['status' => 'success', 'message' => 'Domande e risposte salvate con successo.']);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(500); // Internal Server Error
            error_log("ERRORE API SALVATAGGIO DOMANDE: " . $e->getMessage() . " | Dati Ricevuti: " . print_r($data, true));
            echo json_encode(['status' => 'error', 'message' => "Errore del server: " . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['status' => 'error', 'message' => 'Metodo non consentito.']);
        exit;
}
?>