<?php

/**
 * API per la gestione dei quiz.
 *
 * Questo file gestisce le operazioni CRUD relative ai quiz.
 * Funzionalità implementate:
 * - Creazione di nuovi quiz;
 * - Modifica dei dettagli di un quiz esistente;
 * - Eliminazione di quiz;
 * - Recupero di tutti i quiz disponibili;
 * - Recupero dei dettagli di un quiz specifico;
 * - Filtraggio dei quiz per data, creatore o stato (aperto/chiuso);
 */

// --- Inizializzazione della sessione e configurazione ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php'; // Assicurati che il percorso sia corretto
header('Content-Type: application/json');

// --- Funzioni di utilità ---
function isAuthenticated()
{
    return isset($_SESSION['user']) && !empty($_SESSION['user']['nomeUtente']);
}

function isOwnerOfQuizActual($pdo, $quizCodice, $nomeUtente)
{
    $stmt = $pdo->prepare("SELECT codice FROM Quiz WHERE codice = :quizCodice AND creatore = :nomeUtente");
    $stmt->bindParam(':quizCodice', $quizCodice, PDO::PARAM_INT);
    $stmt->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

function handleError($message, $code = 500, $exceptionDetails = "")
{
    if (!empty($exceptionDetails)) {
        error_log("API Error: " . $message . " | Details: " . $exceptionDetails);
    } else {
        error_log("API Error: " . $message);
    }
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}


// --- Gestione delle richieste API ---
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            $quizId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($quizId === false) {
                handleError('ID Quiz non valido per il recupero.', 400);
            }
            try {
                $stmt = $pdo->prepare("
                    SELECT q.codice, q.titolo, q.dataInizio, q.dataFine, q.creatore, COUNT(DISTINCT p.utente) as numPartecipanti
                    FROM Quiz q
                    LEFT JOIN Partecipazione p ON q.codice = p.quiz
                    WHERE q.codice = :quizId
                    GROUP BY q.codice
                ");
                $stmt->bindParam(':quizId', $quizId, PDO::PARAM_INT);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
                    $stmtDomande = $pdo->prepare("SELECT numero, testo FROM Domanda WHERE quiz = :quizId ORDER BY numero ASC");
                    $stmtDomande->bindParam(':quizId', $quizId, PDO::PARAM_INT);
                    $stmtDomande->execute();
                    $domande = $stmtDomande->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($domande as $key => $domanda) {
                        $stmtRisposte = $pdo->prepare("SELECT numero, testo, tipo, punteggio FROM Risposta WHERE quiz = :quizId AND domanda = :numeroDomanda ORDER BY numero ASC");
                        $stmtRisposte->bindParam(':quizId', $quizId, PDO::PARAM_INT);
                        $stmtRisposte->bindParam(':numeroDomanda', $domanda['numero'], PDO::PARAM_INT);
                        $stmtRisposte->execute();
                        $domande[$key]['risposte'] = $stmtRisposte->fetchAll(PDO::FETCH_ASSOC);
                    }
                    $quiz['domande_complete'] = $domande;
                    echo json_encode(['status' => 'success', 'data' => $quiz]);
                } else {
                    handleError('Quiz non trovato', 404);
                }
            } catch (PDOException $e) {
                handleError('Errore del database durante il recupero del quiz: ' . $e->getMessage(), 500, $e->getTraceAsString());
            }

        } elseif (isset($_GET['delId'])) {
            if (!isAuthenticated()) {
                handleError('Non sei autenticato', 401);
            }
            $quizIdToDelete = filter_var($_GET['delId'], FILTER_VALIDATE_INT);
            if ($quizIdToDelete === false) {
                handleError('ID Quiz non valido per l\'eliminazione.', 400);
            }
            $nomeUtente = $_SESSION['user']['nomeUtente'];

            if (isOwnerOfQuizActual($pdo, $quizIdToDelete, $nomeUtente)) {
                try {
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("DELETE FROM Risposta WHERE quiz = :quizId");
                    $stmt->bindParam(':quizId', $quizIdToDelete, PDO::PARAM_INT);
                    $stmt->execute();

                    $stmt = $pdo->prepare("DELETE FROM Domanda WHERE quiz = :quizId");
                    $stmt->bindParam(':quizId', $quizIdToDelete, PDO::PARAM_INT);
                    $stmt->execute();

                    $stmt = $pdo->prepare("DELETE FROM Partecipazione WHERE quiz = :quizId");
                    $stmt->bindParam(':quizId', $quizIdToDelete, PDO::PARAM_INT);
                    $stmt->execute();

                    $stmt = $pdo->prepare("DELETE FROM Quiz WHERE codice = :quizId");
                    $stmt->bindParam(':quizId', $quizIdToDelete, PDO::PARAM_INT);
                    $stmt->execute();
                    $pdo->commit();
                    http_response_code(200); // OK
                    echo json_encode(['status' => 'success', 'message' => 'Quiz eliminato con successo.']);
                    exit;
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    handleError('Errore durante l\'eliminazione del quiz: ' . $e->getMessage(), 500, $e->getTraceAsString());
                }
            } else {
                handleError('Quiz non trovato o non autorizzato a eliminarlo', 403);
            }

        } else {
            try {
                $query = "
                    SELECT q.codice, q.titolo, q.dataInizio, q.dataFine, q.creatore, u.nome, u.cognome, COUNT(DISTINCT p.utente) as numPartecipanti
                    FROM Quiz q
                    JOIN Utente u ON q.creatore = u.nomeUtente
                    LEFT JOIN Partecipazione p ON q.codice = p.quiz
                    WHERE q.dataFine IS NULL OR q.dataFine >= CURRENT_DATE() 
                "; // Potresti voler un filtro per i quiz attivi o tutti
                if (isset($_GET['creatore'])) {
                    $query .= " AND q.creatore = :creatore";
                }
                // Aggiungi altri filtri se necessario (es. stato, range di date)
                $query .= " GROUP BY q.codice ORDER BY q.dataInizio DESC, q.titolo ASC";

                $stmt = $pdo->prepare($query);

                if (isset($_GET['creatore'])) {
                    $stmt->bindParam(':creatore', $_GET['creatore'], PDO::PARAM_STR);
                }
                $stmt->execute();
                $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['status' => 'success', 'data' => $quizzes]);
            } catch (PDOException $e) {
                handleError('Errore del database nel recupero lista quiz: ' . $e->getMessage(), 500, $e->getTraceAsString());
            }
        }
        break;

    case 'POST':
        if (!isAuthenticated()) {
            handleError('Non autenticato.', 401);
        }
        $currentUser = $_SESSION['user']['nomeUtente'];
        $data = $_POST; // Dati del form

        if (isset($_GET['action']) && $_GET['action'] === 'update') {
            if (!isset($data['quiz_id']) || !filter_var($data['quiz_id'], FILTER_VALIDATE_INT)) {
                handleError('ID quiz mancante o non valido per l\'aggiornamento.', 400);
            }
            $quizId = (int) $data['quiz_id'];

            if (!isOwnerOfQuizActual($pdo, $quizId, $currentUser)) {
                handleError('Non autorizzato a modificare questo quiz o quiz non trovato.', 403);
            }

            try {
                $pdo->beginTransaction();


                // --- PASSO 1: Aggiorna i dettagli del Quiz (titolo, date) ---
                $updateQuizDetailsSqlParts = [];
                $quizUpdateParams = [':quiz_id' => $quizId, ':current_user' => $currentUser];

                if (isset($data['titolo']) && trim($data['titolo']) !== '') {
                    $updateQuizDetailsSqlParts[] = "titolo = :titolo";
                    $quizUpdateParams[':titolo'] = trim($data['titolo']);
                }
                if (isset($data['dataInizio']) && !empty($data['dataInizio'])) {
                    $updateQuizDetailsSqlParts[] = "dataInizio = :dataInizio";
                    $quizUpdateParams[':dataInizio'] = $data['dataInizio'];
                }
                if (isset($data['dataFine']) && !empty($data['dataFine'])) {
                    $updateQuizDetailsSqlParts[] = "dataFine = :dataFine";
                    $quizUpdateParams[':dataFine'] = $data['dataFine'];
                }

                if (!empty($updateQuizDetailsSqlParts)) {
                    $sqlQuizUpdate = "UPDATE Quiz SET " . implode(', ', $updateQuizDetailsSqlParts) .
                        " WHERE codice = :quiz_id AND creatore = :current_user";
                    $stmtQuizUpdate = $pdo->prepare($sqlQuizUpdate);

                    error_log("SQL Update Quiz Details: " . $sqlQuizUpdate);
                    error_log("Params Update Quiz Details: " . print_r($quizUpdateParams, true));

                    if (!$stmtQuizUpdate->execute($quizUpdateParams)) {
                        $errorInfo = $stmtQuizUpdate->errorInfo();
                        throw new PDOException("Errore durante l'aggiornamento dei dettagli del quiz: " . ($errorInfo[2] ?? 'Dettagli non disponibili'));
                    }
                    error_log("API Update: Dettagli del Quiz ID $quizId aggiornati. Righe affette: " . $stmtQuizUpdate->rowCount());
                } else {
                    error_log("API Update: Nessun dettaglio base del quiz (titolo/date) da aggiornare per Quiz ID $quizId.");
                }


                // --- PASSO 2: Identifica e cancella le domande rimosse (e tutte le loro dipendenze) ---
                $dbExistingQuestionNumbers = [];
                // ... (codice per popolare $dbExistingQuestionNumbers) ...
                $stmtDbQ_Pre = $pdo->prepare("SELECT numero FROM Domanda WHERE quiz = :quizId");
                $stmtDbQ_Pre->execute([':quizId' => $quizId]);
                while ($row = $stmtDbQ_Pre->fetch(PDO::FETCH_ASSOC)) {
                    $dbExistingQuestionNumbers[] = (int) $row['numero'];
                }

                $formSentOriginalQuestionNumbers = [];
                if (isset($data['questions']) && is_array($data['questions'])) {
                    foreach ($data['questions'] as $idx => $qData) {
                        $q_text = trim($q_data['testo'] ?? '');
                        if (isset($qData['original_numero_domanda']) && $qData['original_numero_domanda'] !== '') {
                            $formSentOriginalQuestionNumbers[] = (int) $qData['original_numero_domanda'];
                        }
                    }
                }

                $questionsToDelete = array_diff($dbExistingQuestionNumbers, $formSentOriginalQuestionNumbers);

                if (!empty($questionsToDelete)) {
                    $placeholdersDQ = implode(',', array_fill(0, count($questionsToDelete), '?'));
                    $sqlSelectRisposteToDelete = "SELECT quiz, domanda, numero FROM Risposta WHERE quiz = ? AND domanda IN ($placeholdersDQ)";
                    $stmtSelectR = $pdo->prepare($sqlSelectRisposteToDelete);
                    $paramsSelectR = array_merge([$quizId], $questionsToDelete);
                    $stmtSelectR->execute($paramsSelectR);
                    $risposteDaCuiCancellareRUQ = $stmtSelectR->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($risposteDaCuiCancellareRUQ)) {
                        // Costruisci una condizione OR complessa per il DELETE da RispostaUtenteQuiz
                        $deleteRUQConditions = [];
                        $deleteRUQParams = [];
                        foreach ($risposteDaCuiCancellareRUQ as $r) {
                            $deleteRUQConditions[] = "(quiz = ? AND domanda = ? AND risposta = ?)";
                            $deleteRUQParams[] = $r['quiz'];
                            $deleteRUQParams[] = $r['domanda'];
                            $deleteRUQParams[] = $r['numero']; // 'numero' della Risposta è 'risposta' in RispostaUtenteQuiz
                        }
                        if (!empty($deleteRUQConditions)) {
                            $sqlDeleteRUQ = "DELETE FROM RispostaUtenteQuiz WHERE " . implode(" OR ", $deleteRUQConditions);
                            $stmtDelRUQ = $pdo->prepare($sqlDeleteRUQ);
                            $stmtDelRUQ->execute($deleteRUQParams);
                            error_log("API Update: RispostaUtenteQuiz cancellate per quiz $quizId e domande " . implode(', ', $questionsToDelete));
                        }
                    }

                    $stmtDelR = $pdo->prepare("DELETE FROM Risposta WHERE quiz = ? AND domanda IN ($placeholdersDQ)");
                    $stmtDelR->execute(array_merge([$quizId], $questionsToDelete));
                    error_log("API Update: Risposte cancellate per quiz $quizId e domande " . implode(', ', $questionsToDelete));

                    // INFINE: Cancella le Domande
                    $stmtDelQ = $pdo->prepare("DELETE FROM Domanda WHERE quiz = ? AND numero IN ($placeholdersDQ)");
                    $stmtDelQ->execute(array_merge([$quizId], $questionsToDelete));
                    error_log("API Update: Domande cancellate (quiz $quizId): " . implode(', ', $questionsToDelete));
                }

                // --- PASSO 3: Itera sulle domande inviate per aggiornarle/inserirle ---
                if (isset($data['questions']) && is_array($data['questions'])) {
                    $newQuestionOrder = 1; // Per assegnare un nuovo 'numero' progressivo alle domande se necessario

                    foreach ($data['questions'] as $q_idx => $q_data) { // $q_idx è l'indice 0-based dall'array inviato
                        $q_text = trim($q_data['text']);
                        if (empty($q_text)) {
                            $questionIdentifier = "all'indice form $q_idx";
                            if (isset($q_data['original_numero_domanda']) && !empty($q_data['original_numero_domanda'])) {
                                $questionIdentifier = "esistente con numero DB " . $q_data['original_numero_domanda'];
                            }
                            // Per debug, aggiungiamo l'intero $q_data al messaggio se è un array
                            $debug_q_data_info = "";
                            if (is_array($q_data)) {
                                $debug_q_data_info = " | Dati ricevuti per questo indice: " . json_encode($q_data);
                            } elseif (is_null($q_data)) {
                                $debug_q_data_info = " | Dati ricevuti per questo indice: NULL";
                            } else {
                                $debug_q_data_info = " | Dati ricevuti per questo indice: Non un array - " . gettype($q_data);
                            }

                            throw new Exception("Il testo per la domanda $questionIdentifier non può essere vuoto." . $debug_q_data_info);
                        }

                        // Determina se è una domanda esistente o nuova
                        $isNewQuestion = !isset($q_data['original_numero_domanda']) || $q_data['original_numero_domanda'] === '' || (int) $q_data['original_numero_domanda'] <= 0;

                        $currentQuestionDbNumber = null;

                        if ($isNewQuestion) {
                            // NUOVA DOMANDA: Inseriscila
                            // Ottieni il prossimo numero disponibile per la domanda IN QUESTO QUIZ
                            $stmtNextQNum = $pdo->prepare("SELECT COALESCE(MAX(numero), 0) + 1 as next_num FROM Domanda WHERE quiz = :quizId");
                            $stmtNextQNum->execute([':quizId' => $quizId]);
                            $currentQuestionDbNumber = $stmtNextQNum->fetchColumn();
                            if (!$currentQuestionDbNumber)
                                $currentQuestionDbNumber = 1; // Se è la prima domanda del quiz

                            $stmtInsertQ = $pdo->prepare("INSERT INTO Domanda (quiz, numero, testo) VALUES (:quizId, :numero, :testo)");
                            $stmtInsertQ->execute([
                                ':quizId' => $quizId,
                                ':numero' => $currentQuestionDbNumber,
                                ':testo' => $q_text
                            ]);
                            error_log("API Update: Nuova domanda inserita (quiz $quizId, numero $currentQuestionDbNumber): " . $q_text);
                        } else {
                            // DOMANDA ESISTENTE: Aggiornala
                            $currentQuestionDbNumber = (int) $q_data['original_numero_domanda'];
                            // Il numero visualizzato (posizione nell'array del form) potrebbe essere diverso dal numero nel DB.
                            // Qui aggiorniamo il testo. Il 'numero' della domanda (PK composita con quiz) di solito non cambia,
                            // a meno che tu non stia implementando un riordinamento complesso.
                            // Per semplicità, assumiamo che il 'numero' della domanda originale non cambi,
                            // ma il suo testo sì. $newQuestionOrder potrebbe essere usato se si implementa il riordino.
                            $stmtUpdateQ = $pdo->prepare("UPDATE Domanda SET testo = :testo WHERE quiz = :quizId AND numero = :original_q_num");
                            $stmtUpdateQ->execute([
                                ':testo' => $q_text,
                                ':quizId' => $quizId,
                                ':original_q_num' => $currentQuestionDbNumber
                            ]);
                            error_log("API Update: Domanda esistente aggiornata (quiz $quizId, numero $currentQuestionDbNumber): " . $q_text);

                            // Per le domande esistenti, potremmo dover cancellare le risposte che non sono più presenti nel form
                            $dbExistingAnswerNumbers = [];
                            $stmtDbA_Pre = $pdo->prepare("SELECT numero FROM Risposta WHERE quiz = :quizId AND domanda = :domandaNum");
                            $stmtDbA_Pre->execute([':quizId' => $quizId, ':domandaNum' => $currentQuestionDbNumber]);
                            while ($rowA = $stmtDbA_Pre->fetch(PDO::FETCH_ASSOC)) {
                                $dbExistingAnswerNumbers[] = (int) $rowA['numero'];
                            }

                            $formSentOriginalAnswerNumbers = [];
                            if (isset($q_data['answers']) && is_array($q_data['answers'])) {
                                foreach ($q_data['answers'] as $a_data_check) {
                                    if (isset($a_data_check['original_numero_risposta']) && $a_data_check['original_numero_risposta'] !== '') {
                                        $formSentOriginalAnswerNumbers[] = (int) $a_data_check['original_numero_risposta'];
                                    }
                                }
                            }
                            $answersToDelete = array_diff($dbExistingAnswerNumbers, $formSentOriginalAnswerNumbers);

                            if (!empty($answersToDelete)) {
                                $placeholdersDA = implode(',', array_fill(0, count($answersToDelete), '?'));
                                // Prima cancella da RispostaUtenteQuiz
                                $stmtDelRUQ_A = $pdo->prepare("DELETE FROM RispostaUtenteQuiz WHERE quiz = ? AND domanda = ? AND risposta IN ($placeholdersDA)");
                                $paramsDelRUQ_A = array_merge([$quizId, $currentQuestionDbNumber], $answersToDelete);
                                $stmtDelRUQ_A->execute($paramsDelRUQ_A);
                                error_log("API Update: RispostaUtenteQuiz cancellate per risposte eliminate della domanda $currentQuestionDbNumber, quiz $quizId");

                                // Poi cancella le Risposte
                                $stmtDelA = $pdo->prepare("DELETE FROM Risposta WHERE quiz = ? AND domanda = ? AND numero IN ($placeholdersDA)");
                                $paramsDelA = array_merge([$quizId, $currentQuestionDbNumber], $answersToDelete);
                                $stmtDelA->execute($paramsDelA);
                                error_log("API Update: Risposte eliminate (quiz $quizId, domanda $currentQuestionDbNumber): " . implode(', ', $answersToDelete));
                            }
                        }

                        // Ora gestisci le RISPOSTE per questa domanda (nuova o esistente)
                        if (isset($q_data['answers']) && is_array($q_data['answers'])) {
                            $newAnswerOrder = 1; // Per assegnare un nuovo 'numero' progressivo alle risposte se necessario

                            foreach ($q_data['answers'] as $a_idx => $a_data) {
                                $a_text = trim($a_data['text'] ?? '');

                                // Leggi 'type' (inviato dal frontend come 'type')
                                if (!isset($a_data['type']) || !in_array($a_data['type'], ['Corretta', 'Sbagliata'])) {
                                    $questionIdentifier = "per domanda DB {$currentQuestionDbNumber}, risposta indice form {$a_idx}";
                                    throw new Exception("Tipo di risposta non valido o mancante $questionIdentifier. Ricevuto: '" . htmlspecialchars($a_data['type'] ?? 'NULL') . "'.");
                                }
                                $a_type = $a_data['type'];

                                $a_points = 0;
                                if ($a_type === 'Corretta') {
                                    // Leggi 'points' (inviato dal frontend come 'points')
                                    if (isset($a_data['points']) && is_numeric($a_data['points']) && (int) $a_data['points'] >= 0) {
                                        $a_points = (int) $a_data['points'];
                                    } else {
                                        $a_points = 1;
                                    }
                                }
                                if (empty($a_text)) {
                                    error_log("API Update: Risposta con indice $a_idx per domanda $currentQuestionDbNumber saltata perché il testo è vuoto per quiz $quizId.");
                                    continue;
                                }

                                $isNewAnswer = !isset($a_data['original_numero_risposta']) || $a_data['original_numero_risposta'] === '' || (int) $a_data['original_numero_risposta'] <= 0;

                                if ($isNewAnswer) {
                                    // NUOVA RISPOSTA: Inseriscila
                                    // Ottieni il prossimo numero disponibile per la risposta IN QUESTA DOMANDA
                                    $stmtNextANum = $pdo->prepare("SELECT COALESCE(MAX(numero), 0) + 1 as next_num FROM Risposta WHERE quiz = :quizId AND domanda = :domandaNum");
                                    $stmtNextANum->execute([':quizId' => $quizId, ':domandaNum' => $currentQuestionDbNumber]);
                                    $currentAnswerDbNumber = $stmtNextANum->fetchColumn();
                                    if (!$currentAnswerDbNumber)
                                        $currentAnswerDbNumber = 1;

                                    $stmtInsertA = $pdo->prepare("INSERT INTO Risposta (quiz, domanda, numero, testo, tipo, punteggio) VALUES (:quizId, :domanda_numero, :numero_risposta, :testo, :tipo, :punteggio)");
                                    $stmtInsertA->execute([
                                        ':quizId' => $quizId,
                                        ':domanda_numero' => $currentQuestionDbNumber,
                                        ':numero_risposta' => $currentAnswerDbNumber,
                                        ':testo' => $a_text,
                                        ':tipo' => $a_type,
                                        ':punteggio' => $a_points
                                    ]);
                                    error_log("API Update: Nuova risposta inserita (quiz $quizId, domanda $currentQuestionDbNumber, numero $currentAnswerDbNumber): " . $a_text);
                                } else {
                                    // RISPOSTA ESISTENTE: Aggiornala
                                    $original_a_num = (int) $a_data['original_numero_risposta'];
                                    // Anche qui, il 'numero' della risposta (PK composita) di solito non cambia.
                                    // $newAnswerOrder potrebbe essere usato per il riordino.
                                    $stmtUpdateA = $pdo->prepare("UPDATE Risposta SET testo = :testo, tipo = :tipo, punteggio = :punteggio WHERE quiz = :quizId AND domanda = :domanda_numero AND numero = :original_a_num");
                                    $stmtUpdateA->execute([
                                        ':testo' => $a_text,
                                        ':tipo' => $a_type,
                                        ':punteggio' => $a_points,
                                        ':quizId' => $quizId,
                                        ':domanda_numero' => $currentQuestionDbNumber,
                                        ':original_a_num' => $original_a_num
                                    ]);
                                    error_log("API Update: Risposta esistente aggiornata (quiz $quizId, domanda $currentQuestionDbNumber, numero $original_a_num): " . $a_text);
                                }
                                $newAnswerOrder++;
                            }
                        }
                        $newQuestionOrder++;
                    }
                }

                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'Quiz aggiornato con successo.']);

            } catch (PDOException $e) {
                if ($pdo->inTransaction())
                    $pdo->rollBack();
                handleError('Errore del database durante l\'aggiornamento: ' . $e->getMessage(), 500, $e->getTraceAsString() . " | Dati: " . print_r($data, true));
            } catch (Exception $e) {
                if ($pdo->inTransaction())
                    $pdo->rollBack();
                // L'eccezione ora contiene $debug_q_data_info
                handleError($e->getMessage(), 400, "Dati POST completi: " . print_r($data, true));
            }

        } else { // Altrimenti, si tratta di una creazione di un nuovo quiz (senza domande/risposte qui)
            // Questa logica gestisce solo la creazione dell'entità Quiz principale.

            if (!isset($data['titolo']) || !isset($data['dataInizio']) || !isset($data['dataFine'])) {
                handleError('Dati incompleti per la creazione del quiz (titolo, dataInizio, dataFine richiesti).', 400);
            }
            $titolo = trim($data['titolo']);
            $dataInizio = $data['dataInizio'];
            $dataFine = $data['dataFine'];

            if (empty($titolo)) {
                handleError('Il titolo del quiz non può essere vuoto.', 400);
            }
            // Potresti aggiungere ulteriori validazioni per le date qui.

            try {

                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM Quiz WHERE titolo = :titolo");
                $stmtCheck->bindParam(':titolo', $titolo, PDO::PARAM_STR);
                $stmtCheck->execute();
                $count = $stmtCheck->fetchColumn(); // Ottiene il numero di quiz esistenti con stesso titolo/creatore

                // Se count è maggiore di 0, il quiz esiste già
                if ($count > 0) {
                    // Trovato duplicato! Chiama handleError che invia la risposta e termina lo script.
                    handleError('Esiste già un quiz con questo titolo. Scegli un titolo diverso.', 409); // 409 Conflict
                    // Nota: handleError contiene exit; quindi l'esecuzione si ferma qui se duplicato.
                }

                // Se non esiste, procedi con l'inserimento
                $stmt = $pdo->prepare("INSERT INTO Quiz (titolo, dataInizio, dataFine, creatore) VALUES (:titolo, :dataInizio, :dataFine, :creatore)");
                $stmt->execute([
                    ':titolo' => $titolo,
                    ':dataInizio' => $dataInizio,
                    ':dataFine' => $dataFine,
                    ':creatore' => $currentUser
                ]);
                $newQuizId = $pdo->lastInsertId();
                http_response_code(201); // Created
                echo json_encode(['status' => 'success', 'message' => 'Quiz creato con successo.', 'idQuiz' => $newQuizId]);
            } catch (PDOException $e) {
                handleError('Errore database durante la creazione del quiz: ' . $e->getMessage(), 500, $e->getTraceAsString());
            }
        }
        break;

    case 'PUT':
        // Generalmente, per l'aggiornamento completo di una risorsa.
        // Il tuo approccio con POST e action=update è comune per i form HTML.
        handleError('Metodo PUT non supportato per questa risorsa. Usare POST con action=update per aggiornamenti.', 405);
        break;

    default:
        handleError('Metodo non consentito.', 405);
}

?>