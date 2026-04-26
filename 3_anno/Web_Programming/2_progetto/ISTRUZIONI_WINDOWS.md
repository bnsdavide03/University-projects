ISTRUZIONI PER L'AVVIO DEL PROGETTO QUIZ ONLINE SU WINDOWS

1.  PREREQUISITI INSTALLATI:
    *   Python (3.8+): 'python' e 'pip' eseguibili.
    *   Java JDK (17+): 'java', 'javac' eseguibili.
    *   PostgreSQL (14+): Server PostgreSQL installato e IN ESECUZIONE. 'psql' eseguibile.
        Avrà bisogno della password dell'utente 'postgres'.
    *   Apache Maven (3.6+): 'mvn' eseguibile.
    *   APACHE TOMCAT (11+): Già installato e configurato.
        Dovrà assicurarsi che Tomcat sia in esecuzione dopo il deploy della servlet.

2. VARIABILI DI SISTEMA NECESSARIE:

| Variabile di Ambiente | Valore Esempio (versioni in base a quella installata)          |
|------------------------|---------------------------------------------------------------|
| `JAVA_HOME`            | `C:\Program Files\Java\jdk-17`                                |
| `MAVEN_HOME`           | `C:\Program Files\Apache\Maven\apache-maven-3.9.9`            |
| `CATALINA_HOME`        | `C:\Program Files\Apache Software Foundation\Tomcat 11.0`     |
| `Path`                 | `%MAVEN_HOME%\bin`, `C:\Program Files\PostgreSQL\14\bin`      |

2.  ESECUZIONE DELLO SCRIPT:
    *   Eseguire come amministratore lo script "setup_avvio_progetto.bat";
    *   Lo script le chiederà:
        *   La password dell'utente 'postgres' di PostgreSQL;
        *   La conferma ad ogni passo (premere invio per continuare);
    *   Se tutto va bene al termine dello script si aprirà una nuova finestra del Prompt dei Comandi per il server Django e una per Tomcat.

3.  UTILIZZO:
    *  La Servlet java (che avvierà l'import del db) sarà su: http://localhost:8080/migration-servlet-app/migrate (è possibile ci voglia qualche minuto perché il caricamento termini, il log delle varie richieste POST viene stampato nella finestra di terminale di Django);
    *  Al termine della migrazione sulla pagina del browser verrà mostrato l'esito della stessa;
    * Verificare entrando nel db postgres (psql -U postgres -d quiz_online) che l'import sia avvenuto con successo (le tabelle con i dati importati hanno il prefisso importer_ ).