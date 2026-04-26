// Funzioni helper globali
function checkDateRange(startDate, endDate, allowPastStartDate = false) { // Modificata per includere allowPastStartDate
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Normalizza a mezzanotte per confronto date

    if (!allowPastStartDate && startDate < today) {
        return 'La data di inizio non può essere precedente a oggi.';
    }
    if (endDate < startDate) {
        return 'La data di fine non può essere precedente alla data di inizio.';
    }
    return null;
}

function showAlert(message, type = 'info', containerSelector = '#alert-container-page') {
    const alertId = 'custom-alert-' + Date.now(); // L'ID può rimanere custom per unicità

    // HTML generato con classi "alert" e "alert-close-btn" (come sembra essere la tua intenzione più recente)
    const alertHtml = `
        <div id="${alertId}" class="alert alert-${type}" role="alert">
            <span class="alert-message">${message}</span>
            <button type="button" class="alert-close-btn" aria-label="Close">×</button>
        </div>`;

    let $alertContainer = $(containerSelector);
    if (!$alertContainer.length) {
        $('body').prepend('<div id="alert-container-fallback" class="custom-alert-fallback-container" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; width: auto; max-width: 90%;"></div>');
        $alertContainer = $('#alert-container-fallback');
    }

    // MODIFICA 1: Usa il selettore corretto per rimuovere gli alert precedenti
    $alertContainer.find('.alert').remove(); // Prima era: $alertContainer.find('.custom-alert').remove();

    $alertContainer.append(alertHtml);
    const $currentAlert = $('#' + alertId); // Seleziona il nuovo alert per ID

    // MODIFICA 2: Usa il selettore corretto per il pulsante di chiusura
    $currentAlert.find('.alert-close-btn').on('click', function () { // Prima era: $currentAlert.find('.custom-alert-close').on('click', ...);
        $currentAlert.fadeOut(300, function () {
            $(this).remove();
        });
    });

    if (type !== 'danger' && type !== 'error' && type !== 'warning') {
        setTimeout(function () {
            $currentAlert.fadeOut(500, function () { $(this).remove(); });
        }, 5000);
    }
}

function showEditAlerts(message, type) { // Usata in quiz_edit.php
    showAlert(message, type, '#alert-container-page-modify');
}

// Funzione per rinumerare domande e risposte per quiz_create.php
function renumberQuestions() { // Questa è per quiz_create.php
    const $questionBlocks = $('#questions-container .question-block-styled');
    $('#no-questions-message').toggle($questionBlocks.length === 0);

    $questionBlocks.each(function (index) {
        const qNum = index + 1; // Per quiz_create, gli indici partono da 1 nei name
        const $currentQuestionBlock = $(this);
        $currentQuestionBlock.attr('data-question', qNum); // Usiamo data-question
        $currentQuestionBlock.find('.question-number').text(qNum);

        // Aggiorna label for e id/name del textarea domanda
        $currentQuestionBlock.find('label.form-label-styled[for^="question-"]').first().attr('for', `question-${qNum}`);
        $currentQuestionBlock.find('textarea.textarea-styled[id^="question-"]').attr({
            'id': `question-${qNum}`,
            'name': `questions[${qNum}][text]` // questions[1][text], questions[2][text]
        });

        const $answersContainer = $currentQuestionBlock.find('.answers-container-styled');
        $answersContainer.attr('id', `answers-container-${qNum}`); // O answers-container-q${qNum}
        $answersContainer.find('.message-inline-styled[id^="no-answers-for-q-"]').attr('id', `no-answers-for-q-${qNum}`);

        $currentQuestionBlock.find('button.add-answer').data('question', qNum); // data-question per il bottone
        $currentQuestionBlock.find('button.remove-question').data('question', qNum).attr('title', `Rimuovi Domanda ${qNum}`);

        $answersContainer.find('.answer-block-styled').each(function (aIndex) {
            const aNum = aIndex + 1; // Indici risposte partono da 1
            const $currentAnswerBlock = $(this);
            $currentAnswerBlock.attr('data-answer', aNum); // data-answer
            $currentAnswerBlock.find('.answer-number').text(aNum);

            // Testo risposta
            $currentAnswerBlock.find('label.label-small-styled[for^="question-"][for*="-answer-"]').attr('for', `question-${qNum}-answer-${aNum}`);
            $currentAnswerBlock.find('input[type="text"].input-small-styled[id^="question-"]').attr({
                'id': `question-${qNum}-answer-${aNum}`,
                'name': `questions[${qNum}][answers][${aNum}][text]`
            });

            // Radio buttons per tipo
            $currentAnswerBlock.find('input.radio-input-styled[name$="[type]"]').each(function () {
                const radioIdBase = `question-${qNum}-answer-${aNum}-type-`;
                const radioValue = $(this).val(); // "Corretta" o "Sbagliata"
                const typeSuffix = radioValue === 'Corretta' ? 'correct' : 'wrong';
                $(this).attr({
                    'name': `questions[${qNum}][answers][${aNum}][type]`,
                    'id': radioIdBase + typeSuffix
                });
                $(this).next('label.radio-label-styled').attr('for', radioIdBase + typeSuffix);
            });

            // Punteggio
            const $pointsGroup = $currentAnswerBlock.find('.points-group');
            $pointsGroup.find('label.label-small-styled[for$="-points"]').attr('for', `question-${qNum}-answer-${aNum}-points`);
            $pointsGroup.find('input[type="number"].input-numerical-styled[name$="[points]"]').attr({
                'id': `question-${qNum}-answer-${aNum}-points`,
                'name': `questions[${qNum}][answers][${aNum}][points]`
            });
        });
        // Gestione messaggio "nessuna risposta"
        const noAnswersMsg = document.getElementById(`no-answers-for-q-${qNum}`); // Usa qNum qui
        if (noAnswersMsg) {
            noAnswersMsg.style.display = $answersContainer.find('.answer-block-styled').length === 0 ? 'block' : 'none';
        }
    });
}

