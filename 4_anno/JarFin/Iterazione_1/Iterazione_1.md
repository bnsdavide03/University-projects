# Iterazione 1 – Accounting Service

## Introduzione generale

L’Iterazione 1 del progetto **JarFin** è stata dedicata alla progettazione e allo sviluppo del microservizio **Accounting Service**. Questo servizio rappresenta uno dei componenti fondamentali dell’architettura complessiva del sistema, in quanto è responsabile della gestione delle transazioni finanziarie, ovvero la registrazione, la consultazione e la cancellazione delle operazioni economiche effettuate dall’utente.

L’obiettivo principale di questa iterazione non è stato solo “far funzionare” il servizio, ma costruirlo seguendo criteri di buona progettazione software, in modo che il codice risultasse chiaro, manutenibile, estendibile e pronto per essere integrato, nelle iterazioni successive, con altri microservizi (in particolare l’Analytics Service).

Per questo motivo sono state adottate diverse scelte architetturali e implementative che verranno spiegate in dettaglio nelle sezioni successive.



## Scelte tecnologiche

Il microservizio è stato sviluppato utilizzando **Java 21** e **Spring Boot 3.2.0**, che rappresentano una combinazione moderna e ampiamente utilizzata per lo sviluppo di applicazioni backend e microservizi. Spring Boot consente di ridurre la configurazione manuale, fornendo un’infrastruttura pronta all’uso per la creazione di API REST e per l’integrazione con database relazionali.

Per l’esposizione delle API è stato utilizzato **Spring Web**, mentre la persistenza dei dati è stata gestita tramite **Spring Data JPA** e **Hibernate**, che permettono di mappare le classi Java su tabelle di database senza scrivere query SQL esplicite nella maggior parte dei casi.

Il database scelto è **PostgreSQL**, eseguito all’interno di un container Docker. L’uso di Docker garantisce un ambiente di sviluppo riproducibile e coerente tra diversi sviluppatori e sistemi operativi.

A supporto dello sviluppo sono state utilizzate anche:

* **Lombok**, per ridurre il codice boilerplate (getter, setter, costruttori)
* **Jakarta Validation**, per validare i dati in ingresso alle API
* **Maven**, per la gestione delle dipendenze e del ciclo di build



## Architettura del microservizio

L’Accounting Service è stato progettato seguendo una classica architettura a strati, che separa in modo chiaro le responsabilità delle diverse componenti. Questa scelta è fondamentale per evitare l’accoppiamento eccessivo tra parti del codice e per facilitare l’evoluzione futura del sistema.

Il flusso principale delle richieste è il seguente:

Controller → Service → Repository → Database

Il **Controller** si occupa esclusivamente della gestione delle richieste HTTP e della costruzione delle risposte. Non contiene logica di business.

Il **Service** rappresenta il cuore applicativo del microservizio: qui risiede la logica di dominio e vengono effettuati i controlli applicativi (ad esempio verificare l’esistenza di una transazione prima di eliminarla).

Il **Repository** incapsula l’accesso ai dati e delega a Spring Data JPA la gestione delle operazioni CRUD.

Questa separazione rende il codice più leggibile, più semplice da testare e più resistente ai cambiamenti.



## Configurazione del database e Docker

Il database PostgreSQL viene avviato tramite un file `docker-compose.yml`. Questa scelta permette di evitare installazioni manuali del database sulla macchina locale e garantisce che l’ambiente di sviluppo sia sempre coerente.

Nel file di configurazione vengono definiti:

* l’immagine Docker di PostgreSQL
* le credenziali di accesso
* il nome del database
* un volume persistente per i dati

Le credenziali del database non sono fissate rigidamente nel codice, ma vengono lette tramite variabili d’ambiente, con valori di default utilizzabili in fase di sviluppo. Questo approccio è importante perché separa la configurazione sensibile dal codice applicativo e rende il servizio più sicuro e facilmente distribuibile in ambienti diversi.



## Gestione delle dipendenze (pom.xml)

