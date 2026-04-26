<?php

include "includes/header.php";
require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pdo = getPDO();
// --- Controllo accesso ---
$nomeUtente = $_SESSION['user']['nomeUtente'] ?? null;
if (!isset($nomeUtente)) {
    if (php_sapi_name() == 'cli' || empty($_SESSION['user'])) {
        $_SESSION['user']['nomeUtente'] = 'mario_rossi';
        $nomeUtente = $_SESSION['user']['nomeUtente'];
        // Usiamo le classi alert del tuo CSS
        echo "<div class='container' style='padding-top:10px;'><div class='alert alert-warning'>ATTENZIONE: Sessione utente fittizia per test ('$nomeUtente'). Rimuovere in produzione!</div></div>";
    } else {
        header('Location: auth_login.php');
        exit;
    }
}

// --- Recupero ID Quiz ---
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die("<div class='container' style='padding-top:20px;'><div class='alert alert-danger'>ID Quiz non valido o mancante.</div></div>");
}
$quizId = (int) $_GET['id'];

// --- Fetch Dati Quiz, Domande, Risposte e Statistiche ---
try {
    $sqlQuiz = "SELECT q.codice, q.titolo, q.dataInizio, q.dataFine, u.nome AS creatore_nome, u.cognome AS creatore_cognome 
                FROM Quiz q 
                JOIN Utente u ON q.creatore = u.nomeUtente 
                WHERE q.codice = :quizId AND q.creatore = :nomeUtente";
    $stmtQuiz = $pdo->prepare($sqlQuiz);
    $stmtQuiz->bindParam(':quizId', $quizId, PDO::PARAM_INT);
    $stmtQuiz->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
    $stmtQuiz->execute();
    $quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        die("<div class='container' style='padding-top:20px;'><div class='alert alert-danger'>Quiz non trovato o non sei autorizzato a visualizzarne le statistiche.</div></div>");
    }

    $sqlDomande = "SELECT numero, testo FROM Domanda WHERE quiz = :quizId ORDER BY numero ASC";
    $stmtDomande = $pdo->prepare($sqlDomande);
    $stmtDomande->bindParam(':quizId', $quizId, PDO::PARAM_INT);
    $stmtDomande->execute();
    $domande = $stmtDomande->fetchAll(PDO::FETCH_ASSOC);

    $sqlRisposte = "SELECT domanda, numero, testo, tipo FROM Risposta WHERE quiz = :quizId ORDER BY domanda ASC, numero ASC";
    $stmtRisposte = $pdo->prepare($sqlRisposte);
    $stmtRisposte->bindParam(':quizId', $quizId, PDO::PARAM_INT);
    $stmtRisposte->execute();
    $risposteRaw = $stmtRisposte->fetchAll(PDO::FETCH_ASSOC);

    $risposteOrganizzate = [];
    foreach ($risposteRaw as $risposta) {
        $risposteOrganizzate[$risposta['domanda']][] = $risposta;
    }

    $sqlStatistiche = "SELECT
                           ruq.domanda AS numero_domanda_db,
                           ruq.risposta AS numero_risposta_db,
                           COUNT(ruq.partecipazione) AS conteggio_scelte
                       FROM RispostaUtenteQuiz ruq
                       WHERE ruq.quiz = :quizId
                       GROUP BY ruq.domanda, ruq.risposta";
    $stmtStatistiche = $pdo->prepare($sqlStatistiche);
    $stmtStatistiche->bindParam(':quizId', $quizId, PDO::PARAM_INT);
    $stmtStatistiche->execute();
    $statisticheRaw = $stmtStatistiche->fetchAll(PDO::FETCH_ASSOC);

    $statisticheConteggi = [];
    $totaleRispostePerDomandaDB = [];

    foreach ($statisticheRaw as $stat) {
        $statisticheConteggi[$stat['numero_domanda_db']][$stat['numero_risposta_db']] = (int) $stat['conteggio_scelte'];
        if (!isset($totaleRispostePerDomandaDB[$stat['numero_domanda_db']])) {
            $totaleRispostePerDomandaDB[$stat['numero_domanda_db']] = 0;
        }
        $totaleRispostePerDomandaDB[$stat['numero_domanda_db']] += (int) $stat['conteggio_scelte'];
    }

} catch (PDOException $e) {
    error_log("Errore Database in statistiche_quiz.php: " . $e->getMessage());
    die("<div class='container' style='padding-top:20px;'><div class='alert alert-danger'>Si è verificato un errore nel recupero dei dati. Riprova più tardi.</div></div>");
}

