# 📘 Linee Guida di Implementazione del Progetto **Quiz Online**

## 📌 Introduzione

Il progetto **"Quiz Online"** è un'applicazione web sviluppata in **PHP** che consente agli utenti di **creare quiz personalizzati**, **partecipare ai quiz di altri utenti**, e **visualizzare risultati e statistiche**.
L'architettura segue il pattern **MVC (Model-View-Controller)**, adattato a un contesto PHP per garantire una **chiara separazione tra back-end e front-end**.

## 🧱 Architettura del Sistema

### 🗃️ 1. Struttura del Database

Il database, conforme allo schema ER definito nei documenti tecnici, comprende le seguenti entità principali:

* **Utente**: credenziali, dati anagrafici e tracciamento delle attività.
* **Quiz**: metadati e configurazioni dei quiz.
* **Domanda**: contenuti delle domande associati ai quiz.
* **Risposta**: opzioni multiple per ogni domanda, con punteggio personalizzato.
* **Partecipazione**: traccia le sessioni utente-quiz.
* **RispostaUtenteQuiz**: registra le risposte date dagli utenti per ogni quiz svolto.

### 🗂️ 2. Organizzazione del Codice

```
1_progetto/                      # Radice del progetto dell'applicazione web di quiz online
├── api/                         # Directory contenente gli script PHP che fungono da endpoint API RESTful per il backend
│   ├── participation.php        # API per gestire la partecipazione degli utenti ai quiz e la sottomissione delle loro risposte
│   ├── questions.php            # API per le operazioni CRUD (Create, Read, Update, Delete) sulle domande dei quiz
│   ├── quiz.php                 # API per le operazioni CRUD sui quiz stessi (creazione, elenco, modifica, eliminazione)
│   └── users.php                # API per la gestione degli utenti: autenticazione (login, verifica sessione), registrazione, gestione profili
│
├── assets/                      # Directory per risorse statiche generiche come immagini, icone, font, ecc.
│
├── css/                         # Directory contenente i fogli di stile CSS
│   └── style.css                # Foglio di stile principale per l'interfaccia utente, inclusi stili responsivi e design moderno
│
├── js/                          # Directory contenente i file JavaScript per la logica lato client
│   └── main.js                  # Script JavaScript principale per interazioni dinamiche, validazioni di form, chiamate AJAX agli endpoint API
│
├── config/                      # Directory per i file di configurazione dell'applicazione
│   └── database.php             # Script PHP per configurare e stabilire la connessione al database (es. usando PDO per sicurezza)
│
├── includes/                    # Directory per frammenti di codice PHP/HTML riutilizzabili (template parziali)
│   ├── footer.php               # Script PHP/HTML per il piè di pagina comune a tutte le pagine
│   ├── header.php               # Script PHP/HTML per l'intestazione comune (es. doctype, tag <head>, meta, inclusione CSS/JS)
│   └── nav.php                  # Script PHP/HTML per la barra di navigazione comune del sito
│
├── sql/                         # Directory contenente gli script SQL per il database
│   ├── script.sql               # Script SQL per la creazione dello schema del database (tabelle, relazioni, indici, ecc.)
│   └── data-seed.sql            # Script SQL per popolare il database con dati iniziali o di esempio (demo data)
│
├── index.php                    # Pagina principale dell'applicazione (Homepage), solitamente il punto di ingresso del sito
├── auth_login.php               # Pagina o script PHP per gestire il processo di login degli utenti
├── auth_logout.php              # Script PHP per gestire il processo di logout degli utenti (es. distruggere la sessione)
├── auth_register.php            # Pagina o script PHP per gestire la registrazione di nuovi utenti
├── quiz_create.php              # Pagina che permette agli utenti (autorizzati) di creare nuovi quiz
├── quiz_search.php              # Pagina che permette agli utenti di cercare e visualizzare i quiz disponibili a cui partecipare
├── quiz_modify.php              # Pagina che permette agli utenti (creatori del quiz) di modificare un quiz esistente
├── quiz_info.php                # Pagina che mostra statistiche e informazioni dettagliate sui quiz creati da un utente
├── quiz_my.php                  # Pagina che elenca i quiz creati dall'utente attualmente loggato ("I miei quiz")
├── quiz_view.php                # Pagina per visualizzare i dettagli di un quiz specifico (es. descrizione, numero di domande) prima di parteciparvi
├── quiz_participate.php         # Pagina dove l'utente effettivamente svolge un quiz, rispondendo alle domande
├── quiz_results.php             # Pagina che mostra i risultati ottenuti da un utente dopo aver completato un quiz
└── quiz_partecipations.php      # Pagina che mostra lo storico delle partecipazioni ai quiz da parte dell'utente loggato
```

&nbsp;

## ⚙️ Approccio Implementativo

### 🔧 3.1 Backend

* Implementazione di **API RESTful modulari**, una per ciascuna entità.
* Utilizzo di **PDO con prepared statements** per sicurezza da SQL Injection.
* **Controllo di integrità**: prevenzione di quiz duplicati lato server.

### 🎨 3.2 Frontend

* Layout HTML diviso in **header, nav, contenuto, footer**.
* **Interfaccia utente moderna e reattiva**, con stile fresco e minimal.
* **JavaScript asincrono** per comunicazione con API e UX fluida.
* **Gestione dei quiz disponibili** con:

  * Filtri per autore, data, titolo
  * Ordinamento (alfabetico, per popolarità, titolo, partecipazioni)
  * Impaginazione: visualizzazione a gruppi di **10, 20, 50 o 100 quiz per pagina**

&nbsp;

## 🚀 Funzionalità Principali

### 👤 4.1 Gestione Utenti

* Registrazione, login e logout sicuri
* Verifica sessione utente già loggato con redirect intelligente
* Profilo utente: storico quiz creati e partecipazioni

### 📝 4.2 Creazione e Modifica Quiz

* Aggiunta domande e risposte multiple con punteggi personalizzati
* Impostazione data di apertura/chiusura
* Prevenzione **quiz duplicati** tramite controllo su titolo e data
* Salvataggio progressivo in fase di creazione/modifica

### 🧩 4.3 Partecipazione ai Quiz

* Visualizzazione domande e risposte in **ordine randomico**
* Interfaccia semplice.

### 📊 4.4 Visualizzazione Risultati

* Al termine del quiz:
  * **Punteggio ottenuto**
  * **Correzione delle risposte**, con indicazione di quelle errate/corrette
* Accesso storico ai quiz svolti tramite sezione dedicata

### 📈 4.5 Statistiche Quiz Creati

* Statistiche aggregate sui quiz creati:

  * Numero partecipanti
  * Risposte effettuate dai vari partecipanti

&nbsp;

## 🔐 Considerazioni sulla Sicurezza

* **Validazioni lato server** per tutti i dati ricevuti
* **Prepared statements** per tutte le query al DB
* Filtri e controlli anti-spam lato form

&nbsp;

## ⚡ Considerazioni sulle Prestazioni

* Utilizzo di query ottimizzate e indicizzazioni nel DB
* Minimizzazione e caching delle risorse CSS/JS
* **Caricamento asincrono** dei contenuti per ridurre i tempi di attesa
* API ridotte al minimo necessario per ogni interazione