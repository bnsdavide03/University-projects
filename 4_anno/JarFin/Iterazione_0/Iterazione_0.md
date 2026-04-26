# 📘 Documentazione Ufficiale: Iterazione 0 – Project Jarfin

**Progetto:** Jarfin (Java ARtificial Financial INtelligence)
**Metodologia:** SCRUM + AMDD (Agile Model Driven Development)
**Fase:** Iterazione 0 – Envisioning & Setup

---

## 1. Visione del Prodotto

### 1.1 L'Ispirazione: "Il mito di J.A.R.V.I.S."

L'idea alla base di **Jarfin** nasce da una fantasia condivisa da molti appassionati di tecnologia: possedere un assistente intelligente, onnipresente e proattivo, simile al **J.A.R.V.I.S.** (*Just A Rather Very Intelligent System*) visto nei film di *Iron Man*. Nel contesto cinematografico, Tony Stark delega all'IA la gestione di compiti complessi, permettendogli di concentrarsi solo sulle decisioni strategiche.

### 1.2 Il Problema Reale

Nel mondo reale, la gestione delle **finanze personali** è l'opposto di questa fantasia:

* È frammentata su più app bancarie.
* Richiede l'inserimento manuale noioso di dati su file Excel.
* Manca di un'interfaccia naturale: bisogna navigare menu complessi per capire quanto si è speso.

### 1.3 La Soluzione: Jarfin

**Jarfin** risolve questo problema colmando il divario tra la gestione finanziaria rigida e l'interazione naturale. Non è solo un "gestore di spese", ma un assistente che:

1. **Capisce il linguaggio naturale (NLU):** L'utente può dire o scrivere "Ho speso 20 euro per la pizza" e il sistema comprende intento, importo e categoria.
2. **Centralizza la logica:** Aggrega dati e fornisce analytics avanzati attraverso un'architettura a microservizi.
3. **Elimina l'attrito:** Rende l'inserimento e la consultazione dei dati immediati, trasformando un dovere noioso in un'interazione fluida.


## 2. Analisi dei Requisiti

In questa fase di *Envisioning*, sono stati formalizzati i requisiti necessari per costruire il Core, l'Analytics e il modulo NLU.

### 2.1 Requisiti Funzionali (RF)

Questi requisiti definiscono *cosa* il sistema deve fare.

| ID | Requisito | Descrizione Formale | Priorità |
| --- | --- | --- | --- |
| **RF-01** | **Gestione Transazioni (CRUD)** | Il sistema deve permettere la creazione, lettura, aggiornamento e cancellazione di entrate e uscite finanziarie tramite API REST dedicate. | Alta |
| **RF-02** | **Parsing del Linguaggio Naturale** | Il modulo NLU deve accettare input testuali (es. "Pranzo 15€"), estrarre le entità (importo: 15, valuta: €, categoria: Pranzo) e mapparle in transazioni strutturate. | Alta |
| **RF-03** | **Aggregazione Dati e Reporting** | Il sistema deve calcolare totali per periodo temporale e categoria, fornendo proiezioni finanziarie basate sui dati storici. | Media |
| **RF-04** | **Orchestrazione API Gateway** | Un singolo punto di ingresso deve gestire il routing delle richieste verso i microservizi di Contabilità, Analytics e NLU. | Alta |
| **RF-05** | **Output Strutturato** | Il sistema deve restituire report e risposte in formato JSON standardizzato per essere consumati da eventuali frontend o interfacce vocali. | Media |

### 2.2 Requisiti Non Funzionali (RNF)

Questi requisiti definiscono *come* il sistema deve comportarsi (Quality Attributes).

