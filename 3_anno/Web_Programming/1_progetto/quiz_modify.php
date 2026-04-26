<?php

/**
 * Pagina di modifica quiz.
 * 
 * Questa pagina permette all'utente di modificare un quiz esistente.
 */

include "includes/header.php"; // Assicurati che header.php non faccia output prima di session_start()
require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Controllo accesso ---
$nomeUtente = $_SESSION['user']['nomeUtente'] ?? null;
if (!isset($nomeUtente)) {
    header('Location: auth_login.php');
    exit;
}

// --- Recupero ID Quiz ---
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die("ID Quiz non valido o mancante.");
}
$quizId = (int) $_GET['id'];

// --- Fetch Dati Quiz, Domande, Risposte ---
try {
    $sqlQuiz = "SELECT codice, titolo, dataInizio, dataFine FROM Quiz WHERE codice = :quizId AND creatore = :nomeUtente";
    $stmtQuiz = $pdo->prepare($sqlQuiz);
    $stmtQuiz->bindParam(':quizId', $quizId, PDO::PARAM_INT);
    $stmtQuiz->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
    $stmtQuiz->execute();
    $quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        die("Quiz non trovato o non sei autorizzato a modificarlo.");
    }

    $sqlDomande = "SELECT numero, testo FROM Domanda WHERE quiz = :quizId ORDER BY numero ASC";
    $stmtDomande = $pdo->prepare($sqlDomande);
    $stmtDomande->bindParam(':quizId', $quizId, PDO::PARAM_INT);
    $stmtDomande->execute();
    $domande = $stmtDomande->fetchAll(PDO::FETCH_ASSOC);

    $sqlRisposte = "SELECT domanda, numero, testo, tipo, punteggio FROM Risposta WHERE quiz = :quizId ORDER BY domanda ASC, numero ASC";
    $stmtRisposte = $pdo->prepare($sqlRisposte);
    $stmtRisposte->bindParam(':quizId', $quizId, PDO::PARAM_INT);
    $stmtRisposte->execute();
    $risposteRaw = $stmtRisposte->fetchAll(PDO::FETCH_ASSOC);

    $risposteOrganizzate = [];
    foreach ($risposteRaw as $risposta) {
        $risposteOrganizzate[$risposta['domanda']][] = $risposta;
    }

} catch (PDOException $e) {
    error_log("Errore Database in quiz_modify.php: " . $e->getMessage());
    die("Si è verificato un errore nel caricamento dei dati del quiz. Riprova più tardi.");
}

?>

