<?php
include 'includes/header.php';
require_once 'config/database.php';
$pdo = getPDO();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$quiz_id = (int) $_GET['id'];
$quiz = null;
$questions = [];
$questions_data = [];
$quiz_status = '';
$status_text = '';
$status_icon = 'question-circle';

try {
    $sql_quiz = "SELECT q.*, u.nome, u.cognome
                 FROM Quiz q
                 JOIN Utente u ON q.creatore = u.nomeUtente
                 WHERE q.codice = :quiz_id";
    $stmt_quiz = $pdo->prepare($sql_quiz);
    $stmt_quiz->execute(['quiz_id' => $quiz_id]);
    $quiz = $stmt_quiz->fetch(PDO::FETCH_ASSOC);

    if ($quiz) {
        $sql_questions_load = "SELECT * FROM Domanda WHERE quiz = :quiz_id ORDER BY numero ASC";
        $stmt_questions_load = $pdo->prepare($sql_questions_load);
        $stmt_questions_load->execute(['quiz_id' => $quiz_id]);
        $questions_data = $stmt_questions_load->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($questions_data)) {
            foreach ($questions_data as $question_item) {
                $sql_answers = "SELECT * FROM Risposta WHERE quiz = :quiz_id AND domanda = :domanda_numero ORDER BY numero ASC";
                $stmt_answers = $pdo->prepare($sql_answers);
                $stmt_answers->execute([
                    'quiz_id' => $quiz_id,
                    'domanda_numero' => $question_item['numero']
                ]);
                $question_item['answers'] = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);
                $questions[] = $question_item;
            }
        }

        $today = date('Y-m-d');
        $user_logged_in = isset($_SESSION['user']);
        $can_participate = $user_logged_in &&
            ($quiz['dataInizio'] <= $today && $quiz['dataFine'] >= $today) &&
            !empty($questions_data);

        if ($quiz['dataInizio'] > $today) {
            $quiz_status = 'pending';
            $status_text = 'In attesa';
            $status_icon = 'clock';
        } elseif ($quiz['dataFine'] < $today) {
            $quiz_status = 'expired';
            $status_text = 'Scaduto';
            $status_icon = 'calendar-times';
        } else {
            $quiz_status = 'available';
            $status_text = 'Disponibile';
            $status_icon = 'check-circle';
        }
    } else {
        // Se il quiz non esiste, $quiz rimarrà null e l'HTML sottostante gestirà questo caso
        // Potresti voler impostare un messaggio di errore in sessione e reindirizzare qui
        // $_SESSION['error_message'] = "Quiz non trovato.";
        // header('Location: index.php');
        // exit;
    }

} catch (PDOException $e) {
    error_log("Errore DB in quiz_view.php: " . $e->getMessage());
    die("Si è verificato un errore nel caricamento dei dati del quiz. Si prega di riprovare più tardi o contattare l'assistenza.");
}
?>

