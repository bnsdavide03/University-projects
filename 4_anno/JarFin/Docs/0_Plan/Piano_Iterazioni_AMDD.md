# **Piano delle Iterazioni AMDD – Jarfin**

## **1. Modello di Processo: SCRUM + AMDD**

Il progetto **Jarfin** adotta un modello **SCRUM** integrato con i principi di **Agile Model Driven Development (AMDD)**. Lo sviluppo è iterativo e incrementale, con modellazione leggera iniziale e continuo affinamento dei modelli durante le iterazioni.

### **Durata degli Sprint**

Gli **sprint hanno una durata standard di 2 settimane**, scelta che rappresenta un compromesso efficace tra produttività e impegni accademici.
*Nota: La durata è flessibile e può subire variazioni in caso di sovrapposizione con le sessioni d'esame.*

### **Riunioni e Cerimonie**

* **Sprint Planning (inizio sprint)**: Definizione obiettivi, selezione dei task e stima priorità.
* **Riunione settimanale di sincronizzazione (Scrum Week)** – ~1 ora:
* Monitoraggio avanzamento lavori.
* Identificazione e risoluzione degli impedimenti.
* Riorganizzazione dei task in base alle priorità.
* *Aggiornamenti asincroni:* Durante la settimana, il team si aggiorna quotidianamente tramite chat (WhatsApp) per segnalare completamento task o blocchi (Daily Stand-up asincrono).


* **Sprint Review & Retrospective (fine sprint)**:
* Valutazione dell’incremento prodotto.
* Analisi di cosa ha funzionato e cosa migliorare.


### **Definition of Done (DoD)**

Un task si considera concluso quando:

1. Il codice è committato sul repository.
2. I test unitari (dove previsti) sono superati.
3. È stata effettuata una Code Review interna.

&nbsp;

## **2. Organizzazione del Team SCRUM**

Il progetto è sviluppato da un **team di tre membri**, che gestisce l’intero ciclo di vita del software (analisi, progettazione, sviluppo, test e documentazione).

### **Membri del Team**

* **Davide Bonsembiante**
* **Alessandro Biscaro**
* **Alessandro Rocco**

### **Ruoli e Responsabilità**

* **Scrum Master a rotazione:** Il ruolo viene ricoperto a turno da ciascun membro. Questo favorisce la comprensione condivisa del processo SCRUM e lo sviluppo di competenze organizzative.
* **Product Owner condiviso:** Il ruolo è condiviso tra tutti i membri. Le decisioni su backlog, priorità e funzionalità vengono prese collegialmente, garantendo una visione comune del prodotto.
* **Team di sviluppo:** Tutti i membri partecipano attivamente allo sviluppo del codice. Non esistono ruoli rigidi, ma aree di maggiore competenza che possono variare nel tempo (Backend, Analytics, Integrazione, Testing).
* **Tester:** Ogni membro svolge anche attività di testing (Manuale e Unit Testing). In assenza di clienti esterni, il team assume il ruolo di utente finale per garantire feedback continuo.

&nbsp;

## **3. Gestione dei Task e Collaborazione**

* Tutti i task sono tracciati tramite **GitHub Project Board (Kanban)** con stati: *To Do, In Progress, Review, Done*.
* I task sono suddivisi in modo equo tra i membri, mantenendo flessibilità e collaborazione continua.

### **Gestione del Codice**

* Ogni sviluppatore lavora su un **branch dedicato**.
* Prima di ogni **Scrum Week**, il codice deve essere pushato sul branch personale.
* L’integrazione nel branch principale avviene solo dopo **revisione collettiva** tramite Pull Request.
* La revisione include una valutazione qualitativa e consente di individuare e risolvere rapidamente eventuali problemi.

&nbsp;

## **4. Best Practices Agile adottate**

* **Sviluppo Iterativo**: Ogni sprint produce funzionalità complete e testabili.
* **Pair Programming**: Utilizzato per componenti complessi (es. Parser NLU e algoritmi di aggregazione).
* **Test-first mindset**: Scrittura di test unitari e test API prima o in parallelo allo sviluppo.
* **Code Review obbligatoria**: Nessun commit diretto su `main`.
* **Feedback continuo**: Il team testa il prodotto come utente finale a ogni incremento.

&nbsp;

## **5. Toolchain di Progetto**

| Area | Strumento | Scopo |
| --- | --- | --- |
| **Modellazione** | UMLet | Diagrammi UML (Use Case, Classi, Componenti, Sequenza) |
| **Versioning & PM** | GitHub | Repository, Kanban board, Pull Request |
| **Backend** | Java + Spring Boot | Microservizi Jarfin |
| **Testing API** | Postman | Verifica endpoint REST |
| **Unit Testing** | JUnit 5 | Test della logica applicativa |
| **Code Coverage** | EclEmma | Analisi dinamica dei test |
| **Analisi Statica** | STAN4J | Metriche di qualità del codice |

&nbsp;

## **6. Piano delle Iterazioni (Roadmap AMDD)**

### **Iterazione 0 – Envisioning & Setup**

**Obiettivo:** Definire le basi del progetto.

* Raccolta requisiti funzionali e non funzionali.
* Definizione dei casi d’uso.
* Progettazione architettura a microservizi (JarFin).
* Setup repository GitHub, board SCRUM e toolchain.
**Output:** Documentazione analisi requisiti e architetturale iniziale.

### **Iterazione 1 – Core Contabilità**

**Obiettivo:** Realizzare il cuore funzionale di Jarfin.

* Implementazione microservizio di **Contabilità**.
* API CRUD per entrate e spese.
* Configurazione e integrazione database (PostgreSQL).
* Test unitari del service layer.
* Test manuali delle API con Postman.
**Output:** Gestione dati finanziari funzionante.

### **Iterazione 2 – Analytics & Reporting**

**Obiettivo:** Elaborazione e analisi dei dati finanziari.

* Implementazione microservizio **Analytics**.
* Sviluppo Algoritmo di aggregazione dati (totali, categorie, proiezioni).
* Analisi della complessità computazionale.
* Validazione con dataset di test.
**Output:** Report finanziari in formato JSON.

### **Iterazione 3 – NLU, Integrazione & Rilascio**

**Obiettivo:** Interazione naturale e completamento del sistema.

* Implementazione microservizio **NLU**.
* Parser NLU per estrazione intenti e parametri.
* Integrazione Speech-to-Text e TTS (Voice AI).
* Orchestrazione tramite API Gateway.
* Analisi statica e dinamica finale.
* Aggiornamento UML allo stato *as-built*.
**Output:** Sistema Jarfin completo, integrato e pronto al rilascio.

&nbsp;

## **7. Pacchetti di Lavoro**

Il progetto è suddiviso in **macro-pacchetti di lavoro**:

1. Analisi e requisiti
2. Architettura e modellazione UML
3. Core Contabilità
4. Analytics & Algoritmi
5. NLU e integrazione esterna
6. Testing e qualità
7. Documentazione

&nbsp;

## **8. Gestione dei Rischi**

Sono stati identificati e gestiti i seguenti rischi di progetto:

| Rischio | Impatto | Strategia di Mitigazione |
| --- | --- | --- |
| **Sessione Esami** | Ritardi nella tabella di marcia | Sospensione pianificata delle attività e ridistribuzione del carico nei periodi liberi. |
| **Tecnologie Nuove** | Curva di apprendimento | Pair programming e studio preliminare nell'Iterazione 0. |