<?php

/**
 * Pagina di visualizzazione dei quiz creati dall'utente.
 * 
 * Questa pagina mostra all'utente i quiz che ha creato, con la possibilità di modificarli o eliminarli.
 */

require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se l'utente non è loggato, reindirizza alla pagina di login.
if (!isset($_SESSION['user']['nomeUtente'])) {
    header('Location: auth_login.php');
    exit;
}

$nomeUtente = $_SESSION['user']['nomeUtente'];
$dbError = null;

// --- PARAMETRI PAGINAZIONE ---
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
$valid_per_page_options = [5, 10, 20, 50, 100]; // Aggiunto 100 come richiesto

if ($page < 1)
    $page = 1;
if (!in_array($per_page, $valid_per_page_options))
    $per_page = 10;


$count_sql = "SELECT COUNT(*) as total 
              FROM Quiz AS Q
              WHERE Q.creatore = :nomeUtente";
$total_quizzes = 0;

try {
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
    $count_stmt->execute();
    $total_quizzes = (int) $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    error_log("Errore query conteggio quiz per utente $nomeUtente: " . $e->getMessage());
    $dbError = "Si è verificato un errore durante il recupero del numero di quiz.";
}

$total_pages = ($total_quizzes > 0) ? ceil($total_quizzes / $per_page) : 1;
if ($page > $total_pages)
    $page = $total_pages;
if ($page < 1 && $total_quizzes > 0)
    $page = 1;
elseif ($total_quizzes === 0)
    $page = 1;


$offset = ($page - 1) * $per_page;

$sql = "SELECT Q.titolo, Q.dataInizio, Q.dataFine, Q.codice
        FROM Quiz AS Q
        WHERE Q.creatore = :nomeUtente
        ORDER BY Q.dataInizio DESC, Q.titolo ASC
        LIMIT :offset, :per_page";

$quizzes_to_display = [];

if (!$dbError) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':per_page', $per_page, PDO::PARAM_INT);
        $stmt->execute();
        $quizzes_to_display = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Errore query recupero quiz per utente $nomeUtente: " . $e->getMessage());
        $dbError = "Si è verificato un errore durante il recupero dei quiz. Riprova più tardi.";
        $quizzes_to_display = [];
    }
}

include 'includes/header.php';
?>

<script src="assets/js/main.js" defer></script>

