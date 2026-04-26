<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
$pdo = getPDO();
try {
    // 'codice' è la PK originale. 'creatore' è la FK (nomeUtente).
    $stmt = $pdo->query("SELECT codice AS original_id, titolo, dataInizio, dataFine, creatore AS creatore_original_id FROM Quiz");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);
} catch (PDOException $e) { /* ... error handling ... */ }
?>