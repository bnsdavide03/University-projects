<?php

/**
 * Registrazione di un nuovo utente.
 * 
 * Questa pagina permette agli utenti di registrarsi al sistema.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Se l'utente è già loggato, reindirizza alla home.
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/database.php'; // Assicurati che $pdo sia disponibile
$pdo = getPDO();
$error = '';
$success = '';
$nomeUtente = ''; // Inizializza per il value nel form
$nome = '';       // Inizializza per il value nel form
$cognome = '';    // Inizializza per il value nel form
$eMail = '';      // Inizializza per il value nel form

// --- Gestione del form di registrazione ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeUtente = trim($_POST['nomeUtente'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $eMail = trim($_POST['eMail'] ?? '');

    if (empty($nomeUtente) || empty($nome) || empty($cognome) || empty($eMail)) {
        $error = 'Tutti i campi sono obbligatori';
    } elseif (!filter_var($eMail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email non valida';
    } else {
        try {
            // Verifica se il nome utente e/o l'email sono già registrati con una singola query.
            $stmt = $pdo->prepare("
                SELECT 
                    EXISTS(SELECT 1 FROM Utente WHERE nomeUtente = :nomeUtente) as username_exists,
                    EXISTS(SELECT 1 FROM Utente WHERE eMail = :eMail) as email_exists
            ");
            $stmt->bindParam(':nomeUtente', $nomeUtente);
            $stmt->bindParam(':eMail', $eMail);
            $stmt->execute();
            $existence = $stmt->fetch(PDO::FETCH_ASSOC);

            $usernameExists = (bool) $existence['username_exists'];
            $emailExists = (bool) $existence['email_exists'];

            $errorsFound = [];
            if ($usernameExists) {
                $errorsFound[] = 'Nome utente già registrato';
            }
            if ($emailExists) {
                $errorsFound[] = 'Email già registrata';
            }

            if (!empty($errorsFound)) {
                $error = implode('. ', $errorsFound) . '.';
            } else {
                // Inserimento del nuovo utente.
                $stmt = $pdo->prepare("INSERT INTO Utente (nomeUtente, nome, cognome, eMail) VALUES (:nomeUtente, :nome, :cognome, :eMail)");
                $stmt->bindParam(':nomeUtente', $nomeUtente);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':cognome', $cognome);
                $stmt->bindParam(':eMail', $eMail);

                if ($stmt->execute()) {
                    $success = 'Registrazione completata con successo! Ora puoi accedere.';
                    // Svuota i campi del form in caso di successo per evitare re-invio
                    $nomeUtente = $nome = $cognome = $eMail = '';
                } else {
                    $error = 'Errore durante la registrazione. Riprova.';
                }
            }
        } catch (PDOException $e) {
            // Log dell'errore $e->getMessage() per debug
            error_log('Errore di registrazione DB: ' . $e->getMessage());
            $error = 'Errore di connessione al database. Riprova più tardi.';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h1 class="page-main-title">Registrazione</h1>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <div class="mt-2">
                                <a href="auth_login.php" class="btn btn-primary">Vai al login</a>
                            </div>
                        </div>
                    <?php else: // Mostra il form solo se non c'è un messaggio di successo ?>
                        <form method="POST" action="">
                            <div class="form-group mb-3">
                                <label for="nomeUtente">Nome Utente</label>
                                <input type="text" class="form-control" id="nomeUtente" name="nomeUtente" required
                                    value="<?php echo htmlspecialchars($nomeUtente); ?>">
                            </div>
                            <div class="form-group mb-3">
                                <label for="nome">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required
                                    value="<?php echo htmlspecialchars($nome); ?>">
                            </div>
                            <div class="form-group mb-3">
                                <label for="cognome">Cognome</label>
                                <input type="text" class="form-control" id="cognome" name="cognome" required
                                    value="<?php echo htmlspecialchars($cognome); ?>">
                            </div>
                            <div class="form-group mb-3">
                                <label for="eMail">Email</label>
                                <input type="email" class="form-control" id="eMail" name="eMail" required
                                    value="<?php echo htmlspecialchars($eMail); ?>">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Registrati</button>
                            </div>
                        </form>

                        <div class="mt-3 text-center">
                            <p>Hai già un account? <a href="auth_login.php">Accedi</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>