<?php

/**
 * Header comune dell'applicazione
 *
 * Questo file contiene l'intestazione HTML comune a tutte le pagine.
 * Include:
 * - Dichiarazione del doctype e tag meta
 * - Collegamenti ai fogli di stile CSS
 * - Collegamenti agli script JavaScript
 * - Logo e titolo dell'applicazione
 * - Gestione della sessione utente
 * - Inizio della struttura della pagina
 */

session_start();
require_once 'config/database.php';

// Determina la classe del body in base alla pagina corrente
$bodyClass = '';
$currentPage = basename($_SERVER['PHP_SELF']);

if ($currentPage == 'index.php') {
    $bodyClass = 'page-index';
} elseif ($currentPage == 'quiz_create.php') {
    $bodyClass = 'page-quiz-create';
} elseif ($currentPage == 'quiz_modify.php') {
    $bodyClass = 'page-quiz-modify';
}
// Aggiungi altre condizioni elseif per altre pagine se necessario
// ad es. per la pagina di partecipazione, visualizzazione quiz, ecc.

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Online - UniBG</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Potresti voler includere Font Awesome qui se non è già in style.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="<?php echo htmlspecialchars($bodyClass); ?>">
    <header>
        <div class="container header-content">
            <div class="logo">
                <a href="index.php" title="Homepage Quiz UniBG">
                    <img src="https://upload.wikimedia.org/wikipedia/it/b/b4/UNIBG_Logo_2018.svg"
                        alt="Logo Università degli Studi di Bergamo" id="header-logo">
                </a>
            </div>

            <div class="user-info">
                <?php if (isset($_SESSION['user'])): ?>
                    <span class="user-welcome-text">
                        Benvenuto, <?php echo htmlspecialchars($_SESSION['user']['nome'], ENT_QUOTES, 'UTF-8'); ?>!  
                    </span>
                    <a href="auth_logout.php" class="btn-header-auth btn-header-logout">Logout</a>
                <?php else: ?>
                    <a href="auth_login.php" class="btn-header-auth btn-header-login">Login</a>  
                    <a href="auth_register.php" class="btn-header-auth btn-header-register">Registrati</a>
                <?php endif; ?>
            </div>

        </div>
    </header>

    <?php include 'includes/nav.php'; // Assicurati che nav.php esista e sia corretto ?>

    <div class="container main-page-container"> <!-- Aggiunta classe per styling se necessario -->
        <!-- Spostato l'alert container principale qui, fuori dall'header, più vicino al contenuto -->
        <div id="alert-container-page" class="alert-container-fixed-top"></div>
        <!-- La vecchia #alert-container potrebbe essere rimossa o rinominata se #alert-container-page è il principale -->