* **RNF-01 – Modularity (Architettura):** Il sistema deve essere basato su **Microservizi** indipendenti (Spring Boot) per garantire che il fallimento di un modulo (es. Analytics) non blocchi le funzionalità Core (es. Contabilità).
* **RNF-02 – Maintainability (Qualità del Codice):** Il codice deve rispettare i principi di **Clean Code**. È obbligatoria l'analisi statica (tramite strumenti di analisi statica, es. STAN4J) e una copertura dei test (es. JUnit + EclEmma) adeguata con un **Test Coverage** minimo del 70%.
* **RNF-03 – Scalability:** L'architettura deve supportare l'aggiunta di nuovi moduli (es. Speech-to-Text) senza rifattorizzare l'intero backend.
* **RNF-04 – Usability (Interazione):** Il parser NLU deve riconoscere comandi con una variabilità sintattica ragionevole (sinonimi, ordine delle parole diverso) per garantire un'esperienza "umana" e il tempo di risposta deve essere inferiore a **500ms** per non degradare l'esperienza utente.
* **RNF-05 – Data Integrity:** Le transazioni finanziarie devono garantire consistenza; nessuna spesa deve essere persa o duplicata durante l'elaborazione asincrona.


## 3. Strategia di Modellazione: UML 4+1 View Model

Per gestire la complessità di un sistema a microservizi sviluppato in team, abbiamo adottato il modello **UML 4+1**.
Come specificato nella strategia di progetto, utilizziamo queste viste in modo ibrido: **3 viste per la struttura statica** e **2 viste per lo sviluppo dinamico e ibrido**.

### A. Viste Strutturali (Fondamenta del Progetto)

Queste viste definiscono "l'ossatura" del sistema e sono state prioritarie nell'Iterazione 0.

1. **Logical View (Vista Logica) – *Class Diagram***
* **Scopo:** Rappresenta le classi, le interfacce e le loro relazioni (ereditarietà, associazione).
* **Applicazione in Jarfin:** Definisce il *Domain Model* finanziario (Transazione, Categoria, Utente, Report) e i DTO per la comunicazione tra microservizi.

2. **Physical/Deployment View (Vista di Distribuzione) – *Deployment Diagram***
* **Scopo:** Mappa i componenti software sull'hardware o sull'infrastruttura di rete.
* **Applicazione in Jarfin:** Definisce come i microservizi (Container Spring Boot), il Database e l'API Gateway comunicano tra loro e su quali nodi (locali o cloud) risiedono.

3. **Development View (Vista di Sviluppo) – *Component Diagram***
* **Scopo:** Organizzazione del codice in moduli e gestione delle dipendenze.
* **Applicazione in Jarfin:** Gestita tramite la struttura multi-modulo Maven/Gradle e i branch di GitHub, separando chiaramente `Core`, `Analytics` e `NLU`.


### B. Viste per lo Sviluppo Ibrido (Comportamento e Processo)

Queste viste guidano l'implementazione delle funzionalità e i flussi di dati.

4. **Process View (Vista di Processo) – *Sequence Diagram***
* **Scopo:** Mostra l'interazione tra oggetti nel tempo e lo scambio di messaggi.
* **Applicazione in Jarfin:** Cruciale per modellare il flusso asincrono: *Utente -> API Gateway -> NLU -> Contabilità -> DB*. Mostra come una frase viene trasformata in un record database.

5. **Scenarios (Vista "+1") – *Use Case Diagram***
* **Scopo:** Unifica tutte le altre viste descrivendo le interazioni degli utenti (attori) con il sistema.
* **Applicazione in Jarfin:** Definisce i casi d'uso primari (es. "Inserimento spesa vocale", "Richiesta report mensile") che guidano i test e la validazione dei requisiti.


## 3.1. Spiegazione Diagrammi UML

### 3.1.1 Diagramma dei Casi d'Uso

Il Diagramma dei Casi d'Uso rappresenta le interazioni tra l'utente e il sistema durante l'**Iterazione 0**.

1. **Attori**
Il diagramma identifica due tipologie di attori che interagiscono con il sistema:
    * **Utente**: attore che interagisce con l'interfaccia per la gestione delle proprie finanze;
    * **Sistemi esterni**:
        * **Cloud Speech-to-Text**: necessario per la funzionalità vocale; 
        * **Database**: necessario per la persistenza dei dati.

