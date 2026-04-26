<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
$pdo = getPDO();
try {
    // (quiz, numero) è la PK composita originale.
    $stmt = $pdo->query("SELECT quiz AS quiz_original_id, numero AS numero_originale_domanda, testo FROM Domanda");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);
} catch (PDOException $e) { /* ... error handling ... */ }
?>