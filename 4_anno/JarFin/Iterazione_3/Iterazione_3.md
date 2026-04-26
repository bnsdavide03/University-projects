# Iterazione 3 – API Gateway, Web UI e Integrazione NLU

## 1. Obiettivi dell’iterazione

L’Iterazione 3 del progetto **JarFin** ha avuto come obiettivo principale la trasformazione del sistema da un insieme di microservizi backend indipendenti a una **piattaforma integrata e accessibile tramite un’interfaccia utente unificata**.

In particolare, l’iterazione si è concentrata su:

- introduzione di un **API Gateway** come punto di ingresso unico;
- sviluppo di una **Web UI server-side** per la visualizzazione degli analytics;
- integrazione di un sistema di **inserimento delle transazioni tramite comandi vocali**;
- implementazione di un **motore NLU deterministico** per la trasformazione del linguaggio naturale in dati strutturati;
- miglioramento della qualità del codice e della configurabilità del sistema.

Il risultato finale è un flusso *end-to-end* che collega direttamente l’utente ai microservizi finanziari senza esporre la complessità architetturale sottostante.


## 2. Evoluzione architetturale

### 2.1 Stato precedente

Nelle iterazioni precedenti, i microservizi **Accounting** e **Analytics** erano accessibili direttamente dal client, ciascuno tramite una porta dedicata.  
Questa configurazione presentava diversi limiti:

- forte accoppiamento tra client e servizi;
- difficoltà di evoluzione dell’architettura;
- assenza di un punto di controllo centralizzato.


### 2.2 Introduzione dell’API Gateway

Per risolvere tali criticità è stato introdotto il microservizio `jarfin-api-gateway`, basato su **Spring Cloud Gateway**.

Il Gateway espone un unico entry-point sulla porta **8080** e instrada le richieste secondo le seguenti regole:

- `/api/transactions/**` → Accounting Service (8081)
- `/api/analytics/**` → Analytics Service (8082)
- `/**` → Web UI (8083)

Questa soluzione implementa il pattern **API Gateway**, permettendo di:

- disaccoppiare il frontend dalla topologia dei microservizi;
- centralizzare il routing;
- semplificare future estensioni (sicurezza, logging, rate limiting).

Il frontend comunica esclusivamente con il Gateway, senza conoscere l’esistenza o la posizione dei servizi interni.


## 3. Scelte tecnologiche

### 3.1 Spring Cloud Gateway

Spring Cloud Gateway è stato scelto per la sua integrazione nativa con Spring Boot e per la possibilità di definire il routing in modo dichiarativo tramite file YAML.  
Rispetto a proxy tradizionali, consente una configurazione più flessibile e una migliore manutenibilità nel contesto di microservizi Java.


### 3.2 Web UI Server-Side

La Web UI è stata implementata come applicazione Spring MVC con rendering server-side:

- **Thymeleaf** per il binding dinamico dei dati;
- **Bootstrap 5** per una UI moderna e responsive.

Questa scelta evita la complessità di una SPA separata e garantisce una stretta integrazione con il backend, mantenendo il frontend semplice e facilmente manutenibile.


### 3.3 Speech-to-Text lato client

Il riconoscimento vocale è stato implementato tramite **Web Speech API**, direttamente nel browser.

Motivazioni principali:
- nessuna dipendenza da servizi cloud esterni;
- riduzione della latenza;
- maggiore tutela della privacy.

Il backend non gestisce audio, ma riceve esclusivamente il testo già trascritto.


## 4. Web UI e comunicazione con il backend

### 4.1 HomeController

Il `HomeController` è responsabile del caricamento della dashboard principale.

Funzioni principali:
- invocazione dell’endpoint `/api/analytics/report` tramite Gateway;
- popolamento del modello Thymeleaf con il `FinancialReport`;
- gestione degli errori in caso di backend non raggiungibile.

In caso di errore, viene mostrata una dashboard degradata ma funzionante, evitando il blocco dell’interfaccia.


### 4.2 CommandController

Il `CommandController` gestisce l’interazione vocale tramite l’endpoint:


Il controller svolge un ruolo puramente orchestrativo:
1. riceve il testo trascritto dal frontend;
2. delega l’interpretazione semantica al servizio NLU;
3. invia la transazione strutturata al servizio Accounting tramite Gateway;
4. restituisce il risultato al frontend.

Non contiene logica di business, rispettando il principio di separazione delle responsabilità.


## 5. Natural Language Understanding (NLU)

### 5.1 Motivazione progettuale

Per l’interpretazione dei comandi vocali è stato sviluppato un **motore NLU custom**, implementato nella classe `NaturalLanguageService`.

La scelta di una soluzione deterministica, basata su regole, è motivata da:
- semplicità di implementazione;
- totale controllo del comportamento;
- assenza di dipendenze esterne;
- facilità di debug e manutenzione.


### 5.2 Pipeline di elaborazione

Il processo di parsing del testo segue una pipeline strutturata:

#### 1. Estrazione dell’importo
Utilizzo di espressioni regolari per individuare valori numerici con supporto a separatori decimali differenti.

#### 2. Classificazione dell’intento
Determinazione del tipo di transazione (`INCOME` o `EXPENSE`) tramite parole chiave semanticamente rilevanti.

#### 3. Categorizzazione
Associazione della transazione a una categoria finanziaria tramite una mappa keyword → categoria.

#### 4. Risoluzione temporale
Assegnazione esplicita della data corrente (`LocalDate.now()`), garantendo coerenza con i vincoli del backend.


### 5.3 Gestione dei casi non riconosciuti

Se nessuna categoria viene identificata:
- il testo viene ripulito da stop-word;
- il contenuto residuo viene utilizzato come descrizione;
- la categoria viene impostata su un valore generico.

Questo approccio aumenta la robustezza del sistema in presenza di input imprevisti.


## 6. Flusso di interazione vocale

Il flusso completo è il seguente:

1. l’utente attiva il microfono dalla Web UI;
2. il browser trascrive il parlato;
3. il testo viene inviato asincronamente al backend;
4. la transazione viene interpretata e salvata;
5. la UI riceve conferma e aggiorna automaticamente la dashboard.

Il sistema è completamente asincrono e non blocca l’esperienza utente.


## 7. Qualità del codice e robustezza

Durante l’iterazione sono stati applicati diversi miglioramenti qualitativi:

- utilizzo sistematico di **SLF4J** per il logging;
- eliminazione di output standard non controllati;
- configurazione esterna degli endpoint tramite `@Value`;
- gestione esplicita degli errori e dei fallback.

Il codice risultante è più leggibile, testabile e pronto per ambienti reali.


## 8. Conclusione

L’Iterazione 3 rappresenta il completamento dell’architettura **JarFin**:

- accesso centralizzato tramite API Gateway;
- frontend integrato e disaccoppiato;
- supporto all’interazione in linguaggio naturale;
- flusso dati completo dalla UI al backend analitico.

Il sistema è ora strutturalmente pronto per future estensioni, come sicurezza avanzata, autenticazione o NLU più sofisticate.