Il file `pom.xml` definisce tutte le dipendenze necessarie al funzionamento del microservizio. L’uso dello starter parent di Spring Boot garantisce la compatibilità tra le versioni delle librerie.

Le dipendenze principali includono:

* lo starter web per la creazione di API REST
* lo starter JPA per la persistenza
* il driver PostgreSQL per la connessione al database
* Lombok per ridurre il codice ripetitivo
* lo starter per la validazione degli input

La versione di Java è impostata a 21, in linea con le versioni più recenti di Spring Boot.



## Modellazione del dominio: Transaction

La classe `Transaction` rappresenta il concetto centrale del dominio dell’Accounting Service. Ogni istanza corrisponde a una transazione finanziaria salvata nel database.

Una scelta particolarmente importante è stata l’adozione di **BigDecimal** per rappresentare l’importo (`amount`) della transazione. L’uso di tipi floating-point come `Double` può introdurre errori di precisione nei calcoli monetari, che in un contesto finanziario sono inaccettabili. BigDecimal garantisce invece una rappresentazione esatta dei valori decimali.

La classe è annotata come entità JPA e viene mappata alla tabella `transactions`. L’identificatore è generato automaticamente dal database.

I metodi `equals` e `hashCode` sono stati implementati manualmente basandosi sull’identificatore. Questa scelta evita problemi noti legati all’uso automatico di `equals` e `hashCode` con entità JPA e proxy Hibernate, rendendo il comportamento dell’entità più prevedibile.



## Repository

Il repository `TransactionRepository` estende `JpaRepository`. In questo modo Spring Data JPA fornisce automaticamente tutte le operazioni CRUD di base senza la necessità di scrivere query esplicite.

Questa scelta riduce il codice necessario e permette di concentrarsi sulla logica di business piuttosto che sull’accesso ai dati.



## Uso dei DTO e separazione dell’API

Un aspetto fondamentale dell’iterazione è l’introduzione dei **DTO (Data Transfer Object)**. Le entità JPA non vengono esposte direttamente tramite le API REST, ma vengono utilizzati oggetti dedicati per l’input e l’output.

Il `TransactionRequest` rappresenta i dati necessari per creare una nuova transazione. Non contiene l’ID, in modo da impedire che un client possa forzare l’aggiornamento di una transazione esistente tramite una richiesta POST.

Il `TransactionResponse` rappresenta invece i dati restituiti al client. Questa separazione consente di modificare internamente l’entità senza impattare il contratto dell’API.

La validazione degli input viene effettuata direttamente sui DTO tramite annotazioni di Jakarta Validation. In questo modo i dati errati vengono intercettati prima di raggiungere il livello di business.



## Mapper

La conversione tra DTO ed entità è gestita dalla classe `TransactionMapper`. Questa scelta evita di inserire logica di mapping all’interno del controller o del service, mantenendo il codice più pulito e organizzato.

Centralizzare il mapping rende inoltre più semplice modificare la struttura dei DTO o dell’entità in futuro.



## Service layer e logica di business

Il `TransactionService` rappresenta il livello in cui risiede la logica applicativa. Qui vengono gestite le operazioni di salvataggio, recupero ed eliminazione delle transazioni.

Prima di eliminare una transazione viene verificata l’esistenza dell’ID. In caso contrario viene sollevata un’eccezione, evitando operazioni inconsistenti sul database.

È stato inoltre introdotto il logging per tracciare le operazioni principali. Il logging è uno strumento fondamentale per il debug e per il monitoraggio del comportamento del servizio.



## Controller REST

Il `TransactionController` espone le API REST del microservizio. Il controller si limita a:

* ricevere le richieste HTTP
* validare i dati in ingresso
* delegare la logica al service
* restituire le risposte HTTP appropriate

Ogni endpoint restituisce uno status code coerente con l’operazione eseguita (201 per la creazione, 200 per la lettura, 204 per la cancellazione).



## Gestione centralizzata delle eccezioni

