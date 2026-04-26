<?php
/**
 * Pagina di partecipazione al Quiz.
 *
 * Questa pagina permette agli utenti di partecipare a un quiz esistente.
 */

include 'includes/header.php';
require_once 'config/database.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['nomeUtente'])) {
    $_SESSION['error_message'] = "Devi effettuare il login per partecipare ai quiz.";
    header('Location: auth_login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['info_message'] = "ID del quiz non specificato.";
    header('Location: index.php');
    exit;
}

$quiz_id = (int) $_GET['id'];
$user_name = $_SESSION['user']['nomeUtente'];
$today = date('Y-m-d');
$error_message_page = null;
$quiz = null;
$questions = [];
$quiz_details_for_error = null;

try {
    $participation_check_sql = "SELECT codice FROM Partecipazione WHERE utente = :nomeUtente AND quiz = :quizId";
    $part_check_stmt = $pdo->prepare($participation_check_sql);
    $part_check_stmt->bindParam(':nomeUtente', $user_name, PDO::PARAM_STR);
    $part_check_stmt->bindParam(':quizId', $quiz_id, PDO::PARAM_INT);
    $part_check_stmt->execute();
    if ($part_check_stmt->fetch()) {
        $_SESSION['info_message'] = "Hai già partecipato a questo quiz. Puoi vedere i tuoi risultati nella sezione 'Le Mie Partecipazioni'.";
        header('Location: quiz_participations.php');
        exit;
    }

    $sql_quiz = "SELECT * FROM Quiz WHERE codice = :id AND dataInizio <= :oggiDataInizio AND dataFine >= :oggiDataFine";
    $stmt_quiz = $pdo->prepare($sql_quiz);
    $stmt_quiz->bindParam(':id', $quiz_id, PDO::PARAM_INT);
    $stmt_quiz->bindParam(':oggiDataInizio', $today, PDO::PARAM_STR);
    $stmt_quiz->bindParam(':oggiDataFine', $today, PDO::PARAM_STR);
    $stmt_quiz->execute();
    $quiz = $stmt_quiz->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        $check_existence_sql = "SELECT titolo, dataInizio, dataFine FROM Quiz WHERE codice = :id";
        $check_stmt = $pdo->prepare($check_existence_sql);
        $check_stmt->bindParam(':id', $quiz_id, PDO::PARAM_INT);
        $check_stmt->execute();
        $quiz_details_for_error = $check_stmt->fetch(PDO::FETCH_ASSOC);

        $quiz_title_display = 'ID ' . htmlspecialchars($quiz_id);
        if ($quiz_details_for_error && !empty($quiz_details_for_error['titolo'])) {
            $quiz_title_display = htmlspecialchars($quiz_details_for_error['titolo']);
        } elseif ($quiz && !empty($quiz['titolo'])) {
            $quiz_title_display = htmlspecialchars($quiz['titolo']);
        }

        if (!$quiz_details_for_error) {
            $_SESSION['error_message'] = "Quiz con ID " . htmlspecialchars($quiz_id) . " non trovato.";
        } elseif ($quiz_details_for_error['dataInizio'] > $today) {
            $_SESSION['info_message'] = "Il quiz \"" . $quiz_title_display . "\" non è ancora iniziato. Sarà disponibile dal " . date('d/m/Y', strtotime($quiz_details_for_error['dataInizio'])) . ".";
        } elseif ($quiz_details_for_error['dataFine'] < $today) {
            $_SESSION['info_message'] = "Il quiz \"" . $quiz_title_display . "\" è terminato il " . date('d/m/Y', strtotime($quiz_details_for_error['dataFine'])) . " e non accetta più partecipazioni.";
        } else {
            $_SESSION['error_message'] = "Quiz non disponibile (" . $quiz_title_display . ").";
        }
        header('Location: index.php');
        exit;
    }

    $sql_domande = "SELECT * FROM Domanda WHERE quiz = :quiz ORDER BY numero";
    $stmt_domande = $pdo->prepare($sql_domande);
    $stmt_domande->bindParam(':quiz', $quiz_id, PDO::PARAM_INT);
    $stmt_domande->execute();
    $questions = $stmt_domande->fetchAll(PDO::FETCH_ASSOC);

    if (empty($questions)) {
        $error_message_page = "Questo quiz non ha ancora domande associate. Torna più tardi o contatta il creatore del quiz.";
    } else {
        foreach ($questions as &$question_data) { // Rinominato per evitare confusione con la reference in PHP < 8
            $sql_risposte = "SELECT * FROM Risposta WHERE quiz = :quiz AND domanda = :domanda ORDER BY RAND()";
            $stmt_risposte = $pdo->prepare($sql_risposte);
            $stmt_risposte->bindParam(':quiz', $quiz_id, PDO::PARAM_INT);
            $stmt_risposte->bindParam(':domanda', $question_data['numero'], PDO::PARAM_INT);
            $stmt_risposte->execute();
            $question_data['answers'] = $stmt_risposte->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($question_data); // Rimuovi la reference
    }

} catch (PDOException $e) {
    error_log("Errore DB in quiz_participate.php: " . $e->getMessage());
    $error_message_page = "Si è verificato un errore tecnico durante il caricamento del quiz. Riprova più tardi.";
}

