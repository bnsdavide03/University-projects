<?php
/**
 * Pagina di visualizzazione delle partecipazioni dell'utente.
 * Logica di paginazione e recupero dati gestita server-side in questa pagina.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se l'utente non è loggato, reindirizza alla pagina di login.
if (!isset($_SESSION['user']['nomeUtente'])) {
    $_SESSION['error_message'] = "Devi essere loggato per vedere le tue partecipazioni.";
    header('Location: auth_login.php');
    exit;
}

require_once 'config/database.php'; // Assicurati del percorso
$nomeUtente = $_SESSION['user']['nomeUtente'];
$dbError = null;

// --- PARAMETRI PAGINAZIONE ---
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$valid_per_page_options_participations = [5, 10, 20, 50, 100];
$per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;

if ($page < 1)
    $page = 1;
if (!in_array($per_page, $valid_per_page_options_participations))
    $per_page = 10;

$total_participations = 0;
try {
    $count_sql = "SELECT COUNT(*) as total FROM Partecipazione WHERE utente = :nomeUtente";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
    $count_stmt->execute();
    $total_participations = (int) $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    error_log("Errore conteggio partecipazioni per utente $nomeUtente: " . $e->getMessage());
    $dbError = "Si è verificato un errore durante il recupero del numero di partecipazioni.";
}

$total_pages = ($total_participations > 0) ? ceil($total_participations / $per_page) : 1;
if ($page > $total_pages && $total_participations > 0)
    $page = $total_pages; // Se la pagina richiesta è oltre, vai all'ultima
if ($page < 1 && $total_participations > 0)
    $page = 1; // Se la pagina richiesta è prima della prima, vai alla prima
elseif ($total_participations === 0)
    $page = 1; // Se non ci sono risultati, la pagina è 1


$offset = ($page - 1) * $per_page;

$participations_to_display = [];
if (!$dbError) {
    try {
        $sql = "SELECT
                    P.codice AS partecipazione_id, Q.titolo AS quiz_titolo, Q.codice AS quiz_id,
                    DATE_FORMAT(P.data, '%d/%m/%Y') AS data_partecipazione,
                    COALESCE(SUM(R.punteggio), 0) AS punteggio_ottenuto,
                    (SELECT COALESCE(SUM(R_total.punteggio), 0)
                     FROM Domanda D_total
                     JOIN Risposta R_total ON D_total.quiz = R_total.quiz AND D_total.numero = R_total.domanda
                     WHERE D_total.quiz = Q.codice AND R_total.tipo = 'Corretta') AS punteggio_massimo_quiz
                FROM Partecipazione P
                JOIN Quiz Q ON P.quiz = Q.codice
                LEFT JOIN RispostaUtenteQuiz RUQ ON P.codice = RUQ.partecipazione
                LEFT JOIN Risposta R ON RUQ.quiz = R.quiz AND RUQ.domanda = R.domanda AND RUQ.risposta = R.numero AND R.tipo = 'Corretta'
                WHERE P.utente = :nomeUtente
                GROUP BY P.codice, Q.titolo, Q.codice, P.data
                ORDER BY P.data DESC, P.codice DESC
                LIMIT :offset, :per_page";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':per_page', $per_page, PDO::PARAM_INT);
        $stmt->execute();
        $participations_to_display = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Errore recupero partecipazioni per utente $nomeUtente: " . $e->getMessage() . " | Query: " . $sql);
        $dbError = "Si è verificato un errore durante il recupero delle partecipazioni.";
        $participations_to_display = []; // Assicura che sia un array vuoto in caso di errore
    }
}

require_once 'includes/header.php';
?>

<div class="container main-content-area" style="padding-top: 30px; padding-bottom: 50px;">
    <?php // Aggiunta classe e padding ?>

    <div class="page-header-controls">
        <div class="page-title-container">
            <i class="fas fa-tasks page-title-icon"></i>
            <h1 class="page-main-title"> <?php // h2 -> h1 per semantica ?>
                Le Mie Partecipazioni
                <?php if ($total_participations > 0): ?>
                    <span class="total-count-badge">(<?php echo $total_participations; ?>
                        total<?php echo ($total_participations !== 1) ? 'i' : 'e'; ?>)</span>
                <?php endif; ?>
            </h1>
        </div>

        <?php if ($total_participations > 0): ?>
            <div class="per-page-controls-container">
                <form method="GET" id="per-page-form-participations" action="quiz_participations.php" class="per-page-form">
                    <?php
                    foreach ($_GET as $key => $value) {
                        if ($key !== 'per_page' && $key !== 'page') {
                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                        }
                    }
                    ?>
                    <label for="per_page_select_participations" class="per-page-label">
                        <i class="fas fa-list-ul"></i>Elementi:
                    </label>
                    <select id="per_page_select_participations" name="per_page" class="per-page-select custom-select-styled"
                        <?php // Aggiunta classe custom-select-styled ?> aria-label="Elementi per pagina"
                        onchange="this.form.submit()">
                        <?php foreach ($valid_per_page_options_participations as $option): ?>
                            <option value="<?php echo $option; ?>" <?php if ($per_page == $option)
                                   echo 'selected'; ?>>
                                <?php echo $option; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div id="alert-container-participations" class="custom-alert-container-static" style="margin-bottom: 20px;"
        aria-live="polite">
        <?php /* ... il tuo codice PHP per gli alert ... */ ?>
    </div>

    <div id="participations-list-container"> <?php // Cambiato id per chiarezza ?>
        <?php if (!$dbError && empty($participations_to_display) && $total_participations === 0): ?>
            <div class="no-results-box card"> <?php // Aggiunta classe .card per coerenza ?>
                <div class="no-results-icon"><i class="fas fa-meh"></i></div>
                <h3 class="no-results-title">Nessuna Partecipazione Trovata</h3>
                <p class="no-results-text">Non hai ancora partecipato a nessun quiz. <br>Esplora i quiz disponibili e
                    mettiti alla prova!</p>
                <a href="index.php" class="btn button-primary-styled btn-lg"> <?php // Aggiunto btn-lg ?>
                    <i class="fas fa-search"></i> Vedi i Quiz
                </a>
            </div>
        <?php elseif (!$dbError && empty($participations_to_display) && $total_participations > 0): ?>
            <div class="no-results-box card">
                <div class="no-results-icon"><i class="fas fa-info-circle"></i></div>
                <p class="no-results-text">Nessuna partecipazione da visualizzare in questa pagina.</p>
                <p><a href="quiz_participations.php" class="text-link">Torna alla prima pagina</a></p>
            </div>
        <?php elseif (!empty($participations_to_display)): ?>
            <div class="participations-grid"> <?php // Wrapper per le card se vuoi un layout a griglia ?>
                <?php foreach ($participations_to_display as $p): ?>
                    <?php
                    // ... (la tua logica PHP per scorePercentage, scoreClass, scoreText è OK) ...
                    $scorePercentage = 0;
                    if (isset($p['punteggio_massimo_quiz']) && $p['punteggio_massimo_quiz'] > 0) {
                        $scorePercentage = ($p['punteggio_ottenuto'] / $p['punteggio_massimo_quiz']) * 100;
                    }
                    $scoreClass = '';
                    if (isset($p['punteggio_massimo_quiz']) && $p['punteggio_massimo_quiz'] > 0) {
                        if ($scorePercentage >= 75)
                            $scoreClass = 'score-high';
                        else if ($scorePercentage >= 50)
                            $scoreClass = 'score-medium';
                        else
                            $scoreClass = 'score-low';
                    }

                    $scorePercentage_formatted = number_format($scorePercentage, 1, '.', '');

                    $scoreText = (isset($p['punteggio_massimo_quiz']) && $p['punteggio_massimo_quiz'] > 0) ?
                        "<span class=\"score-value {$scoreClass}\">" . htmlspecialchars($p['punteggio_ottenuto']) . "</span> / <span class=\"score-max\">" . htmlspecialchars($p['punteggio_massimo_quiz']) . "</span> <span class='score-unit'>punti</span> <span class='score-percentage'>({$scorePercentage_formatted}%)</span>" :
                        (isset($p['punteggio_ottenuto']) && $p['punteggio_ottenuto'] > 0 ? "<span class=\"score-value\">" . htmlspecialchars($p['punteggio_ottenuto']) . "</span> <span class='score-unit'>punti (max non calcolabile)</span>" : "<span class='score-unavailable'>Punteggio non disponibile</span>");
                    ?>
                    <div class="participation-card card"> <?php // Rimosso card-hover-effect, lo gestiremo in CSS ?>
                        <div class="card-header participation-card-header">
                            <h3 class="participation-card-title">
                                <a
                                    href="quiz_view.php?id=<?php echo htmlspecialchars($p['quiz_id']); ?>"><?php echo htmlspecialchars($p['quiz_titolo'] ?: 'Quiz Senza Titolo'); ?></a>
                            </h3>
                            <span class="participation-card-date">
                                <i class="far fa-calendar-alt"></i> <?php // Usato far per un look più leggero ?>
                                <?php echo htmlspecialchars($p['data_partecipazione'] ?: 'N/D'); ?>
                            </span>
                        </div>
                        <div class="card-body participation-card-body">
                            <div class="participation-score-section">
                                <div class="score-label"><i class="fas fa-star"></i> Punteggio Ottenuto:</div>
                                <div class="score-display"><?php echo $scoreText; ?></div>
                            </div>
                            <?php if (isset($p['punteggio_massimo_quiz']) && $p['punteggio_massimo_quiz'] > 0): ?>
                                <div class="progress-bar-container"> <?php // Rimosso -custom ?>
                                    <div class="progress-bar <?php // Rimosso -custom, aggiunte classi per colore ?>
                                <?php echo $scorePercentage >= 75 ? 'progress-bar-success' : ($scorePercentage >= 50 ? 'progress-bar-warning' : 'progress-bar-danger'); ?>"
                                        role="progressbar"
                                        style="width: <?php echo htmlspecialchars($scorePercentage_formatted); ?>%;"
                                        aria-valuenow="<?php echo htmlspecialchars($scorePercentage_formatted); ?>"
                                        aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer participation-card-actions">
                            <a href="quiz_results.php?participation_id=<?php echo htmlspecialchars($p['partecipazione_id']); ?>"
                                class="btn">
                                <i class="fas fa-poll-h"></i> Vedi Risultati
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div> <?php // Fine .participations-grid ?>
        <?php endif; ?>
    </div>

    <?php if (!$dbError && $total_pages > 1): ?>
        <nav class="pagination-container" aria-label="Paginazione delle tue partecipazioni"> <?php // Classe per il nav ?>
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    <i class="fas fa-list-ol"></i>
                    <span>Visualizzazione
                        <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_participations); ?> di
                        <?php echo $total_participations; ?> partecipazioni</span>
                </div>
                <div class="pagination-controls">
                    <?php /* ... La tua logica PHP per i link di paginazione è OK ... */ ?>
                </div>
            </div>
        </nav>
    <?php endif; ?>
</div>

<?php
include 'includes/footer.php';
?>