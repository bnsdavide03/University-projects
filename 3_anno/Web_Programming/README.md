# 📚 Documentazione del Progetto – Quiz Online

Questo progetto è una piattaforma completa per la **creazione**, **gestione** e **partecipazione a quiz online**, realizzata utilizzando tecnologie **PHP**, **Java**, **Django (Python)** e **SQL**. Supporta anche la **migrazione dei dati** tramite API dedicate e una servlet Java.

---

## 📁 Struttura del Progetto

### 🗂️ `1_progetto/` – Applicazione Web (Frontend + Backend)

```
1_progetto/
├── api/                         # Endpoint API RESTful per il backend
│   ├── participation.php        # Gestione partecipazioni ai quiz
│   ├── questions.php            # CRUD domande
│   ├── quiz.php                 # CRUD quiz
│   └── users.php                # Autenticazione e gestione utenti
│
├── assets/                      # Risorse statiche (immagini, font, ecc.)
│
├── css/
│   └── style.css                # Stile dell’interfaccia (responsive e moderno)
│
├── js/
│   └── main.js                  # Logica frontend, validazioni, chiamate AJAX
│
├── config/
│   └── database.php             # Connessione sicura al database (es. via PDO)
│
├── includes/                    # Template PHP riutilizzabili
│   ├── footer.php               # Piè di pagina
│   ├── header.php               # Intestazione HTML comune
│   └── nav.php                  # Barra di navigazione
│
├── sql/
│   ├── script.sql               # Creazione schema DB
│   └── data-seed.sql            # Inserimento dati iniziali/demo
│
├── index.php                    # Homepage
├── auth_login.php               # Login utente
├── auth_logout.php              # Logout
├── auth_register.php            # Registrazione
├── quiz_create.php              # Creazione nuovi quiz
├── quiz_search.php              # Ricerca e visualizzazione quiz
├── quiz_modify.php              # Modifica quiz esistenti
├── quiz_info.php                # Statistiche sui quiz creati
├── quiz_my.php                  # I quiz creati dall’utente loggato
├── quiz_view.php                # Dettagli di un quiz prima della partecipazione
├── quiz_participate.php         # Esecuzione del quiz (risposte)
├── quiz_results.php             # Risultati di un quiz completato
└── quiz_partecipations.php      # Storico partecipazioni utente
```

---

### 🗂️ `2_progetto/` – Integrazione Dati e Migrazione

```
2_progetto/
├── data_migration_django/                # Progetto Django per importazione/migrazione dati
│   ├── data_migrator/                    # Core del progetto Django
│   │   ├── settings.py                   # Configurazioni generali (DB, app)
│   │   ├── urls.py                       # URL routing
│   │   └── wsgi.py / asgi.py             # Entry point server sync/async
│   │
│   ├── importer/                         # App Django per gestione migrazione
│   │   ├── models.py                     # Modelli DB Django
│   │   ├── serializers.py                # Serializzatori per JSON/XML
│   │   ├── views.py                      # Logica per ricezione dati migrati
│   │   └── urls.py                       # URL specifici app
│   │
│   ├── manage.py                         # CLI Django
│   └── requirements.txt                  # Dipendenze Python
│
├── quiz_online/                          # Applicazione PHP dei quiz (duplicata per export)
│   ├── api/
│   │   ├── partecipation.php
│   │   ├── questions.php
│   │   ├── quiz.php
│   │   └── users.php
│   ├── assets/
│   │   ├── css/style.css
│   │   └── js/main.js
│   ├── config/database.php
│   ├── includes/
│   │   ├── footer.php
│   │   ├── header.php
│   │   └── nav.php
│   ├── migration_api/                   # API dedicate all’esportazione dati
│   │   ├── export_answers.php
│   │   ├── export_participations.php
│   │   ├── export_questions.php
│   │   ├── export_quizzes.php
│   │   ├── export_user_quiz_answers.php
│   │   └── export_users.php
│   ├── sql/
│   │   ├── data-seed.sql
│   │   └── script.sql
│   ├── auth_login.php
│   ├── auth_logout.php
│   ├── auth_register.php
│   ├── index.php
│   ├── quiz_create.php
│   ├── quiz_info.php
│   ├── quiz_modify.php
│   ├── quiz_my.php
│   ├── quiz_participate.php
│   ├── quiz_partecipations.php
│   ├── quiz_results.php
│   └── quiz_view.php
│
├── servlet_java/                         # Servlet Java per bridge migrazione
│   └── migration_bridge/
│       ├── src/main/java/com/webprogramming/
│       │   └── MigrationServlet.java     # Servlet ponte per ricevere dati da PHP
│       ├── target/
│       │   ├── classes/                  # Classi compilate
│       │   ├── migration-servlet-app/
│       │   │   └── WEB-INF/lib/          # Librerie (es. Jackson)
│       │   │       ├── jackson-core-2.15.2.jar
│       │   │       ├── jackson-databind-2.15.2.jar
│       │   │       └── jackson-annotations-2.15.2.jar
│       │   ├── test-classes/            # Test compilati
│       │   └── pom.properties
│       └── pom.xml                      # Configurazione Maven
│
├── .gitignore                            # File per escludere file da Git
├── Guida_Progetto.md                     # Documentazione guida generale
└── Scelte_di_Progetto.md                 # Motivazioni tecniche e architetturali
```

---

## 🧩 Tecnologie Utilizzate

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP (API REST), Java Servlet (Orchestrator), Python (Django REST)
* **Database:** MySQL
* **Migrazione Dati:** PHP → Java Servlet → Django
* **Build Tool Java:** Maven
* **Standard Web:** RESTful APIs, PDO, JSON