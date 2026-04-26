<?php
/**
 * Pagina di creazione dei quiz.
 *
 * Questa pagina permette agli utenti di creare nuovi quiz.
 */

include 'includes/header.php'; // Assicurati che questo includa FontAwesome se usato

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Controllo se l'utente è loggato.
if (!isset($_SESSION['user'])) {
    header('Location: auth_login.php');
    exit;
}
?>

<div class="main-content">
    <div class="content">
        <h1 class="page-main-title">Crea un nuovo Quiz</h1>

        <!-- Placeholder per gli alert JavaScript -->
        <div id="alert-container-page" class="alert-placeholder"></div>

        <div id="quiz-details-section">
            <div class="custom-card box-shadow- हल्का margin-bottom-large">
                <div class="custom-card-header">
                    <h2 class="custom-card-title">Dettagli del Quiz</h2>
                </div>
                <div class="custom-card-body">
                    <form id="create-quiz-form">
                        <div class="form-field-group margin-bottom-medium">
                            <label for="titolo" class="form-label-styled">Titolo del Quiz</label>
                            <input type="text" name="titolo" id="titolo" class="form-input-styled">
                        </div>

                        <div class="form-row">
                            <div class="form-col-half">
                                <div class="form-field-group margin-bottom-medium">
                                    <label for="dataInizio" class="form-label-styled">Data di inizio</label>
                                    <input type="date" name="dataInizio" id="dataInizio" class="form-input-styled">
                                </div>
                            </div>
                            <div class="form-col-half">
                                <div class="form-field-group margin-bottom-medium">
                                    <label for="dataFine" class="form-label-styled">Data di fine</label>
                                    <input type="date" name="dataFine" id="dataFine" class="form-input-styled">
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-actions-container padding-top-medium">
                            <button type="submit" class="button-styled button-primary-styled">
                                <i class="fas fa-arrow-right icon-spacing-right"></i> Continua e Aggiungi Domande
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="questions-section" style="display: none;">
            <div class="custom-card box-shadow-light">
                <div class="custom-card-header">
                    <h2 class="custom-card-title">Aggiungi Domande al Quiz</h2>
                </div>
                <div class="custom-card-body">
                    <input type="hidden" id="quiz-id" value="">
                    <form id="questions-form">
                        <div id="questions-container">
                            <p id="no-questions-message"
                                class="message-placeholder-styled text-align-center padding-vertical-medium">
                                Nessuna domanda aggiunta. Clicca su "Aggiungi Domanda" per iniziare.
                            </p>
                        </div>
                        <div
                            class="main-actions-bar flex-container flex-gap-medium margin-top-large padding-top-medium border-top-light">
                            <button type="button" id="add-question" class="button-styled button-secondary-styled">
                                <i class="fas fa-plus icon-spacing-right"></i> Aggiungi Domanda
                            </button>
                            <button type="button" id="abort-quiz-creation" class="button-styled button-danger-styled">
                                <i class="fas fa-times icon-spacing-right"></i> Annulla Creazione
                            </button>
                            <button type="button" id="save-questions" class="button-styled button-success-styled">
                                <i class="fas fa-save icon-spacing-right"></i> Salva Quiz e Domande
                            </button>
                        </div>
                    </form>
                </div> <!-- fine custom-card-body -->
            </div> <!-- fine custom-card -->
        </div> <!-- fine questions-section -->

    </div>
</div>

<!-- Template per una singola domanda (stile custom) -->
<template id="question-template-create">
    <div class="question-block-styled margin-bottom-large" data-question="__Q_NUM__">
        <div
            class="question-header-styled flex-container flex-justify-between flex-align-center padding-medium margin-bottom-medium">
            <h3 class="question-title-styled">Domanda <span class="question-number">__Q_NUM__</span></h3>
            <button type="button" class="button-styled button-danger-outline-styled button-small-styled remove-question"
                data-question="__Q_NUM__" title="Rimuovi Domanda __Q_NUM__">
                <i class="fas fa-trash-alt icon-spacing-right-small"></i>Rimuovi
            </button>
        </div>
        <div class="question-body-styled padding-medium-horizontal">
            <div class="form-field-group margin-bottom-medium">
                <label for="question-__Q_NUM__" class="form-label-styled label-bold-styled">Testo della Domanda:</label>
                <textarea name="questions[__Q_NUM__][text]" id="question-__Q_NUM__" class="textarea-styled" rows="3"
                    required placeholder="Inserisci qui il testo della domanda..."></textarea>
            </div>
            <h4 class="answers-area-title-styled margin-top-medium margin-bottom-small">Risposte:</h4>
            <div class="answers-container-styled padding-left-small" id="answers-container-__Q_NUM__">
                <p class="message-inline-styled text-description-styled" id="no-answers-for-q-__Q_NUM__">Nessuna
                    risposta aggiunta per questa domanda.</p>
            </div>
            <button type="button"
                class="button-styled button-secondary-styled button-small-styled margin-top-medium add-answer"
                data-question="__Q_NUM__">
                <i class="fas fa-plus-circle icon-spacing-right-small"></i>Aggiungi Risposta
            </button>
        </div>
    </div>
