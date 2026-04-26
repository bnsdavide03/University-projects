<?php
// Inizializzazione della sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se l'utente è già loggato, reindirizza alla home
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Include la configurazione del database
require_once 'config/database.php';

$error = '';

// Gestione del form di login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeUtente = trim($_POST['nomeUtente'] ?? '');

    // Validazione di base
    if (empty($nomeUtente)) {
        $error = 'Inserisci il nome utente';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT nomeUtente, nome, cognome, eMail FROM Utente WHERE nomeUtente = :nomeUtente");
            $stmt->bindParam(':nomeUtente', $nomeUtente);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Login riuscito - Semplice login basato sul nome utente come specificato nella traccia
                $_SESSION['user'] = [
                    'nomeUtente' => $user['nomeUtente'],
                    'nome' => $user['nome'],
                    'cognome' => $user['cognome'],
                    'eMail' => $user['eMail']
                ];

                header('Location: index.php');
                exit;
            } else {
                $error = 'Nome utente non trovato';
            }
        } catch (PDOException $e) {
            $error = 'Errore di connessione al database: ' . $e->getMessage();
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
                    <h3 class="text-center">Accesso</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group mb-3">
                            <label for="nomeUtente">Nome Utente</label>
                            <input type="text" class="form-control" id="nomeUtente" name="nomeUtente" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Accedi</button>
                        </div>
                    </form>

                    <div class="mt-3 text-center">
                        <p>Non hai un account? <a href="auth_register.php">Registrati</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>