<div class="container" style="padding-top: 20px; padding-bottom: 40px;">

    <!-- Riga del titolo e, separatamente, controllo "Elementi per pagina" -->
    <div
        style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center;">
            <i class="fas fa-th-list" style="font-size: 1.8rem; margin-right: 12px; color: var(--main-color);"></i>
            <h2 style="font-size: 1.8rem; color: var(--dark-color); margin: 0; line-height: 1.3;">
                I Miei Quiz
                <?php if ($total_quizzes > 0): ?>
                    <small style="font-size: 0.85em; color: var(--text-muted); font-weight: normal; margin-left: 10px;">
                        (<?php echo $total_quizzes; ?> total<?php echo ($total_quizzes !== 1) ? 'i' : 'o'; ?>)
                    </small>
                <?php endif; ?>
            </h2>
        </div>

        <?php // MODIFICA CONDIZIONE QUI: Mostra se ci sono quiz, anche se solo 1 pagina ?>
        <?php if ($total_quizzes > 0): ?>
            <div class="controls-container" style="margin-bottom: 0;">
                <?/* Assicurati che questa classe .controls-container esista e sia stilizzata nel tuo CSS come in index.php per allineare il form */ ?>
                <form method="GET" id="per-page-form-myquiz" action="quiz_my.php"
                    style="display: flex; align-items: center; margin:0;">
                    <?php
                    foreach ($_GET as $key => $value) {
                        if ($key !== 'per_page' && $key !== 'page') {
                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                        }
                    }
                    ?>
                    <label for="per_page_select_myquiz"
                        style="font-size: 0.9rem; color: var(--text-dark); font-weight: 500; margin-right: 8px; white-space: nowrap;">
                        <i class="fas fa-list-ul" style="margin-right: 4px;"></i>Elementi:
                    </label>
                    <select id="per_page_select_myquiz" name="per_page" aria-label="Elementi per pagina"
                        style="padding: 7px 10px; font-size: 0.9rem; border: 1px solid var(--border-color, #ced4da); border-radius: 4px; background-color: #fff; color: var(--text-dark); cursor:pointer; min-width: 60px;"
                        onchange="this.form.submit()">
                        <?php foreach ($valid_per_page_options as $option): ?>
                            <option value="<?php echo $option; ?>" <?php if ($per_page == $option)
                                   echo 'selected'; ?>>
                                <?php echo $option; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- CONTENITORE PER GLI ALERT -->
    <div id="alert-container-page" class="custom-alert-container-static" style="margin-bottom: 20px;">
        <!-- Gli alert verranno inseriti qui da JavaScript -->
    </div>


    <?php if ($dbError): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
            <?php echo htmlspecialchars($dbError); ?>
        </div>
    <?php elseif (empty($quizzes_to_display) && $total_quizzes === 0): ?>
        <div class="no-results"
            style="text-align: center; padding: 25px; border: 1px dashed var(--border-color); border-radius: 8px; background-color: var(--background-card);">
            <i class="fas fa-folder-open"
                style="font-size: 3rem; color: var(--text-muted); margin-bottom: 15px; display: block;"></i>
            <p style="font-size: 1.1rem; color: var(--text-dark); margin-bottom: 8px;">Non hai ancora creato nessun quiz.
            </p>
            <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 25px;">Inizia subito a creare contenuti
                coinvolgenti!</p>
            <a href="quiz_create.php" class="btn button-primary-styled">
                <i class="fas fa-plus" style="margin-right: 6px;"></i> Crea il tuo primo Quiz!
            </a>
        </div>
    <?php elseif (empty($quizzes_to_display) && $total_quizzes > 0): ?>
        <div class="no-results card">
            <p><i class="fas fa-info-circle"></i> Nessun quiz da visualizzare in questa pagina. Probabilmente hai
                selezionato un numero di pagina non valido.</p>
            <p><a href="quiz_my.php" class="text-link">Torna alla prima pagina dei tuoi quiz</a></p>
        </div>
    <?php else: ?>
        <div class='quiz-list'>
            <?php foreach ($quizzes_to_display as $quiz): ?>
                <div class='quiz-item card'>
                    <h3 class='quiz-title'><?php echo htmlspecialchars($quiz['titolo']); ?></h3>
                    <div class='quiz-meta'>
                        <p title="Periodo di disponibilità">
                            <i class="fas fa-calendar-alt" style="margin-right: 6px; color: var(--secondary-gray);"></i>
                            Disponibile dal &nbsp; <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($quiz['dataInizio']))); ?></strong>
                            &nbsp; al &nbsp;<strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($quiz['dataFine']))); ?></strong>
                        </p>
                        <p title="Codice Quiz">
                            <i class="fas fa-key" style="margin-right: 6px; color: var(--secondary-gray);"></i>
                            Codice:   <strong><?php echo htmlspecialchars($quiz['codice']); ?></strong>
                        </p>
                    </div>
                    <div class='quiz-actions quiz-actions-manage'>
                        <a href='quiz_info.php?id=<?php echo htmlspecialchars($quiz['codice']); ?>'
                            class='btn button-info-styled btn-sm' title="Info">
                            <i class="fas fa-info-circle"></i> Info
                        </a>
                        <a href='quiz_view.php?id=<?php echo htmlspecialchars($quiz['codice']); ?>'
                            class='btn button-primary-styled btn-sm' title="Visualizza">
                            <i class="fas fa-eye"></i> Visualizza
                        </a>
                        <a href='quiz_modify.php?id=<?php echo htmlspecialchars($quiz['codice']); ?>'
                            class='btn btn-secondary btn-sm' title="Modifica">
                            <i class="fas fa-edit"></i> Modifica
                        </a>
                        <button data-delid="<?php echo htmlspecialchars($quiz['codice']); ?>"
                            data-quiz-title="<?php echo htmlspecialchars($quiz['titolo']); ?>"
                            class='btn button-danger-styled delete-quiz-btn btn-sm' title="Elimina">
                            <i class="fas fa-trash-alt"></i> Elimina
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        // --- HTML DELLA PAGINAZIONE ---
        // Questa condizione rimane invariata: mostra la paginazione solo se c'è più di una pagina
        if ($total_pages > 1):
            ?>
            <nav class="pagination" aria-label="Paginazione dei tuoi quiz" style="margin-top: 30px;">
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        <i class="fas fa-list-ol"></i>
                        Visualizzazione <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_quizzes); ?> di
                        <?php echo $total_quizzes; ?> quiz
                    </div>
                    <div class="pagination-controls">
                        <?php
                        if (!function_exists('getMyQuizPaginationUrl')) {
                            function getMyQuizPaginationUrl($page_num_target)
                            {
                                $current_params = $_GET;
                                $current_params['page'] = $page_num_target;
                                // 'per_page' sarà già in $current_params se è stato settato
                                return 'quiz_my.php?' . http_build_query($current_params);
                            }
                        }
                        ?>
                        <div class="compact-pagination" role="navigation" aria-label="Navigazione pagine">
                            <?php if ($page > 1): ?>
                                <a href="<?php echo getMyQuizPaginationUrl($page - 1); ?>" class="page-item page-nav"
                                    title="Pagina precedente">
                                    <i class="fas fa-chevron-left"></i> Prec
                                </a>
                            <?php else: ?>
                                <span class="page-item page-nav disabled" title="Sei alla prima pagina">
                                    <i class="fas fa-chevron-left"></i> Prec
                                </span>
                            <?php endif; ?>

                            <?php
                            $links_range = 2;
                            $show_first_last = true;

                            for ($i = 1; $i <= $total_pages; $i++) {
                                if ($i == 1 || $i == $total_pages || ($i >= $page - $links_range && $i <= $page + $links_range)) {
                                    if ($page == $i) {
                                        echo '<span class="page-item active" aria-current="page">' . $i . '</span>';
                                    } else {
                                        echo '<a href="' . getMyQuizPaginationUrl($i) . '" class="page-item" title="Vai a pagina ' . $i . '">' . $i . '</a>';
                                    }
                                } elseif (
                                    ($i == $page - $links_range - 1 && $page - $links_range > 2 && $show_first_last) ||
                                    ($i == $page + $links_range + 1 && $page + $links_range < $total_pages - 1 && $show_first_last)
                                ) {
                                    echo '<span class="page-dots">...</span>';
                                }
                            }
                            ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="<?php echo getMyQuizPaginationUrl($page + 1); ?>" class="page-item page-nav"
                                    title="Pagina successiva">
                                    Succ <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="page-item page-nav disabled" title="Sei all'ultima pagina">
                                    Succ <i class="fas fa-chevron-right"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </nav>
        <?php endif; ?>

        <div id="confirmDeleteModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h2 style="color: var(--dark-color); display: flex; align-items: center; margin-top:0; font-size: 1.4rem;">
                    <i class="fas fa-exclamation-triangle"
                        style="color: #dc3545; margin-right: 10px; font-size: 1.5rem;"></i>
                    Conferma Eliminazione
                </h2>
                <p style="margin-top: 15px; margin-bottom: 10px;">
                    Sei sicuro di voler eliminare il quiz "<strong><span id="quizTitleToDelete">[Nome
                            Quiz]</span></strong>"?
                </p>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5;">
                    L'azione è <strong>irreversibile</strong>. Tutti i dati associati al quiz, incluse le domande, le
                    risposte e le eventuali partecipazioni, verranno persi definitivamente.
                </p>
                <div class="modal-actions" style="margin-top: 25px; text-align: right;">
                    <button id="cancelDeleteBtn" class="btn btn-secondary" style="margin-right: 10px;">
                        <i class="fas fa-times" style="margin-right: 6px;"></i> Annulla
                    </button>
                    <button id="confirmDeleteActionBtn" class="btn button-danger-styled">
                        <i class="fas fa-trash-alt" style="margin-right: 6px;"></i> Elimina Quiz
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php
include 'includes/footer.php';
?>