</template>

<!-- Template per una singola risposta (stile custom) -->
<template id="answer-template-create">
    <div class="answer-block-styled padding-medium margin-bottom-medium box-shadow-extra-light" data-answer="__A_NUM__">
        <div class="answer-header-styled flex-container flex-justify-between flex-align-center margin-bottom-small">
            <label class="form-label-styled label-bold-styled label-accent-color-styled answer-label-dynamic">Risposta
                <span class="answer-number">__A_NUM__</span></label>
            <button type="button" class="button-styled button-danger-styled button-extra-small-styled remove-answer"
                title="Rimuovi Risposta">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="form-field-group margin-bottom-small">
            <label for="question-__Q_NUM__-answer-__A_NUM__" class="form-label-styled label-small-styled">Testo della
                Risposta:</label>
            <input type="text" name="questions[__Q_NUM__][answers][__A_NUM__][text]"
                id="question-__Q_NUM__-answer-__A_NUM__" class="form-input-styled input-small-styled" required
                placeholder="Testo della risposta">
        </div>
        <div class="form-field-group margin-bottom-small">
            <label class="form-label-styled label-small-styled display-block margin-bottom-extra-small">Tipo
                Risposta:</label>
            <div class="radio-option-group-styled">
                <div class="radio-option-styled">
                    <input class="radio-input-styled" type="radio" name="questions[__Q_NUM__][answers][__A_NUM__][type]"
                        id="question-__Q_NUM__-answer-__A_NUM__-type-correct" value="Corretta">
                    <label class="radio-label-styled"
                        for="question-__Q_NUM__-answer-__A_NUM__-type-correct">Corretta</label>
                </div>
                <div class="radio-option-styled">
                    <input class="radio-input-styled" type="radio" name="questions[__Q_NUM__][answers][__A_NUM__][type]"
                        id="question-__Q_NUM__-answer-__A_NUM__-type-wrong" value="Sbagliata" checked>
                    <label class="radio-label-styled"
                        for="question-__Q_NUM__-answer-__A_NUM__-type-wrong">Sbagliata</label>
                </div>
            </div>
        </div>
        <div class="form-field-group points-group" style="display:none;">
            <label for="question-__Q_NUM__-answer-__A_NUM__-points" class="form-label-styled label-small-styled">Punti
                (se corretta):</label>
            <input type="number" name="questions[__Q_NUM__][answers][__A_NUM__][points]"
                id="question-__Q_NUM__-answer-__A_NUM__-points"
                class="form-input-styled input-small-styled input-numerical-styled" value="1" min="0">
        </div>
    </div>
</template>

<!-- Modale di Conferma Annullamento Creazione Quiz (stili custom) -->
<div id="confirmAbortCreationModal" class="custom-modal" style="display:none;">
    <div class="custom-modal-content">
        <span class="custom-modal-close-button">×</span>
        <h2 class="custom-modal-title">Conferma Annullamento</h2>
        <p id="abortCreationModalMessage" class="custom-modal-text">Sei sicuro di voler annullare la creazione del quiz?
            Eventuali dati inseriti o il quiz parzialmente creato verranno persi.</p>
        <div class="custom-modal-actions">
            <button type="button" id="cancelAbortBtn" class="button-styled button-grey-styled">Indietro</button>
            <button type="button" id="confirmAbortActionBtn" class="button-styled button-danger-styled">Sì, Annulla
                Creazione</button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>

</html>