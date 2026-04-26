<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
$pdo = getPDO();
try {
    // (quiz, domanda, numero) è la PK composita originale.
    $stmt = $pdo->query("SELECT quiz AS quiz_original_id, domanda AS domanda_original_numero, numero AS numero_originale_risposta, testo, tipo, punteggio FROM Risposta");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);
} catch (PDOException $e) { /* ... error handling ... */ }
?>