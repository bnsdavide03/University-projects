<?php
require_once __DIR__ . '/../config/database.php'; // Va indietro di una cartella per trovare config/

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Per sviluppo. In produzione, specifica il dominio della servlet.

$pdo = getPDO();
try {
    // 'nomeUtente' è la PK originale. Lo inviamo come 'original_id' per coerenza con Django.
    $stmt = $pdo->query("SELECT nomeUtente AS original_id, nome, cognome, eMail FROM Utente");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore database: ' . $e->getMessage()]);
}
?>