2. **Casi d'uso**
    * **Gestione Accesso**
        * **Registrazione, Login e Logout**: necessari per la sicurezza del sistema e modellati come casi d’uso distinti. La "Registrazione" è tipicamente eseguita una tantum, il "Login" richiede che l'utente sia già registrato e il "Logout" richiede che l'utente sia autenticato.

    * **Gestione Transazioni**
        * **Registrare Transazione**: include la "Conversione NL ad Azione", in quanto ogni comando testuale è processato dal parser NLU per essere compreso dal sistema; è esteso dalla "Conversione STT", che interviene solo se la condizione *input==vocale* è soddisfatta. In tal caso viene coinvolto il sistema esterno Cloud Speech-to-Text;
        * **Gestire Transazioni**: permette la modifica o l'eliminazione di spese inserite. Anche questo caso d'uso include la "Conversione NL ad Azione".

    * **Analisi e Monitoraggio**
        * **Consultare Stato Finanziario**: visualizzazione del saldo attuale;
        * **Gestire Fondi e Budget**: permette di impostare limiti di spesa;
        * **Visualizzare Report e Stime**: generazione di previsioni in base allo storico.


### 3.1.2 Diagramma dei Componenti

Il Diagramma dei Componenti illustra la struttura logica del sistema e le dipendenze verso i servizi esterni. L'architettura è organizzata in tre livelli distinti.

1. **Client Layer**
Rappresenta il frontend dell'applicazione:
    * **Componente GUI**: Gestisce l'interfaccia grafica e le interazioni dell'utente;
    * **UserInterface**: definisce i metodi *inputCommand()* (per catturare l'intento dell'utente) e *displayReport()* (per mostrare i risultati finanziari);
    * ***GUIPORT[1]***: porta di comunicazione che collega il client al resto del sistema tramite un *API Gateway*.

2. **Application Layer**
Responsabile della logica di business, è costituita da tre microservizi:
    * **NLU Service**: si occupa della comprensione del linguaggio naturale. Utilizza l'artefatto *ParserNLU.java* per eseguire l'algoritmo di parsing tramite il metodo *parseCommand()*;
    * **Accounting Service**: gestisce l'aspetto finanziario. Fornisce i metodi *addTransaction()* e *getBalance()* per gestire le spese;
    * **Analytics Service**: utilizza l'artefatto *DataAggregator.java* per generare statistiche e previsioni tramite il metodo *getStatistics()*.

3. **Data Layer**
Si occupa della persistenza delle informazioni:
    * **Componente Database**: gestisce l'archiviazione fisica dei dati;
    * **DBInterface**: fornisce le operazioni di *save()* e *query()*, gestendo il disaccoppiamento tra la logica di business e la persistenza dei dati. Questo permette la manutenzione e la portabilità del sistema.

4. **Dipendenze Esterne**
    * **STT Service**: componente richiesto dall'**NLU Service** per convertire l'input vocale in testo prima che venga elaborato dalla logica interna del sistema.


### 3.1.3 Diagramma dei Deployment

Il Diagramma di Deployment illustra la configurazione fisica dei nodi hardware e ambienti software che compongono il sistema. L'architettura è organizzata a microservizi distribuiti, in modo da garantire scalabilità e isolamento dei componenti.

1. **Architettura dei Nodi**
Il sistema è costituito da quattro entità fisiche o logiche:
    * **User Device**: Dispositivo terminale dell'utente; trattandosi di una Web App, non richiede installazione locale ma interagisce tramite un ambiente controllato (Web Browser);
    * **Cloud Server**: Server remoto che ospita la logica di business;
    * **Database Server**: si occupa della persistenza dei dati. È isolato dagli altri nodi per ragioni di sicurezza e di prestazioni. La scelta di questo RDBMS è giustificata da un’elevata scalabilità e da prestazioni elevate anche lavorando con dati complessi. PostgreSQL garantisce che le operazioni seguano le proprietà ACID;
    * **External STT Service**: entità esterna che fornisce servizi di conversione Speech-to-Text.