function addAnswerToQuestion(questionBlockElement, questionNum, answerCount) { // Per quiz_create.php
    const answerTemplate = document.getElementById('answer-template-create').innerHTML;
    let answerHtml = answerTemplate.replace(/__Q_NUM__/g, questionNum) // Sostituisce __Q_NUM__
        .replace(/__A_NUM__/g, answerCount); // Sostituisce __A_NUM__
    $(questionBlockElement).find('.answers-container-styled').append(answerHtml);
    $(questionBlockElement).find(`#no-answers-for-q-${questionNum}`).hide();
}


function toggleQuestion(headerElement) {
    const questionCard = headerElement.closest('.question-card');
    if (!questionCard) return;

    const contentWrapper = questionCard.querySelector('.question-content-wrapper');
    if (!contentWrapper) return;

    const wasOpen = questionCard.classList.contains('open');

    if (wasOpen) { // Sta per chiudere
        // Prima imposta l'altezza esplicita se era 'none'
        if (contentWrapper.style.maxHeight === 'none' || contentWrapper.style.maxHeight === '') {
            contentWrapper.style.maxHeight = contentWrapper.scrollHeight + "px";
        }
        // Forza un reflow per assicurare che l'altezza esplicita sia applicata prima della transizione a 0
        contentWrapper.offsetHeight;

        requestAnimationFrame(() => { // Usa requestAnimationFrame per la transizione a 0
            contentWrapper.style.maxHeight = "0px";
            questionCard.classList.remove('open');
            headerElement.setAttribute('aria-expanded', 'false');
        });
    } else { // Sta per aprire
        questionCard.classList.add('open');
        headerElement.setAttribute('aria-expanded', 'true');
        contentWrapper.style.maxHeight = contentWrapper.scrollHeight + "px";

        // Rimuovi l'altezza esplicita dopo la transizione per permettere contenuto dinamico
        const handleTransitionEnd = () => {
            if (questionCard.classList.contains('open')) { // Controlla se è ancora aperto
                contentWrapper.style.maxHeight = 'none';
            }
            contentWrapper.removeEventListener('transitionend', handleTransitionEnd);
        };
        contentWrapper.addEventListener('transitionend', handleTransitionEnd);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Accordion per quiz_view.php e altre pagine che usano .question-card
    document.querySelectorAll('.question-header').forEach(header => {
        header.addEventListener('click', function () { toggleQuestion(this); });
        header.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleQuestion(this);
            }
        });
    });

    // Inizializzazione delle card già aperte (es. se la classe 'open' è impostata server-side)
    document.querySelectorAll('.question-card.open').forEach(card => {
        const contentWrapper = card.querySelector('.question-content-wrapper');
        const header = card.querySelector('.question-header');
        if (header) header.setAttribute('aria-expanded', 'true');
        if (contentWrapper) {
            // Imposta maxHeight a scrollHeight per l'apertura iniziale,
            // poi 'none' dopo un breve timeout per permettere alla transizione di finire
            // e per adattarsi a contenuti che potrebbero cambiare dimensione.
            contentWrapper.style.maxHeight = contentWrapper.scrollHeight + "px";
            setTimeout(() => {
                if (card.classList.contains('open')) contentWrapper.style.maxHeight = 'none';
            }, 360); // Durata leggermente superiore alla transizione CSS (es. 0.35s)
        }
    });
});