// Calcolo totale partecipanti unici al quiz
$sqlTotalePartecipazioni = "SELECT COUNT(DISTINCT partecipazione) AS totale_partecipanti FROM RispostaUtenteQuiz WHERE quiz = :quizId";
$stmtTotPartecipazioni = $pdo->prepare($sqlTotalePartecipazioni);
$stmtTotPartecipazioni->bindParam(':quizId', $quizId, PDO::PARAM_INT);
$stmtTotPartecipazioni->execute();
$risultatoPartecipazioni = $stmtTotPartecipazioni->fetch(PDO::FETCH_ASSOC);
$totalePartecipantiComplessivi = $risultatoPartecipazioni['totale_partecipanti'] ?? 0;

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale_1.0">
    <title>Statistiche Quiz: <?php echo htmlspecialchars($quiz['titolo']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="main-content">
        <div class="content">
            <h1
                style="text-align:center; margin-bottom: 20px; padding-bottom:10px; border-bottom: 1px solid var(--border-color);">
                Statistiche: <?php echo htmlspecialchars($quiz['titolo']); ?>
            </h1>
            <div class="quiz-detail-card" style="margin-bottom: 25px;">
                <div class="quiz-info-grid">
                    <div class="quiz-info-item">
                        <i class="fas fa-user-edit"></i> <!-- Icona che rappresenta il creatore -->
                        <div>
                            <span class="info-label">Creato da</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($quiz['creatore_nome'] . ' ' . $quiz['creatore_cognome']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="quiz-info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <span class="info-label">Data di inizio</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($quiz['dataInizio'])); ?></span>
                        </div>
                    </div>

                    <div class="quiz-info-item">
                        <i class="fas fa-calendar-check"></i>
                        <div>
                            <span class="info-label">Data di fine</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($quiz['dataFine'])); ?></span>
                        </div>
                    </div>
                </div>
                <!-- Nota: Il pulsante "Partecipa al Quiz" è stato omesso intenzionalmente qui
                     poiché questa è una pagina di statistiche per il creatore del quiz. -->
            </div>

            <?php if (empty($domande)): ?>
                <div class="no-results">
                    <p>
                        Questo quiz non ha ancora domande definite.
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($domande as $indexDomanda => $domanda): ?>
                    <div class="card" style="margin-bottom: 25px;">
                        <div class="card-content">
                            <h2 class="card-title">Domanda <?php echo $indexDomanda + 1; ?></h2>
                            <div class="form-group"
                                style="background-color: var(--light-gray-bg); padding: 12px; border-radius: 4px; border: 1px solid var(--border-color);">
                                <p style="margin-bottom:0;"><?php echo nl2br(htmlspecialchars($domanda['testo'])); ?></p>
                            </div>

                            <?php
                            $totaleSceltePerQuestaDomanda = $totaleRispostePerDomandaDB[$domanda['numero']] ?? 0;
                            ?>
                            <p class="text-muted"
                                style="font-style: italic; margin-bottom: 15px; text-align: right; font-size: 0.9em;">
                                Totale risposte per questa domanda: <strong class="bold"
                                    style="color: var(--text-dark);"><?php echo $totaleSceltePerQuestaDomanda; ?></strong>
                            </p>

                            <?php if (isset($risposteOrganizzate[$domanda['numero']]) && !empty($risposteOrganizzate[$domanda['numero']])): ?>
                                <?php foreach ($risposteOrganizzate[$domanda['numero']] as $risposta): ?>
                                    <?php
                                    $conteggioSceltaCorrente = $statisticheConteggi[$domanda['numero']][$risposta['numero']] ?? 0;
                                    $percentuale = 0;
                                    if ($totaleSceltePerQuestaDomanda > 0) {
                                        $percentuale = round(($conteggioSceltaCorrente / $totaleSceltePerQuestaDomanda) * 100, 1);
                                    }
                                    $tipoRisposta = $risposta['tipo'];
                                    $barColor = 'var(--primary-blue)';
                                    $badgeStyle = '';

                                    if ($tipoRisposta == 'Corretta') {
                                        $barColor = '#27ae60'; // Verde
                                        $badgeStyle = 'background-color: #27ae60; color: white;';
                                        $bg = 'background-color:rgb(229, 255, 240);';
                                    } elseif ($tipoRisposta == 'Sbagliata') {
                                        $barColor = '#e74c3c'; // Rosso
                                        $badgeStyle = 'background-color: #e74c3c; color: white;';
                                        $bg = 'background-color: var(--background-card);';
                                    }
                                    ?>
                                    <div
                                        style="margin-bottom: 15px; padding: 12px; border-radius: 4px; border: 1px solid var(--border-color); <?php echo $bg; ?> border-left: 4px solid <?php echo $barColor; ?>;">
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                            <span
                                                style="flex-grow: 1; padding-right:10px;"><?php echo nl2br(htmlspecialchars($risposta['testo'])); ?></span>
                                            <span class="status-badge"
                                                style="<?php echo $badgeStyle; ?> padding: 4px 10px; font-size:0.75rem; border-radius:10px; border:none;">
                                                <?php echo htmlspecialchars($tipoRisposta); ?>
                                            </span>
                                        </div>
                                        <!-- Barra Percentuale -->
                                        <div
                                            style="background-color: var(--background-medium); border-radius: 15px; height: 20px; width: 100%; overflow: hidden; margin-bottom: 5px;">
                                            <div
                                                style="background-color: <?php echo ($percentuale > 0 ? $barColor : 'transparent'); ?>;
                                                    width: <?php echo $percentuale; ?>%;
                                                    height: 100%; border-radius: 15px;
                                                    text-align: <?php echo ($percentuale > 0 ? 'right' : 'left'); ?>;
                                                    padding-right: <?php echo ($percentuale > 0 ? '8px' : '0'); ?>;
                                                    padding-left: <?php echo ($percentuale == 0 ? '8px' : '0'); ?>;
                                                    line-height: 20px;
                                                    color: <?php echo ($percentuale > 0 ? 'white' : 'var(--text-muted)'); ?>;
                                                    font-size: 0.8em; font-weight: bold;
                                                    transition: width 0.5s ease-in-out; white-space: nowrap; box-sizing: border-box;">
                                                <?php echo $percentuale; ?>%
                                            </div>
                                        </div>
                                        <p class="text-muted" style="font-size: 0.85em; margin-top: 3px; text-align: right;">
                                            Scelta da <strong class="bold"
                                                style="color: var(--text-dark);"><?php echo $conteggioSceltaCorrente; ?></strong>
                                            utent<?php echo ($conteggioSceltaCorrente == 1) ? 'e' : 'i'; ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted"><em>Nessuna opzione di risposta definita per questa domanda.</em></p>
                            <?php endif; ?>

                            <?php if ($totaleSceltePerQuestaDomanda == 0 && isset($risposteOrganizzate[$domanda['numero']]) && !empty($risposteOrganizzate[$domanda['numero']])): ?>
                                <div class="alert alert-info" style="margin-top:15px;">
                                    Nessun utente ha ancora risposto a questa domanda.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php
            if ($totalePartecipantiComplessivi == 0 && !empty($domande)) {
                echo '<div class="no-results"><p>';
                echo 'Nessun utente ha ancora partecipato a questo quiz.</p></div>';
            } elseif ($totalePartecipantiComplessivi > 0) {
                echo '<div class="card" style="text-align:center; margin-top:30px;"><div class="card-content">Numero totale di partecipanti unici al quiz: <strong class="bold">' . $totalePartecipantiComplessivi . '</strong></div></div>';
            }
            ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>