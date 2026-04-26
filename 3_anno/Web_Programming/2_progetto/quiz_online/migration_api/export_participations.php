<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
$pdo = getPDO();
try {
    // 'codice' PK originale. 'utente' (nomeUtente) e 'quiz' (codice) sono FK.
    $stmt = $pdo->query("SELECT codice AS original_id, utente AS utente_original_id, quiz AS quiz_original_id, data FROM Partecipazione");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data]);
} catch (PDOException $e) { /* ... error handling ... */ }
?>