La classe `GlobalExceptionHandler` consente di intercettare le eccezioni a livello globale e di trasformarle in risposte HTTP significative.

Le eccezioni di validazione producono una risposta con codice 400 e una descrizione dettagliata degli errori, mentre le richieste a risorse inesistenti producono una risposta 404.

Questo approccio migliora l’esperienza del client e rende il comportamento dell’API più prevedibile.



## Testing e Quality Assurance

La qualità del software sviluppato nell’Iterazione 1 è stata affrontata considerando sia l’affidabilità della logica di business sia il corretto comportamento dell’API REST esposta. In questa fase del progetto, l’obiettivo principale non è stato raggiungere una copertura di test esaustiva, ma garantire che i flussi critici del servizio di accounting funzionassero correttamente e in modo prevedibile, riducendo il rischio di regressioni nelle iterazioni successive.

### Unit Testing (JUnit 5 e Mockito)

Il livello di unit testing è stato concentrato sul **service layer**, in particolare sulla classe `TransactionService`, poiché rappresenta il cuore della logica applicativa e costituisce il punto di intersezione tra il controller REST e il layer di persistenza.

I test unitari sono stati progettati per essere **completamente isolati dal database**. A questo scopo è stato utilizzato **Mockito** per simulare (`mock`) il comportamento di `TransactionRepository`. Questa scelta consente di verificare la correttezza della logica di business senza introdurre dipendenze esterne, rendendo i test più veloci, deterministici e facili da manutenere.

In particolare, il mocking del repository permette di:

* simulare il salvataggio di una transazione senza eseguire query reali;
* controllare esplicitamente i valori restituiti dal repository;
* verificare il comportamento del servizio in presenza di condizioni di errore, difficilmente riproducibili in modo affidabile su un database reale.

I casi di test implementati coprono:

* **Scenari di successo**, come il salvataggio di una nuova transazione e il recupero della lista delle transazioni;
* **Scenari di errore**, in particolare il tentativo di eliminare una transazione inesistente, verificando il corretto lancio dell’eccezione `EntityNotFoundException`.

Questo approccio consente di validare che il service layer applichi correttamente le regole di business indipendentemente dall’infrastruttura sottostante, seguendo le buone pratiche del testing a piramide.

### Test Manuali e di Integrazione (Postman)

Accanto ai test unitari, sono stati effettuati test manuali di tipo **black-box** utilizzando **Postman**, con l’obiettivo di validare il comportamento complessivo dell’applicazione dal punto di vista di un client esterno.

Questa fase di test ha permesso di verificare:

* la corretta esposizione degli endpoint REST;
* il rispetto dei codici di stato HTTP;
* l’integrazione reale tra controller, service, repository e database PostgreSQL.

In particolare, sono stati testati i seguenti aspetti:

* **Creazione di una risorsa** tramite endpoint `POST /api/transactions`, verificando la restituzione dello status code `201 Created` e del payload corretto;
* **Sicurezza dei DTO**, simulando richieste contenenti campi non previsti (come un `id` forzato dal client) e verificando che tali valori vengano ignorati dal sistema;
* **Persistenza effettiva dei dati**, controllando che le transazioni create siano realmente salvate nel database PostgreSQL e recuperabili tramite l’endpoint `GET`.

I test di integrazione manuali hanno svolto un ruolo fondamentale nel confermare che le scelte architetturali (DTO, mapper, validazione, gestione delle eccezioni) funzionino correttamente quando il sistema viene utilizzato come servizio REST reale.

Nel complesso, la combinazione di unit test isolati e test di integrazione manuali fornisce una base solida di quality assurance per l’Iterazione 1, preparando il progetto alle successive estensioni funzionali e all’integrazione con l’`analytics-service`.



## Conclusione dell’iterazione

Al termine dell’Iterazione 1, l’Accounting Service risulta completamente funzionante e progettato secondo buone pratiche di sviluppo software. Il servizio è pronto per essere esteso e integrato con altri microservizi, in particolare con l’Analytics Service, che verrà affrontato nelle iterazioni successive.