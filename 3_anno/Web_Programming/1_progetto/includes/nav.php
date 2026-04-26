<?php

/**
 * Barra di navigazione dell'applicazione
 * 
 * Questo file contiene la barra di navigazione comune a tutte le pagine.
 * Include:
 * - Menu di navigazione principale
 * - Gestione dello stato di login/logout dell'utente
 * - Link alle sezioni principali dell'applicazione
 * - Adattamento responsive per dispositivi mobili
 * - Evidenziazione della pagina corrente
 */

$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav>
    <div class="container">
        <ul>
            <li><a href="index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a></li>
            <?php if (isset($_SESSION['user'])): ?>
                <li><a href="quiz_create.php" <?php echo $current_page == 'quiz_create.php' ? 'class="active"' : ''; ?>>Crea
                        Quiz</a></li>
                <li><a href="quiz_my.php" <?php echo $current_page == 'quiz_my.php' ? 'class="active"' : ''; ?>>I Miei
                        Quiz</a></li>
                <li><a href="quiz_participations.php" <?php echo $current_page == 'quiz_participations.php' ? 'class="active"' : ''; ?>>Le Mie Partecipazioni</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>