2. **Ambienti di Esecuzione e Artefatti**
    * **User Device**: Il Web Browser fornisce l'interprete per l'esecuzione del codice client. L'artefatto Jarfin Web UI è l'insieme di risorse che costituiscono l'interfaccia utente.
    * **Cloud Server**: L'organizzazione a Container garantisce l'isolamento. Il Container NLU ospita l'artefatto ParserNLU.jar (algoritmo 1); il Container Accounting ospita l'artefatto AccountingService.jar (gestione delle spese); il Container Analytics ospita l'artefatto AnalyticsService.jar, utile per la generazione dei report (algoritmo 2).
    * **Database Server**: Il database selezionato è PostgreSQL per l'archiviazione persistente delle operazioni.

3. **Percorsi di Comunicazione/Protocolli**
    * **User Device --> Cloud Server**: utilizza il protocollo HTTPS, il traffico web è gestito tramite API Gateway verso i microservizi interni.
    * **Cloud Server --> Database Server**: utilizza il protocollo JDBC, utile per l'interazione tra servizi e il database.
    * **Cloud Server --> External STT Service**: utilizza il protocollo HTTPS/JSON, si occupa dell'invio dei flussi audio e della ricezione delle trascrizioni testuali.


### 3.1.4 Diagramma delle Classi

Il **Diagramma delle Classi** definisce la struttura statica del codice Java, organizzata secondo il pattern **Controller–Service–Repository** per garantire il disaccoppiamento tra interfaccia, logica applicativa e persistenza dei dati.

- **Core Finanziario**
  La classe `Transaction` rappresenta l’entità principale del dominio ed è caratterizzata da attributi quali:
  - `amount`
  - `date`
  - `category`  

  Essa è legata tramite un’associazione 1..* alla classe `Account`, che identifica il portafoglio dell’utente.

- **Logic Layer**
  I microservizi sono gestiti da classi di tipo *Service* (ad es. `AccountingService`, `ParserNLU`, `DataAggregator`).  
  Queste classi implementano la logica di business, occupandosi di:
  - validazione dei dati
  - applicazione delle regole di dominio
  - esecuzione degli algoritmi di aggregazione e analisi

- **Persistence Layer**
  L’accesso al database **PostgreSQL** è mediato da interfacce *Repository*, che astraggono le query SQL tramite **Spring Data JPA**, garantendo modularità e facilità di manutenzione.

- **Infrastruttura**
  La classe `APIGateway` funge da punto di ingresso unico del sistema, smistando le richieste (tramite **DTO**) verso i controller specifici dei diversi microservizi.


### 3.1.5 Diagramma di Sequenza

Il **Diagramma di Sequenza** illustra la dinamica temporale di un comando utente, mostrando come i microservizi collaborano per processare una frase in linguaggio naturale.

- **Trigger**
  L’utente invia un comando (testuale o vocale) all’**API Gateway**.

- **Preprocessing (Opzionale)**
  Se l’input è vocale, il Gateway interroga il servizio **Cloud STT** per ottenere la trascrizione in formato testuale.

- **Parsing NLU**
  Il testo viene inviato al **NLU Service**, che tramite l’algoritmo di parsing:
  - estrae l’intento (es. *“Aggiungi Spesa”*)
  - identifica i dati rilevanti (es. *“20€”*, *“Cibo”*)

- **Esecuzione**
  Una volta validato l’intento e i parametri estratti, il Gateway inoltra la richiesta strutturata:
  - all’**Accounting Service**, per salvare la transazione nel database
  - oppure all’**Analytics Service**, per il calcolo di report e statistiche

- **Feedback**
  Il sistema restituisce una risposta di conferma in formato **JSON**, visualizzando l’esito dell’operazione sulla **GUI**.