<body>
    <div class="main-content">
        <div class="content">
            <h1 class="page-main-title">Modifica Quiz: <?php echo htmlspecialchars($quiz['titolo']); ?></h1>

            <div id="alert-container-page-modify" class="alert-placeholder"></div>

            <form id="edit-quiz-form" action="api/quiz.php?action=update" method="POST">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz['codice']; ?>">

                <div class="custom-card box-shadow-light margin-bottom-large">
                    <div class="custom-card-header">
                        <h2 class="custom-card-title">Dettagli del Quiz</h2>
                    </div>
                    <div class="custom-card-body">
                        <div class="form-field-group margin-bottom-medium">
                            <label for="titolo" class="form-label-styled">Titolo del Quiz:</label>
                            <input type="text" id="titolo" name="titolo" class="form-input-styled"
                                value="<?php echo htmlspecialchars($quiz['titolo']); ?>" required>
                        </div>
                        <div class="form-row">
                            <div class="form-col-half">
                                <div class="form-field-group margin-bottom-medium">
                                    <label for="dataInizio" class="form-label-styled">Data di Inizio:</label>
                                    <input type="date" id="dataInizio" name="dataInizio" class="form-input-styled"
                                        value="<?php echo htmlspecialchars($quiz['dataInizio']); ?>" required>
                                </div>
                            </div>
                            <div class="form-col-half">
                                <div class="form-field-group margin-bottom-medium">
                                    <label for="dataFine" class="form-label-styled">Data di Fine:</label>
                                    <input type="date" id="dataFine" name="dataFine" class="form-input-styled"
                                        value="<?php echo htmlspecialchars($quiz['dataFine']); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="custom-card box-shadow-light">
                    <div class="custom-card-header">
                        <h2 class="custom-card-title">Domande e Risposte</h2>
                    </div>
                    <div class="custom-card-body">
                        <div id="questions-container">
                            <?php if (empty($domande)): ?>
                                <p id="no-questions-message"
                                    class="message-placeholder-styled text-align-center padding-vertical-medium">Nessuna
                                    domanda presente. Clicca su "Aggiungi Domanda" per iniziare.</p>
                            <?php else: ?>
                                <p id="no-questions-message"
                                    class="message-placeholder-styled text-align-center padding-vertical-medium"
                                    style="display: none;">Nessuna domanda presente. Clicca su "Aggiungi Domanda" per
                                    iniziare.</p>
                            <?php endif; ?>

                            <?php foreach ($domande as $indexDomanda => $domanda): ?>
                                <div class="question-block-styled margin-bottom-large"
                                    data-question-index="<?php echo $indexDomanda; ?>">
                                    <input type="hidden"
                                        name="questions[<?php echo $indexDomanda; ?>][original_numero_domanda]"
                                        value="<?php echo $domanda['numero']; ?>">

                                    <div
                                        class="question-header-styled flex-container flex-justify-between flex-align-center padding-medium margin-bottom-medium">
                                        <h3 class="question-title-styled">Domanda <span
                                                class="question-number"><?php echo $indexDomanda + 1; ?></span></h3>
                                        <button type="button"
                                            class="button-styled button-danger-outline-styled button-small-styled remove-question-btn"
                                            title="Rimuovi Domanda <?php echo $indexDomanda + 1; ?>">
                                            <i class="fas fa-trash-alt icon-spacing-right-small"></i>Rimuovi Domanda
                                        </button>
                                    </div>
                                    <div class="question-body-styled padding-medium-horizontal">
                                        <div class="form-field-group margin-bottom-medium">
                                            <label for="question_text_<?php echo $indexDomanda; ?>"
                                                class="form-label-styled label-bold-styled">Testo della Domanda:</label>
                                            <textarea id="question_text_<?php echo $indexDomanda; ?>"
                                                class="textarea-styled" name="questions[<?php echo $indexDomanda; ?>][text]"
                                                rows="3" required
                                                placeholder="Inserisci qui il testo della domanda..."><?php echo htmlspecialchars($domanda['testo']); ?></textarea>
                                        </div>

                                        <h4 class="answers-area-title-styled margin-top-medium margin-bottom-small">
                                            Risposte:</h4>
                                        <div class="answers-container-styled padding-left-small"
                                            id="answers-container-q<?php echo $indexDomanda; ?>">
                                            <?php $rispostePerDomanda = $risposteOrganizzate[$domanda['numero']] ?? []; ?>
                                            <?php if (empty($rispostePerDomanda)): ?>
                                                <p class="message-inline-styled text-description-styled no-answers-message"
                                                    id="no-answers-for-q<?php echo $indexDomanda; ?>">Nessuna risposta aggiunta
                                                    per questa domanda.</p>
                                            <?php else: ?>
                                                <p class="message-inline-styled text-description-styled no-answers-message"
                                                    id="no-answers-for-q<?php echo $indexDomanda; ?>" style="display: none;">
                                                    Nessuna risposta aggiunta per questa domanda.</p>
                                            <?php endif; ?>

                                            <?php foreach ($rispostePerDomanda as $indexRisposta => $risposta): ?>
                                                <div class="answer-block-styled padding-medium margin-bottom-medium box-shadow-extra-light"
                                                    data-answer-index="<?php echo $indexRisposta; ?>">
                                                    <input type="hidden"
                                                        name="questions[<?php echo $indexDomanda; ?>][answers][<?php echo $indexRisposta; ?>][original_numero_risposta]"
                                                        value="<?php echo $risposta['numero']; ?>">

                                                    <div
                                                        class="answer-header-styled flex-container flex-justify-between flex-align-center margin-bottom-small">
                                                        <label
                                                            class="form-label-styled label-bold-styled label-accent-color-styled answer-label-dynamic">Risposta
                                                            <span class="answer-number"><?php echo $indexRisposta + 1; ?></span></label>
                                                    </div>

                                                    <div class="form-field-group margin-bottom-small">
                                                        <label
                                                            for="answer_text_<?php echo $indexDomanda; ?>_<?php echo $indexRisposta; ?>"
                                                            class="form-label-styled label-small-styled">Testo della
                                                            Risposta:</label>
                                                        <input type="text"
                                                            id="answer_text_<?php echo $indexDomanda; ?>_<?php echo $indexRisposta; ?>"
                                                            class="form-input-styled input-small-styled"
                                                            name="questions[<?php echo $indexDomanda; ?>][answers][<?php echo $indexRisposta; ?>][text]"
                                                            value="<?php echo htmlspecialchars($risposta['testo']); ?>" required
                                                            placeholder="Testo della risposta">
                                                    </div>

                                                    <?php $isCorrect = ($risposta['tipo'] == 'Corretta'); ?>
                                                    <div class="form-field-group margin-bottom-small">
                                                        <label
                                                            class="form-label-styled label-small-styled display-block margin-bottom-extra-small">Tipo
                                                            Risposta:</label>
                                                        <div class="radio-option-group-styled">
                                                            <div class="radio-option-styled">
                                                                <input class="radio-input-styled" type="radio"
                                                                    name="questions[<?php echo $indexDomanda; ?>][answers][<?php echo $indexRisposta; ?>][type]"
                                                                    id="q<?php echo $indexDomanda; ?>a<?php echo $indexRisposta; ?>tipoC"
                                                                    value="Corretta" <?php echo $isCorrect ? 'checked' : ''; ?>
                                                                    required>
                                                                <label class="radio-label-styled"
                                                                    for="q<?php echo $indexDomanda; ?>a<?php echo $indexRisposta; ?>tipoC">Corretta</label>
                                                            </div>
                                                            <div class="radio-option-styled">
                                                                <input class="radio-input-styled" type="radio"
                                                                    name="questions[<?php echo $indexDomanda; ?>][answers][<?php echo $indexRisposta; ?>][type]"
                                                                    id="q<?php echo $indexDomanda; ?>a<?php echo $indexRisposta; ?>tipoS"
                                                                    value="Sbagliata" <?php echo !$isCorrect ? 'checked' : ''; ?> required>
                                                                <label class="radio-label-styled"
                                                                    for="q<?php echo $indexDomanda; ?>a<?php echo $indexRisposta; ?>tipoS">Sbagliata</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-field-group points-group" <?php echo !$isCorrect ? 'style="display:none;"' : ''; ?>>
                                                        <label
                                                            for="q<?php echo $indexDomanda; ?>a<?php echo $indexRisposta; ?>punti"
                                                            class="form-label-styled label-small-styled">Punti (se
                                                            corretta):</label>
                                                        <input type="number"
                                                            id="q<?php echo $indexDomanda; ?>a<?php echo $indexRisposta; ?>punti"
                                                            class="form-input-styled input-small-styled input-numerical-styled"
                                                            name="questions[<?php echo $indexDomanda; ?>][answers][<?php echo $indexRisposta; ?>][points]"
                                                            value="<?php echo htmlspecialchars($risposta['punteggio'] ?? ($isCorrect ? 1 : 0)); ?>"
                                                            required min="0">
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button"
                                            class="button-styled button-secondary-styled button-small-styled margin-top-medium add-answer-btn">
                                            <i class="fas fa-plus-circle icon-spacing-right-small"></i>Aggiungi Risposta a
                                            Domanda <span
                                                class="question-number-btn-text"><?php echo $indexDomanda + 1; ?></span>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div
                            class="main-actions-bar flex-container flex-gap-medium margin-top-large padding-top-medium border-top-light">
                            <button type="button" id="add-question-btn" class="button-styled button-secondary-styled">
                                <i class="fas fa-plus icon-spacing-right"></i> Aggiungi Domanda
                            </button>
                            <button type="submit" class="button-styled button-success-styled">
                                <i class="fas fa-save icon-spacing-right"></i> Salva Modifiche al Quiz
                            </button>
                        </div>
                    </div> <!-- fine custom-card-body -->
                </div> <!-- fine custom-card -->
            </form>
        </div> <!-- fine content -->
    </div> <!-- fine main-content -->


    <!-- Template per Nuova Domanda (nascosto) - Stile come quiz_create.php -->
    <template id="question-template">
        <div class="question-block-styled margin-bottom-large" data-question-index="__Q_INDEX__">
            <div
                class="question-header-styled flex-container flex-justify-between flex-align-center padding-medium margin-bottom-medium">
                <h3 class="question-title-styled">Domanda <span class="question-number">__Q_DISPLAY_NUM__</span></h3>
                <button type="button"
                    class="button-styled button-danger-outline-styled button-small-styled remove-question-btn"
                    title="Rimuovi Domanda __Q_DISPLAY_NUM__">
                    <i class="fas fa-trash-alt icon-spacing-right-small"></i>Rimuovi Domanda
                </button>
            </div>
            <div class="question-body-styled padding-medium-horizontal">
                <div class="form-field-group margin-bottom-medium">
                    <label for="question_text___Q_INDEX__" class="form-label-styled label-bold-styled">Testo della
                        Domanda:</label>
                    <textarea id="question_text___Q_INDEX__" class="textarea-styled" name="questions[__Q_INDEX__][text]"
                        rows="3" required placeholder="Inserisci qui il testo della domanda..."></textarea>
                </div>
                <h4 class="answers-area-title-styled margin-top-medium margin-bottom-small">Risposte:</h4>
                <div class="answers-container-styled padding-left-small" id="answers-container-q__Q_INDEX__">
                    <p class="message-inline-styled text-description-styled no-answers-message"
                        id="no-answers-for-q__Q_INDEX__" style="display: block;">Nessuna risposta aggiunta.</p>
                </div>
                <button type="button"
                    class="button-styled button-secondary-styled button-small-styled margin-top-medium add-answer-btn">
                    <i class="fas fa-plus-circle icon-spacing-right-small"></i>Aggiungi Risposta a Domanda <span
                        class="question-number-btn-text">__Q_DISPLAY_NUM__</span>
                </button>
            </div>
        </div>
    </template>

    <!-- Template per Nuova Risposta (nascosto) - Stile come quiz_create.php -->
    <template id="answer-template">
        <div class="answer-block-styled padding-medium margin-bottom-medium box-shadow-extra-light"
            data-answer-index="__A_INDEX__">
            <div class="answer-header-styled flex-container flex-justify-between flex-align-center margin-bottom-small">
                <label
                    class="form-label-styled label-bold-styled label-accent-color-styled answer-label-dynamic">Risposta
                    <span class="answer-number">__A_DISPLAY_NUM__</span></label>
                <button type="button"
                    class="button-styled button-danger-styled button-extra-small-styled remove-answer-btn"
                    title="Rimuovi Risposta">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="form-field-group margin-bottom-small">
                <label for="answer_text___Q_INDEX_____A_INDEX__" class="form-label-styled label-small-styled">Testo
                    della Risposta:</label>
                <input type="text" id="answer_text___Q_INDEX_____A_INDEX__" class="form-input-styled input-small-styled"
                    name="questions[__Q_INDEX__][answers][__A_INDEX__][text]" required
                    placeholder="Testo della risposta">
            </div>
            <div class="form-field-group margin-bottom-small">
                <label class="form-label-styled label-small-styled display-block margin-bottom-extra-small">Tipo
                    Risposta:</label>
                <div class="radio-option-group-styled">
                    <div class="radio-option-styled">
                        <input class="radio-input-styled" type="radio"
                            name="questions[__Q_INDEX__][answers][__A_INDEX__][type]" id="q__Q_INDEX__a__A_INDEX__tipoC"
                            value="Corretta" required>
                        <label class="radio-label-styled" for="q__Q_INDEX__a__A_INDEX__tipoC">Corretta</label>
                    </div>
                    <div class="radio-option-styled">
                        <input class="radio-input-styled" type="radio"
                            name="questions[__Q_INDEX__][answers][__A_INDEX__][type]" id="q__Q_INDEX__a__A_INDEX__tipoS"
                            value="Sbagliata" checked>
                        <label class="radio-label-styled" for="q__Q_INDEX__a__A_INDEX__tipoS">Sbagliata</label>
                    </div>
                </div>
            </div>
            <div class="form-field-group points-group" style="display:none;">
                <!-- Nascosto di default per nuova risposta sbagliata -->
                <label for="q__Q_INDEX__a__A_INDEX__punti" class="form-label-styled label-small-styled">Punti (se
                    corretta):</label>
                <input type="number" id="q__Q_INDEX__a__A_INDEX__punti"
                    class="form-input-styled input-small-styled input-numerical-styled"
                    name="questions[__Q_INDEX__][answers][__A_INDEX__][points]" value="0" required min="0">
            </div>
        </div>
    </template>

    <?php include 'includes/footer.php'; // Assicurati che footer.php includa jQuery e main.js ?>
</body>

</html>