$domanda_indice_visualizzato = 0;
?>

<div class="main-content container main-content-page-padding"> <!-- Aggiunta classe per padding globale -->
    <div id="alert-container-participate"></div>
    <!-- Il tuo custom-alert-container-static e margin-bottom è già gestito da showAlert -->

    <?php if ($error_message_page): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message_page); ?></div>
    <?php elseif ($quiz): ?>

        <div class="page-header-controls quiz-participation-page-header">
            <div class="page-title-container">
                <i class="fas fa-pencil-alt page-title-icon"></i>
                <h1 class="page-main-title">
                    Partecipazione al Quiz: <?php echo htmlspecialchars($quiz['titolo']); ?>
                </h1>
            </div>
            <p class="quiz-participation-description">
                Seleziona la risposta che ritieni corretta per ogni domanda e invia il quiz.
            </p>
        </div>

        <form id="participate-form">
            <input type="hidden" name="idQuiz" value="<?php echo $quiz_id; ?>">
            <input type="hidden" name="action" value="submit">

            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $current_question): // Rinominato per chiarezza ?>
                    <?php $domanda_indice_visualizzato++; ?>
                    <div class="question-item card">
                        <div class="question-text">
                            Domanda <?php echo $domanda_indice_visualizzato; ?>:
                            <span><?php echo htmlspecialchars($current_question['testo']); ?></span>
                        </div>

                        <?php if (empty($current_question['answers'])): ?>
                            <p class="no-answers-message">Nessuna opzione di risposta disponibile per questa domanda.</p>
                        <?php else: ?>
                            <div class="answer-options">
                                <?php foreach ($current_question['answers'] as $answer): ?>
                                    <div class="answer-option">
                                        <label>
                                            <input type="radio" name="answers[<?php echo $current_question['numero']; ?>]"
                                                value="<?php echo $answer['numero']; ?>" required>
                                            <span><?php echo htmlspecialchars($answer['testo']); ?></span>
                                            <!-- Avvolto testo in span per styling se necessario -->
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($questions)): ?>
                <div class="form-group text-align-center">
                    <button type="submit" class="btn button-primary-styled btn-lg">
                        <i class="fas fa-paper-plane"></i> Invia Risposte <!-- Rimosso stile inline per l'icona -->
                    </button>
                </div>
            <?php endif; ?>
        </form>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Impossibile caricare i dettagli del quiz. Potrebbe non essere disponibile o l'ID specificato non è corretto.
        </div>
        <p class="text-align-center padding-vertical-medium"><a href="index.php" class="btn">Torna alla Home</a></p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>