<?php
// participation.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user']['nomeUtente'])) {
    echo json_encode(['status' => 'error', 'message' => 'Utente non autenticato. Accesso negato.']);
    http_response_code(401);
    exit();
}

$nomeUtente = $_SESSION['user']['nomeUtente'];
// L'action dovrebbe arrivare sempre via POST con i dati del form quando si usa FormData
$action = isset($_POST['action']) ? $_POST['action'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit') {
    $quizId = isset($_POST['idQuiz']) ? (int) $_POST['idQuiz'] : null;
    $risposteUtenteRaw = isset($_POST['answers']) && is_array($_POST['answers']) ? $_POST['answers'] : [];
    $risposteUtenteProcessed = [];

    foreach ($risposteUtenteRaw as $questionNumber => $selectedAnswersArray) {
        if (is_array($selectedAnswersArray)) {
            foreach ($selectedAnswersArray as $answerNumber) {
                $risposteUtenteProcessed[] = [
                    'domanda_numero' => (int) $questionNumber,
                    'risposta_numero' => (int) $answerNumber
                ];
            }
        } else {
            // Questo caso è meno probabile con checkbox, ma lo teniamo per robustezza
            $risposteUtenteProcessed[] = [
                'domanda_numero' => (int) $questionNumber,
                'risposta_numero' => (int) $selectedAnswersArray
            ];
        }
    }

    $response = ['status' => 'error', 'message' => 'Errore sconosciuto durante l\'invio.']; // Default error

    if (empty($quizId)) {
        $response['message'] = 'ID del quiz mancante.';
        http_response_code(400);
        echo json_encode($response);
        exit();
    }
    // Non è necessario inviare risposte vuote per partecipare, quindi non controlliamo empty($risposteUtenteProcessed) qui
    // a meno che non sia un requisito specifico.

    try {
        $pdo->beginTransaction();

        $quiz_check_sql = "SELECT codice, dataFine FROM Quiz WHERE codice = :quizId";
        $quiz_stmt = $pdo->prepare($quiz_check_sql);
        $quiz_stmt->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $quiz_stmt->execute();
        $quiz_data = $quiz_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$quiz_data) {
            $response['message'] = 'Quiz non trovato (ID: ' . $quizId . ').';
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode($response);
            exit();
        }

        $today = date('Y-m-d');
        if ($quiz_data['dataFine'] < $today) {
            $response['message'] = 'Questo quiz è terminato e non accetta più partecipazioni.';
            $pdo->rollBack();
            http_response_code(403);
            echo json_encode($response);
            exit();
        }

        $participation_check_sql = "SELECT codice FROM Partecipazione WHERE utente = :nomeUtente AND quiz = :quizId";
        $part_check_stmt = $pdo->prepare($participation_check_sql);
        $part_check_stmt->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
        $part_check_stmt->bindParam(':quizId', $quizId, PDO::PARAM_INT);
        $part_check_stmt->execute();
        if ($part_check_stmt->fetch()) {
            $response['message'] = 'Hai già partecipato a questo quiz.';
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode($response);
            exit();
        }

        $sql_partecipazione = "INSERT INTO Partecipazione (utente, quiz, data) VALUES (:utente, :quiz, CURDATE())";
        $stmt_partecipazione = $pdo->prepare($sql_partecipazione);
        $stmt_partecipazione->bindParam(':utente', $nomeUtente, PDO::PARAM_STR);
        $stmt_partecipazione->bindParam(':quiz', $quizId, PDO::PARAM_INT);
        $stmt_partecipazione->execute();
        $partecipazioneId = $pdo->lastInsertId();

        if (!$partecipazioneId) {
            throw new Exception("Impossibile creare la partecipazione nel database.");
        }

        if (!empty($risposteUtenteProcessed)) {
            $sql_risposta_utente = "INSERT INTO RispostaUtenteQuiz (partecipazione, quiz, domanda, risposta)
                                    VALUES (:partecipazione, :quiz, :domanda_numero, :risposta_numero)";
            $stmt_risposta_utente = $pdo->prepare($sql_risposta_utente);

            foreach ($risposteUtenteProcessed as $risposta) {
                $stmt_risposta_utente->bindParam(':partecipazione', $partecipazioneId, PDO::PARAM_INT);
                $stmt_risposta_utente->bindParam(':quiz', $quizId, PDO::PARAM_INT);
                $stmt_risposta_utente->bindParam(':domanda_numero', $risposta['domanda_numero'], PDO::PARAM_INT);
                $stmt_risposta_utente->bindParam(':risposta_numero', $risposta['risposta_numero'], PDO::PARAM_INT);

                if (!$stmt_risposta_utente->execute()) {
                    // Logga l'errore e potrebbe essere utile far fallire la transazione
                    error_log("Errore insert RispostaUtenteQuiz: PID=$partecipazioneId, QID=$quizId, DN=" . $risposta['domanda_numero'] . ", RN=" . $risposta['risposta_numero'] . ". Err: " . implode(":", $stmt_risposta_utente->errorInfo()));
                    // throw new Exception("Errore durante il salvataggio di una risposta."); // Per far fallire la transazione
                }
            }
        }

        $pdo->commit();
        $_SESSION['participationMessage'] = 'Partecipazione registrata con successo!';
        $_SESSION['participationMessageType'] = 'success';

        $response = [
            'status' => 'success',
            'message' => 'Partecipazione registrata con successo!',
            'partecipazione_id' => $partecipazioneId,
            'redirect_url' => 'quiz_results.php?participation_id=' . $partecipazioneId
        ];
        http_response_code(201);

    } catch (PDOException $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        error_log("Errore PDO submit_participation: " . $e->getMessage() . " | Dati: quizId=$quizId, utente=$nomeUtente");
        $response['message'] = 'Errore del database durante la registrazione. Riprova più tardi.';
        http_response_code(500);
    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        error_log("Errore generico submit_participation: " . $e->getMessage() . " | Dati: quizId=$quizId, utente=$nomeUtente");
        $response['message'] = 'Errore del server durante la registrazione. Riprova più tardi.';
        http_response_code(500);
    }
    echo json_encode($response);
    exit();

} else {
    // Metodo non POST o action non 'submit'
    echo json_encode(['status' => 'error', 'message' => 'Richiesta non valida. Metodo POST e action "submit" richiesti.']);
    http_response_code(405); // Method Not Allowed
    exit();
}
?>