<div class="main-content">
    <div class="content">
        <?php if ($quiz): ?>
            <div class="quiz-detail-header">
                <h1><?php echo htmlspecialchars($quiz['titolo']); ?></h1>
                <span class="status-badge <?php echo htmlspecialchars($quiz_status); ?>"> <i
                        class="fas fa-<?php echo htmlspecialchars($status_icon); ?>"></i>
                    <?php echo htmlspecialchars($status_text); ?>
                </span>
            </div>

            <div class="quiz-detail-card">
                <div class="quiz-info-grid">
                    <div class="quiz-info-item">
                        <i class="fas fa-user"></i>
                        <div>
                            <span class="info-label">Creato da</span>
                            <span
                                class="info-value"><?php echo htmlspecialchars($quiz['nome'] . ' ' . $quiz['cognome']); ?></span>
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
                    <?php if (!empty($quiz['descrizione'])): ?>
                        <div class="quiz-info-item quiz-description">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <span class="info-label">Descrizione</span>
                                <span class="info-value"><?php echo nl2br(htmlspecialchars($quiz['descrizione'])); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                if (isset($can_participate) && $can_participate):
                    ?>
                    <div class="quiz-action-container">
                        <a href="quiz_participate.php?id=<?php echo $quiz_id; ?>" class="btn btn-participate">
                            <i class="fas fa-play-circle"></i> Partecipa al Quiz
                        </a>
                    </div>
                <?php elseif (isset($user_logged_in) && !$user_logged_in && $quiz_status === 'available' && !empty($questions_data)): ?>
                    <div class="quiz-action-container login-prompt">
                        <p>
                            <i class="fas fa-lock"></i>
                            Effettua il <a href="auth_login.php?redirect=quiz_view.php?id=<?php echo $quiz_id; ?>"
                                class="text-link">login</a> per partecipare a questo quiz.
                        </p>
                    </div>
                <?php elseif ($quiz_status === 'pending'): ?>
                    <div class="quiz-action-container pending-notice">
                        <p>
                            <i class="fas fa-clock"></i>
                            Questo quiz non è ancora iniziato. Sarà disponibile dal
                            <?php echo date('d/m/Y', strtotime($quiz['dataInizio'])); ?>.
                        </p>
                    </div>
                <?php elseif ($quiz_status === 'expired'): ?>
                    <div class="quiz-action-container expired-notice">
                        <p>
                            <i class="fas fa-exclamation-circle"></i>
                            Questo quiz è scaduto il <?php echo date('d/m/Y', strtotime($quiz['dataFine'])); ?> e non è più
                            disponibile.
                        </p>
                    </div>
                <?php elseif (isset($user_logged_in) && $user_logged_in && $quiz_status === 'available' && empty($questions_data)): ?>
                    <div class="quiz-action-container no-questions-prompt">
                        <p>
                            <i class="fas fa-info-circle"></i>
                            Questo quiz è disponibile ma non contiene domande al momento. Non è possibile partecipare.
                        </p>
                    </div>
                <?php elseif (isset($user_logged_in) && !$user_logged_in && $quiz_status !== 'available' && $quiz_status !== ''): // Quiz non disponibile e utente non loggato ?>
                    <div class="quiz-action-container info-notice">
                        <p>
                            <i class="fas fa-info-circle"></i>
                            Questo quiz non è attualmente disponibile per la partecipazione.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                Dettagli del quiz non disponibili o quiz non trovato.
            </div>
            <p class="text-align-center padding-vertical-medium"><a href="index.php" class="btn">Torna alla Home</a></p>
        <?php endif; ?>

        <?php if ($quiz): // Mostra la sezione domande solo se il quiz esiste ?>
            <div class="questions-preview-section">
                <h2><i class="fas fa-list-ul"></i> Anteprima delle Domande</h2>
                <?php if (empty($questions)): ?>
                    <div class="no-questions-notice">
                        <i class="fas fa-info-circle"></i>
                        <p>Nessuna domanda disponibile per questo quiz al momento.</p>
                    </div>
                <?php else: ?>
                    <div class="questions-accordion">
                        <?php
                        $numeroDomandaVisualizzato = 1;
                        foreach ($questions as $index => $question): ?>
                            <div
                                class="question-card <?php echo ($index === 0 && count($questions) > 1) ? 'open' : (count($questions) === 1 ? 'open' : ''); ?>">
                                <div class="question-header" role="button" tabindex="0"
                                    aria-expanded="<?php echo ($index === 0 && count($questions) > 1) ? 'true' : (count($questions) === 1 ? 'true' : 'false'); ?>"
                                    aria-controls="question-content-<?php echo $index; ?>">
                                    <span class="question-number">Domanda <?php echo $numeroDomandaVisualizzato; ?></span>
                                    <div class="question-toggle"><i class="fas fa-chevron-down"></i></div>
                                </div>
                                <div class="question-content-wrapper" id="question-content-<?php echo $index; ?>" <?php echo !($index === 0 || count($questions) === 1) ? 'style="max-height: 0px;"' : ''; ?>>
                                    <div class="question-text">
                                        <?php echo nl2br(htmlspecialchars($question['testo'])); ?>
                                    </div>
                                    <?php if (!empty($question['answers'])): ?>
                                        <ul class="answer-options">
                                            <?php foreach ($question['answers'] as $answer): ?>
                                                <li class="answer-option">
                                                    <span class="option-marker"><?php echo chr(64 + $answer['numero']); ?>.</span>
                                                    <span class="option-text"><?php echo htmlspecialchars($answer['testo']); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p class="no-answers-notice">Nessuna opzione di risposta fornita per questa domanda.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                            $numeroDomandaVisualizzato++;
                        endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; // Fine blocco if ($quiz) per la sezione domande ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>