// Codice jQuery principale
$(document).ready(function () {

    // --- Login e Registrazione ---
    $('#login-form').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this); // Cache form
        $.ajax({
            type: 'POST',
            url: 'api/users.php?action=login',
            data: $form.serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    // showAlert per login di solito non serve qui, si reindirizza
                    // Se vuoi mostrare un messaggio sulla pagina successiva, usa sessionStorage
                    sessionStorage.setItem('loginMessage', response.message || 'Login effettuato con successo!');
                    sessionStorage.setItem('loginMessageType', 'success');
                    window.location.href = response.redirect_url || 'index.php'; // Usa redirect_url dall'API se fornito
                } else {
                    showAlert(response.message || 'Credenziali non valide.', 'danger', '#alert-container-login'); // Specifica container
                }
            },
            error: () => showAlert('Errore durante la comunicazione con il server.', 'danger', '#alert-container-login')
        });
    });

    $('#register-form').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this); // Cache form
        $.ajax({
            type: 'POST',
            url: 'api/users.php?action=register',
            data: $form.serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    showAlert(response.message || 'Registrazione completata con successo! Effettua il login.', 'success', '#alert-container-register');
                    setTimeout(() => window.location.href = 'auth_login.php', 2000); // Reindirizza dopo aver mostrato il messaggio
                } else {
                    showAlert(response.message || 'Errore durante la registrazione.', 'danger', '#alert-container-register');
                }
            },
            error: () => showAlert('Errore durante la comunicazione con il server.', 'danger', '#alert-container-register')
        });
    });

    // --- Creazione Quiz (Pagina: quiz_create.php) ---
    if ($('#create-quiz-form').length) {

        $('#create-quiz-form').on('submit', function (e) {
            e.preventDefault();

            const titolo = $('#titolo').val().trim();
            const startDateVal = $('#dataInizio').val();
            const endDateVal = $('#dataFine').val();

            if (titolo === '') {
                showAlert('Il Titolo del Quiz è obbligatorio.', 'warning', '#alert-container-page');
                $('#titolo').focus(); // Opzionale: porta il focus sul campo vuoto
                return; // Interrompe l'esecuzione della funzione
            }
            if (!startDateVal) { // Controlla se la stringa è vuota
                showAlert('La Data di inizio è obbligatoria.', 'warning', '#alert-container-page');
                $('#dataInizio').focus();
                return;
            }
            if (!endDateVal) { // Controlla se la stringa è vuota
                showAlert('La Data di fine è obbligatoria.', 'warning', '#alert-container-page');
                $('#dataFine').focus();
                return;
            }

            const startDate = new Date(startDateVal);
            const endDate = new Date(endDateVal);

            const errorMessage = checkDateRange(startDate, endDate, false); // false: non permettere data inizio passata
            if (errorMessage) {
                showAlert(errorMessage, 'danger', '#alert-container-page'); // Usa il container corretto
                return;
            }
            $.ajax({
                type: 'POST', url: 'api/quiz.php?action=create', data: $(this).serialize(), dataType: 'json',
                success: function (response) {
                    if (response.status === 'success' && response.idQuiz) {
                        showAlert('Quiz creato con successo! Ora aggiungi le domande.', 'success', '#alert-container-page');
                        $('#quiz-id').val(response.idQuiz);
                        $('#quiz-details-section').slideUp();
                        $('#questions-section').slideDown();
                        // Aggiungi automaticamente la prima domanda se non ce ne sono
                        if ($('#questions-container .question-block-styled').length === 0) {
                            $('#add-question').click(); // Simula click su "Aggiungi Domanda"
                        } else {
                            renumberQuestions();
                        }
                    } else { showAlert(response.message || 'Errore nella creazione del quiz.', 'danger', '#alert-container-page'); }
                },
                error: (xhr) => showAlert('Errore creazione quiz: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger', '#alert-container-page')
            });
        });

        $('#add-question').click(function () {
            $('#no-questions-message').hide();
            const questionTemplate = document.getElementById('question-template-create').innerHTML;
            const questionNumberForTemplate = $('#questions-container .question-block-styled').length + 1;

            const questionHtml = questionTemplate.replace(/__Q_NUM__/g, questionNumberForTemplate); // Per i placeholder nel template
            $('#questions-container').append(questionHtml);

            const $newlyAddedQuestionBlock = $('#questions-container .question-block-styled:last-child');
            if ($newlyAddedQuestionBlock.length) {
                // Aggiungi automaticamente la prima risposta alla nuova domanda
                const actualQuestionNumForData = $newlyAddedQuestionBlock.data('question') || questionNumberForTemplate; // Usa data('question') se già settato da renumber
                addAnswerToQuestion($newlyAddedQuestionBlock, actualQuestionNumForData, 1);
            }
            renumberQuestions();
            $newlyAddedQuestionBlock.find('textarea.textarea-styled').first().focus(); // Focus sul testo della nuova domanda
        });

        $(document).on('click', '#questions-container .add-answer', function () {
            const $thisButton = $(this);
            const questionNum = $thisButton.data('question');
            const $questionBlock = $thisButton.closest('.question-block-styled');
            const answerCount = $questionBlock.find('.answers-container-styled .answer-block-styled').length + 1;
            addAnswerToQuestion($questionBlock, questionNum, answerCount);
            renumberQuestions();
            $questionBlock.find('.answer-block-styled:last-child input[type="text"]').first().focus(); // Focus sul testo della nuova risposta
        });

        $(document).on('click', '#questions-container .remove-question', function () {
            $(this).closest('.question-block-styled').remove();
            renumberQuestions();
        });

        $(document).on('click', '#questions-container .remove-answer', function () {
            const $answerBlock = $(this).closest('.answer-block-styled');
            const $questionBlock = $answerBlock.closest('.question-block-styled');

            if ($questionBlock.find('.answers-container-styled .answer-block-styled').length > 1) {
                $answerBlock.remove();
                renumberQuestions();
            } else {
                showAlert('Ogni domanda deve avere almeno una risposta.', 'warning', '#alert-container-page');
            }
        });

        $('#save-questions').click(function () {
            const quizId = $('#quiz-id').val();
            if (!quizId) {
                showAlert('ID del Quiz non trovato. Impossibile salvare.', 'danger', '#alert-container-page');
                return;
            }
            if ($('#questions-container .question-block-styled').length === 0) {
                showAlert('Devi aggiungere almeno una domanda.', 'warning', '#alert-container-page');
                return;
            }
            let allQuestionsValid = true;
            $('#questions-container .question-block-styled').each(function () {
                const $qBlock = $(this);
                const qNumText = $qBlock.find('.question-number').text();
                if ($qBlock.find('textarea.textarea-styled').val().trim() === '') {
                    showAlert(`Il testo della Domanda ${qNumText} non può essere vuoto.`, 'warning', '#alert-container-page');
                    allQuestionsValid = false; return false;
                }
                if ($qBlock.find('.answers-container-styled .answer-block-styled').length === 0) {
                    showAlert(`La Domanda ${qNumText} non ha risposte.`, 'warning', '#alert-container-page');
                    allQuestionsValid = false; return false;
                }
                let hasCorrect = false;
                let allAnswersHaveText = true;
                $qBlock.find('.answers-container-styled .answer-block-styled').each(function (idx) {
                    if ($(this).find('input[type="text"].input-small-styled').val().trim() === '') {
                        showAlert(`Il testo della Risposta ${idx + 1} per la Domanda ${qNumText} non può essere vuoto.`, 'warning', '#alert-container-page');
                        allAnswersHaveText = false; return false;
                    }
                    if ($(this).find('input.radio-input-styled[value="Corretta"]').is(':checked')) {
                        hasCorrect = true;
                    }
                });
                if (!allAnswersHaveText) { allQuestionsValid = false; return false; }
                if (!hasCorrect) {
                    showAlert(`La Domanda ${qNumText} non ha una risposta designata come "Corretta".`, 'warning', '#alert-container-page');
                    allQuestionsValid = false; return false;
                }
            });
            if (!allQuestionsValid) return;

            const formData = $('#questions-form').serialize() + '&quiz_id=' + quizId + '&action=save_questions'; // Aggiungi action per API
            $.ajax({
                type: 'POST',
                url: 'api/questions.php', // API per le domande
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        showAlert('Domande salvate con successo!', 'success', '#alert-container-page');
                        setTimeout(() => window.location.href = 'quiz_view.php?id=' + quizId, 1500);
                    } else {
                        showAlert(response.message || 'Errore nel salvataggio delle domande.', 'danger', '#alert-container-page');
                    }
                },
                error: (xhr) => {
                    showAlert('Errore AJAX salvataggio domande: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger', '#alert-container-page');
                }
            });
        });

        const $confirmAbortModal = $('#confirmAbortCreationModal');
        const $confirmAbortActionBtn = $('#confirmAbortActionBtn');
        const $abortModalMessage = $('#abortCreationModalMessage');
        function showAbortCreationModal() { if ($confirmAbortModal.length) $confirmAbortModal.fadeIn(200); }
        function hideAbortCreationModal() { if ($confirmAbortModal.length) $confirmAbortModal.fadeOut(200); }

        if ($confirmAbortModal.length) {
            $confirmAbortModal.find('.custom-modal-close-button, #cancelAbortBtn').on('click', hideAbortCreationModal);
            $(window).on('click', (event) => { if ($(event.target).is($confirmAbortModal)) hideAbortCreationModal(); });
            $(document).on('keydown', (event) => { if (event.key === 'Escape' && $confirmAbortModal.is(':visible')) hideAbortCreationModal(); });
        }

        $('#abort-quiz-creation').click(function () {
            const quizIdToDelete = $('#quiz-id').val();
            let msg = "Sei sicuro di voler annullare la creazione del quiz?";
            if (quizIdToDelete && $('#questions-section').is(':visible')) { // Solo se il quiz è stato creato e siamo nella sezione domande
                msg += " Il quiz parzialmente creato (ID: " + quizIdToDelete + ") e le sue domande verranno eliminati.";
            } else if ($('#create-quiz-form').find('input[name="titolo"]').val() !== '') { // Se ci sono dati nel form dettagli
                msg += " Eventuali dati inseriti per i dettagli del quiz verranno persi.";
            }
            if ($abortModalMessage.length) $abortModalMessage.text(msg);
            showAbortCreationModal();

            if ($confirmAbortActionBtn.length) {
                $confirmAbortActionBtn.off('click').on('click', function () {
                    hideAbortCreationModal();
                    if (quizIdToDelete && $('#questions-section').is(':visible')) { // Quiz creato, cancella da DB
                        $.ajax({
                            url: `api/quiz.php?action=delete&delId=${quizIdToDelete}`, // usa action=delete
                            method: 'GET', // o DELETE se il server lo supporta e l'API è configurata
                            dataType: 'json',
                            success: (res) => {
                                if (res.status === 'success') { // API quiz.php dovrebbe tornare JSON per delete
                                    showAlert('Creazione annullata e quiz eliminato.', 'info', '#alert-container-page');
                                    setTimeout(() => {
                                        // Resetta completamente la pagina
                                        $('#quiz-details-section').slideDown();
                                        $('#questions-section').slideUp();
                                        $('#create-quiz-form')[0].reset();
                                        $('#questions-form')[0].reset();
                                        $('#quiz-id').val('');
                                        $('#questions-container').empty().html('<p id="no-questions-message" class="message-placeholder-styled text-align-center padding-vertical-medium">Nessuna domanda aggiunta. Clicca su "Aggiungi Domanda" per iniziare.</p>');
                                        renumberQuestions();
                                    }, 1500);
                                } else showAlert(res.message || 'Errore durante l\'eliminazione del quiz.', 'danger', '#alert-container-page');
                            }, error: () => showAlert('Errore AJAX durante l\'annullamento.', 'danger', '#alert-container-page')
                        });
                    } else { // Solo reset del form, nessun quiz da cancellare da DB
                        showAlert('Creazione annullata.', 'info', '#alert-container-page');
                        $('#quiz-details-section').slideDown();
                        $('#questions-section').slideUp();
                        $('#create-quiz-form')[0].reset();
                        $('#questions-form')[0].reset();
                        $('#quiz-id').val('');
                        $('#questions-container').empty().html('<p id="no-questions-message" class="message-placeholder-styled text-align-center padding-vertical-medium">Nessuna domanda aggiunta. Clicca su "Aggiungi Domanda" per iniziare.</p>');
                        renumberQuestions();
                    }
                });
            }
        });
    }

    // Handler per il cambio dei radio button (gestione campo punti) in quiz_create.php e quiz_modify.php
    $(document).on('change', '#questions-section .answer-block-styled input[type="radio"][name$="[type]"], #edit-quiz-form .answer-block-styled input[type="radio"][name$="[type]"]', function () {
        const $answerBlock = $(this).closest('.answer-block-styled');
        const $pointsGroup = $answerBlock.find('.points-group');
        const $pointsInput = $pointsGroup.find('input[type="number"]');

        if ($(this).val() === 'Corretta' && $(this).is(':checked')) {
            $pointsGroup.slideDown();
            if (parseInt($pointsInput.val(), 10) <= 0 || $pointsInput.val() === "") {
                $pointsInput.val(1); // Default a 1 se non valido o vuoto
            }
        } else {
            // Azzera e nascondi solo se "Sbagliata" è effettivamente selezionato per questo gruppo di radio
            const nameAttr = $(this).attr('name');
            if ($answerBlock.find('input[type="radio"][name="' + nameAttr + '"][value="Sbagliata"]').is(':checked')) {
                $pointsGroup.slideUp(function () { // Callback per azzerare dopo l'animazione
                    $pointsInput.val(0);
                });
            }
        }
    });


    const $participateForm = $('#participate-form'); // Selettore del form di partecipazione

    if ($participateForm.length) {
        $participateForm.on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const $submitButton = $form.find('button[type="submit"]');
            const originalButtonText = $submitButton.html();
            const alertContainerSelector = '#alert-container-participate'; // Selettore dell'alert container specifico

            // Pulisce eventuali alert precedenti prima di una nuova richiesta
            $(alertContainerSelector).empty();

            $submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Invio in corso...');

            $.ajax({
                type: 'POST',
                url: 'api/partecipation.php',
                data: $form.serialize() + '&action=submit', // Assicura che action=submit sia inviato
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        if (response.redirect_url) {
                            // L'API PHP imposta $_SESSION['participationMessage']
                            // Non è strettamente necessario sessionStorage qui se il redirect gestisce il messaggio
                            window.location.href = response.redirect_url;
                        } else {
                            showAlert(response.message || 'Partecipazione inviata con successo!', 'success', alertContainerSelector);
                            $form.hide(); // Nascondi il form se non c'è redirect
                        }
                    } else {
                        showAlert(response.message || 'Si è verificato un errore durante l\'invio.', 'danger', alertContainerSelector);
                        $submitButton.prop('disabled', false).html(originalButtonText);
                    }
                },
                error: function (xhr, status, error) {
                    let errorMsg = 'Errore di comunicazione con il server. Riprova più tardi.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.status === 401) {
                        errorMsg = 'Sessione scaduta o non autorizzato. Effettua nuovamente il login.';
                        setTimeout(() => window.location.href = 'auth_login.php', 2500);
                    } else if (xhr.status === 403) {
                        errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Accesso negato o quiz non più disponibile.';
                        setTimeout(() => window.location.href = 'index.php', 2500);
                    } else if (xhr.status === 409) {
                        errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Hai già partecipato a questo quiz.';
                        if (xhr.responseJSON && xhr.responseJSON.redirect_url) {
                            setTimeout(() => window.location.href = xhr.responseJSON.redirect_url, 2500);
                        } else {
                            setTimeout(() => window.location.href = 'quiz_participations.php', 2500);
                        }
                    } else if (xhr.status === 404) {
                        errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Quiz non trovato o endpoint API errato.';
                    } else {
                        console.error("Errore AJAX invio partecipazione:", xhr.status, xhr.responseText, status, error);
                    }

                    showAlert(errorMsg, 'danger', alertContainerSelector);
                    $submitButton.prop('disabled', false).html(originalButtonText);
                }
            });
        });

        // Aggiungi qui lo snippet per la classe .selected-answer se lo stai usando
        $participateForm.on('change', '.answer-option input[type="checkbox"]', function () {
            const $label = $(this).closest('label');
            if ($(this).is(':checked')) {
                $label.addClass('selected-answer');
            } else {
                $label.removeClass('selected-answer');
            }
        });
        $participateForm.find('.answer-option input[type="checkbox"]:checked').each(function () {
            $(this).closest('label').addClass('selected-answer');
        });
    }

    // --- Modifica Quiz (quiz_modify.php) ---
    if ($('#edit-quiz-form').length) {

        // Funzioni specifiche per la rinumerazione nella pagina di modifica
        function renumberQuestionsForEdit() {
            const $form = $('#edit-quiz-form');
            const $questionBlocks = $form.find('#questions-container .question-block-styled');
            $form.find('#no-questions-message').toggle($questionBlocks.length === 0);

            $questionBlocks.each(function (qIndex) {
                const $currentQuestionBlock = $(this);
                const displayQNum = qIndex + 1;

                $currentQuestionBlock.attr('data-question-index', qIndex);
                $currentQuestionBlock.find('.question-number-badge').first().text(displayQNum); // Usa .question-number-badge

                $currentQuestionBlock.find('.add-answer-btn .question-number-btn-text').text(displayQNum);
                $currentQuestionBlock.find('.remove-question-btn').attr('title', `Rimuovi Domanda ${displayQNum}`);

                $currentQuestionBlock.find('textarea.textarea-styled').each(function () {
                    const newName = `questions[${qIndex}][text]`;
                    $(this).attr('name', newName);
                    const newId = `question_text_${qIndex}`;
                    $(this).attr('id', newId);
                    $(this).closest('.form-field-group').find('label.form-label-styled').attr('for', newId);
                });

                $currentQuestionBlock.find('input[type="hidden"][name$="[original_numero_domanda]"]').each(function () {
                    const newNameHidden = `questions[${qIndex}][original_numero_domanda]`;
                    $(this).attr('name', newNameHidden);
                });

                const $answersContainer = $currentQuestionBlock.find('.answers-container-styled');
                $answersContainer.attr('id', `answers-container-q${qIndex}`);
                $answersContainer.find('.no-answers-message').attr('id', `no-answers-for-q${qIndex}`);

                renumberAnswersForEdit($currentQuestionBlock, qIndex);
            });
        }

        function renumberAnswersForEdit($questionBlock, questionIndex) {
            const $answerBlocks = $questionBlock.find('.answers-container-styled .answer-block-styled');
            $questionBlock.find('.no-answers-message').toggle($answerBlocks.length === 0);

            $answerBlocks.each(function (aIndex) {
                const $currentAnswerBlock = $(this);
                const displayANum = aIndex + 1;

                $currentAnswerBlock.attr('data-answer-index', aIndex);
                $currentAnswerBlock.find('.answer-number-badge').first().text(displayANum); // Usa .answer-number-badge

                const nameQuestionPart = `questions[${questionIndex}]`;
                const nameAnswerPart = `[answers][${aIndex}]`;

                $currentAnswerBlock.find('input.form-input-styled.input-small-styled[type="text"]').each(function () {
                    const newName = `${nameQuestionPart}${nameAnswerPart}[text]`;
                    $(this).attr('name', newName);
                    const newId = `answer_text_${questionIndex}_${aIndex}`;
                    $(this).attr('id', newId);
                    $(this).closest('.form-field-group').find('label.form-label-styled').attr('for', newId);
                });

                $currentAnswerBlock.find('input.radio-input-styled[name$="[type]"]').each(function () {
                    const radioValue = $(this).val();
                    const typeSuffix = radioValue === 'Corretta' ? 'C' : 'S';
                    const newId = `q${questionIndex}a${aIndex}tipo${typeSuffix}`;
                    $(this).attr('name', `${nameQuestionPart}${nameAnswerPart}[type]`);
                    $(this).attr('id', newId);
                    $(this).next('label.radio-label-styled').attr('for', newId);
                });

                $currentAnswerBlock.find('input.input-numerical-styled[name$="[points]"]').each(function () {
                    const newId = `q${questionIndex}a${aIndex}punti`;
                    $(this).attr('name', `${nameQuestionPart}${nameAnswerPart}[points]`);
                    $(this).attr('id', newId);
                    $(this).closest('.points-group').find('label.form-label-styled').attr('for', newId);
                });

                $currentAnswerBlock.find('input[type="hidden"][name$="[original_numero_risposta]"]').each(function () {
                    $(this).attr('name', `${nameQuestionPart}${nameAnswerPart}[original_numero_risposta]`);
                });
                $currentAnswerBlock.find('input[type="hidden"][name$="[id_risposta]"]').each(function () { // Per API update
                    $(this).attr('name', `${nameQuestionPart}${nameAnswerPart}[id_risposta]`);
                });
            });
        }

        // Chiamata iniziale per assicurare che gli indici e i numeri siano corretti al caricamento
        renumberQuestionsForEdit();

        // Aggiungi Nuova Domanda in edit
        $('#edit-quiz-form').on('click', '#add-question-btn', function () {
            const questionTemplate = $('#question-template').html();
            const questionIndex = $('#edit-quiz-form #questions-container .question-block-styled').length;
            const displayQNum = questionIndex + 1;

            let newQuestionHtml = questionTemplate
                .replace(/__Q_INDEX__/g, questionIndex)
                .replace(/__Q_DISPLAY_NUM__/g, displayQNum)
                .replace(/value="[^"]*"/g, 'value=""') // Pulisce i value degli input nel template
                .replace(/original_numero_domanda" value="[^"]*"/g, 'original_numero_domanda" value=""') // Pulisce original_numero_domanda
                .replace(/<textarea[^>]*>[\s\S]*?<\/textarea>/g, '<textarea class="textarea-styled" name="questions[' + questionIndex + '][text]" id="question_text_' + questionIndex + '" rows="3"></textarea>'); // Pulisce textarea


            $('#edit-quiz-form #questions-container').append(newQuestionHtml);

            const $newlyAddedQuestionBlock = $('#edit-quiz-form #questions-container .question-block-styled').last();
            if ($newlyAddedQuestionBlock.length) {
                const answerTemplate = $('#answer-template').html();
                const answerIndex = 0;
                const displayANum = 1;
                let newAnswerHtml = answerTemplate
                    .replace(/__Q_INDEX__/g, questionIndex)
                    .replace(/__A_INDEX__/g, answerIndex)
                    .replace(/__A_DISPLAY_NUM__/g, displayANum)
                    .replace(/value="[^"]*"/g, 'value=""') // Pulisce value
                    .replace(/id_risposta" value="[^"]*"/g, 'id_risposta" value=""') // Pulisce id_risposta
                    .replace(/original_numero_risposta" value="[^"]*"/g, 'original_numero_risposta" value=""'); // Pulisce original_numero_risposta

                $newlyAddedQuestionBlock.find('.answers-container-styled').append(newAnswerHtml);
                $newlyAddedQuestionBlock.find('.no-answers-message').hide();
                // Seleziona "Sbagliata" e imposta punti a 0 per la nuova risposta di default
                const $newAnswer = $newlyAddedQuestionBlock.find('.answer-block-styled').first();
                $newAnswer.find('input[type="radio"][value="Sbagliata"]').prop('checked', true);
                $newAnswer.find('input[type="number"][name$="[points]"]').val(0);
                $newAnswer.find('.points-group').hide(); // Nascondi gruppo punti se "Sbagliata"
            }
            renumberQuestionsForEdit();
            $newlyAddedQuestionBlock.find('textarea').first().focus();
        });

        // Aggiungi Risposta a una Domanda in edit
        $('#edit-quiz-form').on('click', '.add-answer-btn', function () {
            const $questionBlock = $(this).closest('.question-block-styled');
            const questionIndex = $questionBlock.data('question-index');

            const $answersContainer = $questionBlock.find('.answers-container-styled');
            const answerIndex = $answersContainer.find('.answer-block-styled').length;
            const displayANum = answerIndex + 1;

            const answerTemplate = $('#answer-template').html();
            let newAnswerHtml = answerTemplate
                .replace(/__Q_INDEX__/g, questionIndex)
                .replace(/__A_INDEX__/g, answerIndex)
                .replace(/__A_DISPLAY_NUM__/g, displayANum)
                .replace(/value="[^"]*"/g, 'value=""')
                .replace(/id_risposta" value="[^"]*"/g, 'id_risposta" value=""')
                .replace(/original_numero_risposta" value="[^"]*"/g, 'original_numero_risposta" value=""');

            $answersContainer.append(newAnswerHtml);
            $questionBlock.find('.no-answers-message').hide();
            // Seleziona "Sbagliata" e imposta punti a 0 per la nuova risposta di default
            const $newAnswer = $answersContainer.find('.answer-block-styled').last();
            $newAnswer.find('input[type="radio"][value="Sbagliata"]').prop('checked', true);
            $newAnswer.find('input[type="number"][name$="[points]"]').val(0);
            $newAnswer.find('.points-group').hide();

            renumberAnswersForEdit($questionBlock, questionIndex);
            $newAnswer.find('input[type="text"]').first().focus();
        });

        // Rimuovi Domanda in edit
        $('#edit-quiz-form').on('click', '.remove-question-btn', function () {
            $(this).closest('.question-block-styled').remove();
            renumberQuestionsForEdit();
        });

        // Rimuovi Risposta in edit
        $('#edit-quiz-form').on('click', '.remove-answer-btn', function () {
            const $answerBlock = $(this).closest('.answer-block-styled');
            const $questionBlock = $answerBlock.closest('.question-block-styled');
            const questionIndex = $questionBlock.data('question-index');

            if ($questionBlock.find('.answers-container-styled .answer-block-styled').length > 1) {
                $answerBlock.remove();
                renumberAnswersForEdit($questionBlock, questionIndex);
            } else {
                showEditAlerts('Ogni domanda deve avere almeno una risposta!', 'warning');
            }
        });


        // Submit del form di modifica
        $('#edit-quiz-form').submit(function (e) {
            e.preventDefault();
            const $form = $(this);
            const startDateVal = $form.find('#dataInizio').val();
            const endDateVal = $form.find('#dataFine').val();

            if (startDateVal && endDateVal) {
                const startDate = new Date(startDateVal);
                const endDate = new Date(endDateVal);
                // true per allowPastStartDate in modifica, ma solo se la data di inizio originale non è nel futuro
                const originalStartDate = new Date($form.find('#original_data_inizio').val() || startDateVal); // Campo hidden con data originale
                const allowPast = originalStartDate <= new Date(); // Permetti date passate solo se la data originale non era futura

                const dateError = checkDateRange(startDate, endDate, allowPast);
                if (dateError) {
                    showEditAlerts(dateError, 'danger');
                    return;
                }
            } else if (!startDateVal || !endDateVal) {
                showEditAlerts('Le date di inizio e fine sono obbligatorie.', 'warning');
                return;
            }


            if ($form.find('#questions-container .question-block-styled').length === 0) {
                showEditAlerts('Il quiz deve avere almeno una domanda.', 'warning');
                return;
            }

            let allQuestionsValid = true;
            $form.find('#questions-container .question-block-styled').each(function (qIdx) {
                const $qBlock = $(this);
                const qNumText = $qBlock.find('.question-number-badge').first().text();

                if ($qBlock.find('textarea.textarea-styled').val().trim() === '') {
                    showEditAlerts(`Il testo della Domanda ${qNumText} non può essere vuoto.`, 'warning');
                    allQuestionsValid = false; return false;
                }

                if ($qBlock.find('.answers-container-styled .answer-block-styled').length === 0) {
                    showEditAlerts(`La Domanda ${qNumText} non ha risposte. Ogni domanda deve avere almeno una risposta.`, 'warning');
                    allQuestionsValid = false; return false;
                }

                let hasCorrectAnswer = false;
                let allAnswersHaveText = true;
                $qBlock.find('.answers-container-styled .answer-block-styled').each(function (aIdx) {
                    if ($(this).find('input[type="text"].form-input-styled').val().trim() === '') {
                        showEditAlerts(`Il testo della Risposta ${aIdx + 1} per la Domanda ${qNumText} non può essere vuoto.`, 'warning');
                        allAnswersHaveText = false; return false;
                    }
                    if ($(this).find('input[type="radio"][name$="[type]"][value="Corretta"]').is(':checked')) {
                        hasCorrectAnswer = true;
                    }
                });
                if (!allAnswersHaveText) { allQuestionsValid = false; return false; }

                if (!hasCorrectAnswer) {
                    showEditAlerts(`La Domanda ${qNumText} non ha una risposta designata come "Corretta".`, 'warning');
                    allQuestionsValid = false; return false;
                }
            });

            if (!allQuestionsValid) return;

            const $submitButton = $form.find('button[type="submit"]');
            const originalButtonText = $submitButton.html();
            $submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvataggio...');


            $.ajax({
                url: $form.attr('action'), // action dovrebbe essere api/quiz.php?action=update
                type: $form.attr('method'), // method dovrebbe essere POST
                data: $form.serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        showEditAlerts(response.message || 'Quiz aggiornato con successo!', 'success');
                        // const quizId = $form.find('input[name="quiz_id"]').val();
                        setTimeout(() => {
                            // window.location.href = 'quiz_view.php?id=' + quizId;
                            window.location.href = response.redirect_url || 'quiz_my.php'; // Usa redirect_url o fallback
                        }, 1500);
                    } else {
                        showEditAlerts(response.message || 'Errore durante il salvataggio delle modifiche.', 'danger');
                    }
                },
                error: function (xhr) {
                    showEditAlerts('Errore di comunicazione: ' + (xhr.responseJSON?.message || xhr.statusText || 'Dettagli non disponibili'), 'danger');
                },
                complete: function () {
                    $submitButton.prop('disabled', false).html(originalButtonText);
                }
            });
        });
    } // Fine if ($('#edit-quiz-form').length)


    // --- Gestione Modale Conferma Eliminazione (Globale per quiz_my_list.php, quiz_view.php) ---
    const $confirmDeleteModal = $('#confirmDeleteModal'); // Modale di conferma eliminazione
    if ($confirmDeleteModal.length) {
        const $confirmDeleteButton = $('#confirmDeleteActionBtn');
        let quizIdToDeleteModal = null;
        let quizTitleToDeleteModal = '';
        let successRedirectUrl = ''; // Per reindirizzare dopo eliminazione (es. da quiz_view a quiz_my_list)

        const $cancelOrCloseButtons = $confirmDeleteModal.find('.custom-modal-close-button, #cancelDeleteBtn');

        function showMainDeleteModal(quizId, quizTitle = 'questo quiz', redirectUrlAfterDelete = null) {
            quizIdToDeleteModal = quizId;
            quizTitleToDeleteModal = quizTitle;
            successRedirectUrl = redirectUrlAfterDelete || window.location.href; // Default: ricarica pagina corrente

            const $quizTitleSpan = $('#quizTitleToDelete');
            if ($quizTitleSpan.length) {
                $quizTitleSpan.text(quizTitleToDeleteModal); // Mostra il titolo del quiz
            }
            $('#modalDeleteErrorMessage').hide().text(''); // Nascondi/resetta messaggi di errore precedenti
            $confirmDeleteModal.fadeIn(200);
        }

        function hideMainDeleteModal() {
            $confirmDeleteModal.fadeOut(200);
            quizIdToDeleteModal = null;
            quizTitleToDeleteModal = '';
            successRedirectUrl = '';
        }

        // Listener per aprire il modale (delegato per bottoni aggiunti dinamicamente)
        $(document).on('click', '.delete-quiz-btn', function (event) {
            event.preventDefault();
            const $button = $(this);
            const quizId = $button.data('delid') || $button.attr('delid');
            const quizTitle = $button.data('quiz-title') || 'questo quiz';
            const redirectUrl = $button.data('redirect-on-delete'); // es. 'quiz_my_list.php' se si elimina da quiz_view

            if (quizId && quizId !== '#') {
                showMainDeleteModal(quizId, quizTitle, redirectUrl);
            }
        });

        $confirmDeleteButton.on('click', function () {
            if (quizIdToDeleteModal) {
                const $thisButton = $(this);
                const originalButtonText = $thisButton.html();
                $thisButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Eliminazione...');
                $('#modalDeleteErrorMessage').hide();

                $.ajax({
                    url: `api/quiz.php?action=delete&delId=${quizIdToDeleteModal}`, // API dovrebbe gestire action=delete
                    method: 'GET', // o 'DELETE' se l'API lo supporta
                    dataType: 'json', // Aspettati JSON dalla tua API
                    success: function (response) {
                        if (response.status === 'success') {
                            hideMainDeleteModal();
                            // Usa showAlert sulla pagina principale, non nel modale
                            showAlert(response.message || 'Quiz eliminato con successo!', 'success', '#alert-container-page');
                            setTimeout(() => {
                                if (successRedirectUrl && successRedirectUrl !== window.location.href) {
                                    window.location.href = successRedirectUrl;
                                } else {
                                    location.reload(); // Ricarica la pagina corrente (es. quiz_my_list.php)
                                }
                            }, 1500);
                        } else {
                            // Mostra errore nel modale o come alert globale
                            // showAlert(response.message || 'Errore durante l\'eliminazione.', 'danger', '#alert-container-page');
                            $('#modalDeleteErrorMessage').text(response.message || 'Errore durante l\'eliminazione.').show();
                        }
                    },
                    error: function (xhr) {
                        const errorMsg = xhr.responseJSON?.message || xhr.statusText || 'Errore AJAX durante l\'eliminazione.';
                        // showAlert(errorMsg, 'danger', '#alert-container-page');
                        $('#modalDeleteErrorMessage').text(errorMsg).show();
                    },
                    complete: function () {
                        $thisButton.prop('disabled', false).html(originalButtonText);
                        // Non nascondere il modale qui se c'è stato un errore, l'utente potrebbe voler riprovare
                        // hideMainDeleteModal(); // Spostato nel success
                    }
                });
            }
        });

        $cancelOrCloseButtons.on('click', hideMainDeleteModal);
        $(window).on('click', (event) => {
            if ($confirmDeleteModal.is(':visible') && $(event.target).is($confirmDeleteModal)) {
                hideMainDeleteModal();
            }
        });
        $(document).on('keydown', (event) => {
            if (event.key === 'Escape' && $confirmDeleteModal.is(':visible')) {
                hideMainDeleteModal();
            }
        });
    } // Fine if ($confirmDeleteModal.length)


    // --- Funzionalità specifiche per la pagina Index (index.php) ---
    if (document.body.classList.contains('page-index')) { // Meglio usare una classe sul body per identificare la pagina
        const $resetFormButton = $('#reset-filters-btn'); // ID del bottone reset filtri
        if ($resetFormButton.length) {
            $resetFormButton.on('click', function (event) {
                event.preventDefault();
                // Resetta i campi del form filtri e ricarica la pagina o invia il form vuoto
                const $filterForm = $(this).closest('form');
                if ($filterForm.length) {
                    $filterForm[0].reset(); // Resetta i campi del form
                    // Rimuovi parametri extra dall'URL se vuoi una pulizia completa
                    window.location.href = window.location.pathname;
                } else {
                    window.location.href = window.location.pathname; // Fallback se non trova il form
                }
            });
        }

        // Gestione cambio per_page e sort_by se esistono e sono select
        $('#per_page_select, #sort_by_inline').on('change', function () {
            $(this).closest('form').submit(); // Invia il form quando il select cambia
        });
    }
});