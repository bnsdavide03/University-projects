# **Organizzazione Progetto – Modello SCRUM**

## **Indice**
1. [Sprint Planning](#sprint-planning)
2. [Backlog del Prodotto](#backlog-del-prodotto)
3. [Sprint n°1 – Fase di Sviluppo Iniziale](#sprint-n1--23122025---04022026)
4. [Variazione Organizzativa – Fase Finale](#variazione-organizzativa--fase-finale)
5. [Sprint n°2 – Qualità e Documentazione](#sprint-n2--06022026---12022026)
6. [Sprint n°3 – Chiusura e Presentazione](#sprint-n3--13022026---15022026)

&nbsp;

## **Sprint Planning**
*Pianificazione generale delle attività.*

- **Sprint Totali**: 3  
- **Durata Standard Sprint**: 2 settimane  
- **Variazione Finale**: Sprint settimanali (vedi Cap. 4)  
- **Obiettivo Globale**: Realizzare un assistente finanziario a microservizi (*JarFin*) completo, testato e documentato.

&nbsp;

## **Backlog del Prodotto**
*Lista prioritaria delle funzionalità da implementare.*

| ID | Titolo | Descrizione | Priorità |
|----|--------|-------------|----------|
| **1** | **Accounting Service & DB** | Configurazione Docker (PostgreSQL) e sviluppo CRUD delle transazioni. | **Alta** |
| **2** | **Analytics Service** | Aggregazioni, proiezioni di spesa e tasso di risparmio. | **Alta** |
| **3** | **API Gateway** | Instradamento centralizzato delle richieste. | **Media** |
| **4** | **Web Dashboard UI** | Interfaccia web Thymeleaf/Bootstrap. | **Media** |
| **5** | **NLU Engine** | Parser semantico per input in linguaggio naturale. | **Media** |
| **6** | **Voice Assistant (AI)** | Web Speech API con Wake Word e TTS. | **Bassa** |
| **7** | **Quality Assurance** | Analisi statica, dinamica e test coverage. | **Alta** |
| **8** | **Documentazione & Release** | Documentazione LaTeX e presentazione finale. | **Alta** |

&nbsp;

# **Sprint n°1: 23/12/2025 – 04/02/2026**
*Fase di sviluppo iniziale e completamento funzionalità core*

## **Ruoli del Team**

- **Product Owner / Scrum Master**: Davide Bonsembiante  
- **Team di Sviluppo**:  
  - Davide Bonsembiante  
  - Alessandro Biscaro  
  - Alessandro Rocco  

&nbsp;

## **1° Week Scrum – 23 Dicembre 2025**
*Focus: Analisi e Progettazione (Iterazione 0)*

| Titolo | Compiti | Sviluppatore |
|------|--------|--------------|
| Use Case Diagram | Modellazione dei casi d’uso e delle interazioni utente–sistema | Davide Bonsembiante & Alessandro Biscaro |
| Component Diagram | Modellazione dei componenti software e delle loro dipendenze | Alessandro Rocco |
| Sequence Diagram | Modellazione dei flussi di interazione tra microservizi | Davide Bonsembiante |
| Class Diagram | Modellazione delle entità di dominio, DTO e relazioni | Alessandro Rocco |
| Deployment Diagram | Definizione dell’architettura fisica e del deployment dei microservizi | Alessandro Biscaro |
| Documentazione Iterazione 0 | Redazione della documentazione tecnica iniziale | Tutti |

&nbsp;

## ⚠️ **Interruzione del Progetto**

Il progetto è stato sospeso fino al **29 Gennaio 2026** a causa della sessione invernale.

Dal **30 Gennaio al 4 Febbraio 2026**, al fine di rispettare la consegna del **15 Febbraio**, **Davide Bonsembiante** ha proseguito autonomamente lo sviluppo, mentre **Alessandro Biscaro** e **Alessandro Rocco** erano impegnati nel recupero dell’esame di *MAO* fino al 6 febbraio.

&nbsp;

## **Ripresa Sprint – Sviluppo Autonomo**

### **1° Day Scrum – 30 Gennaio 2026**
*Iterazione 1 – Accounting Service*

- Configurazione Docker e PostgreSQL  
- Implementazione Entity, Repository e CRUD REST  
- Documentazione Iterazione 1  

### **2° Day Scrum – 01 Febbraio 2026**
*Iterazione 2 – Analytics Service*

- Aggregazioni e proiezioni di spesa  
- Calcolo tasso di risparmio  
- Refactoring Accounting Service  
- Documentazione Iterazione 2  

### **3° Day Scrum – 02 Febbraio 2026**
*Iterazione 3 – Integrazione*

- API Gateway  
- Web Dashboard UI  
- NLU Engine  
- Voice Assistant  
- Documentazione Iterazione 3  

### **4° Day Scrum – 03–04 Febbraio 2026**
*Chiusura Fase di Sviluppo*

- Refactoring generale
- Test End-to-End
- Aggiornamento backlog

&nbsp;

# **Variazione Organizzativa – Fase Finale**

**Periodo: 06 Febbraio – 15 Febbraio 2026**

Con il rientro operativo di **Alessandro Biscaro** e **Alessandro Rocco**, l’organizzazione SCRUM viene adattata per la fase finale:

- **Sprint settimanali**
- **1 Week Scrum per ogni Sprint**
- Focus su **qualità, testing, documentazione e presentazione**
- Task assegnati prevalentemente ai membri rientrati per equità del carico di lavoro

&nbsp;

# **Sprint n°2: 06/02/2026 – 11/02/2026**
***Focus: Quality Assurance e Documentazione***

## **Week Scrum – 06 Febbraio 2026**

| Titolo | Compiti | Sviluppatore |
|------|--------|--------------|
| Analisi Statica & Test | Revisione test JUnit e analisi statica | Alessandro Rocco |
| Code Coverage | Analisi copertura con EclEmma e refactoring | Alessandro Rocco |
| Analisi Dinamica | Analisi architetturale con Stan4J | Alessandro Rocco |
| Revisione GUI | Miglioramento UX/UI Dashboard | Alessandro Biscaro |
| Aggiornamento Diagrammi | Correzione UML (Class, Sequence, Deployment) | Alessandro Biscaro |
| Calcolo Computazionale | Calcolo degli algoritmi Analytics e Parser NLU| Davide Bonsembiante |
| Documentazione LaTeX | Stesura documentazione tecnica | Tutti (coord. Davide) |

&nbsp;

# **Sprint n°3: 12/02/2026 – 15/02/2026**
***Focus: Chiusura Progetto e Presentazione***

## **Week Scrum – 13 Febbraio 2026**

| Titolo | Compiti | Sviluppatore |
|------|--------|--------------|
| Chiusura Documentazione | Finalizzazione PDF LaTeX | Tutti |
| Presentazione | Creazione slide e demo | Tutti |
| Review Finale | Verifica requisiti e consegna | Tutti |
