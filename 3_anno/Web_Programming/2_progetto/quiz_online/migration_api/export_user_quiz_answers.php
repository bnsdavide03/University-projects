<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
$pdo = getPDO();
try {
    // (partecipazione, quiz, domanda, risposta) PK composita.
    // Le FK puntano a Partecipazione.codice e Risposta(quiz, domanda, numero).
    $stmt = $pdo->query("SELECT 
                            partecipazione AS partecipazione_original_id, 
                            quiz AS risposta_original_quiz_id,             -- ID quiz della Risposta scelta
                            domanda AS risposta_original_domanda_numero,  -- Numero domanda della Risposta scelta
                            risposta AS risposta_original_numero           -- Numero della Risposta scelta
                         FROM RispostaUtenteQuiz");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);
} catch (PDOException $e) { /* ... error handling ... */ }
?>