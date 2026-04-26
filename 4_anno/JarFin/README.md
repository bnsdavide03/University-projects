# Jarfin  
### Assistente Conversazionale per la Contabilità Personale

Jarfin è un **assistente conversazionale intelligente** per la gestione della contabilità personale, progettato secondo un’**architettura a microservizi** e dotato di un modulo proprietario di **Natural Language Understanding (NLU)**.

A differenza delle tradizionali applicazioni finanziarie basate su form strutturati, Jarfin consente all’utente di interagire in modo **naturale**, tramite **input testuale o vocale**, per registrare transazioni, consultare statistiche e ottenere report finanziari in tempo reale.

&nbsp;

# 🎯 Obiettivi e Funzionalità

Jarfin permette di:

### ✔ Registrare spese ed entrate con linguaggio naturale
> "Segna che ho speso 20 euro al supermercato per la spesa settimanale"

### ✔ Consultare lo stato finanziario
> "Quanto ho speso questa settimana?"

### ✔ Ottenere report e analisi avanzate
- Totali mensili e settimanali
- Breakdown per categorie
- Proiezioni di spesa
- Calcolo del savings rate
- Livello di rischio finanziario

Il progetto nasce come **caso di studio accademico**, sviluppato seguendo metodologie **Agile (SCRUM)** e modellazione **UML**.

&nbsp;

# 🏗 Architettura del Sistema

Jarfin è strutturato come un ecosistema di **microservizi indipendenti**, orchestrati tramite un **API Gateway**.

## Componenti principali

- **Web UI**
  - Interfaccia Thymeleaf
  - Supporto input vocale tramite Web Speech API
  - Porta: `8083`

- **API Gateway**
  - Punto di ingresso unico
  - Routing e sicurezza
  - Porta: `8080`

- **NLU Service**
  - Analisi del linguaggio naturale
  - Estrazione intenti e parametri

- **Accounting Service**
  - CRUD transazioni
  - Persistenza dati

- **Analytics Service**
  - Aggregazione dati
  - Calcolo metriche finanziarie
  - Generazione report

- **Database**
  - PostgreSQL containerizzato

&nbsp;

# 🧠 Algoritmi Principali

## 1. Natural Language Parser (NLU)

Trasforma una frase naturale non strutturata in un comando finanziario strutturato.

### Input
```
Ho speso 15 euro e 50 al bar
```

### Output
```json
{
  "azione": "EXPENSE",
  "importo": 15.50,
  "categoria": "Bar",
  "data": "2026-02-14"
}
```

### Tecnica utilizzata
- Keyword matching
- Euristiche posizionali
- Estrazione tramite espressioni regolari (Regex)

### Complessità computazionale
**O(n)** rispetto alla lunghezza del testo in input.



## 2. Aggregatore Analitico

Elabora lo storico delle transazioni per produrre indicatori finanziari.

### Output esempio
```json
{
  "totale_spese": 320.50,
  "saldo_netto": 179.50,
  "savings_rate": "15%",
  "alert_level": "GREEN"
}
```

### Tecnica utilizzata
- Aggregazione lineare
- Calcolo proiezioni temporali
- Classificazione del rischio

### Complessità computazionale
**O(n)** rispetto al numero di transazioni nel periodo analizzato.

&nbsp;

# 🚀 Avvio del Progetto

Il sistema è distribuito: è necessario avviare prima il database e successivamente i servizi.

## 📋 Prerequisiti

- Java 21 (JDK)
- Maven
- Docker Desktop



## 1️⃣ Avvio Database

```bash
cd Iterazione_1/jarfin-accounting
docker compose up -d
```

Verificare che il container `jarfin_db` sia attivo.



## 2️⃣ Build del Progetto

```bash
mvn clean install -DskipTests
```


## 3️⃣ Avvio Microservizi

### Metodo consigliato – IDE (IntelliJ / Eclipse)

Avviare le classi `Application` nel seguente ordine:

1. AccountingServiceApplication  
2. AnalyticsServiceApplication  
3. GatewayApplication
4. WebUiApplication  


### Metodo da terminale

Aprire quattro terminali separati:

```
T1: cd jarfin-gateway     → mvn spring-boot:run
T2: cd jarfin-accounting  → mvn spring-boot:run
T3: cd jarfin-analytics   → mvn spring-boot:run
T4: cd jarfin-web-ui      → mvn spring-boot:run
```


## 🌐 Accesso Applicazione

Una volta avviati tutti i servizi:

```
http://localhost:8083
```

&nbsp;

# 🛠 Stack Tecnologico

| Categoria | Tecnologia | Scopo |
|------------|------------|------------|
| Backend | Java 21 LTS | Linguaggio principale |
|  | Spring Boot 3.2 | Framework microservizi |
|  | Spring Cloud Gateway | Routing centralizzato |
| Frontend | Thymeleaf | Template engine |
|  | Bootstrap 5 | UI responsiva |
|  | Web Speech API | Speech-to-Text |
| Database | PostgreSQL 15 | Persistenza dati |
| DevOps | Docker & Compose | Containerizzazione |
| Testing | JUnit 5 | Unit Testing |
|  | Mockito | Mocking |
|  | Postman | API Testing |
| Qualità | EclEmma | Code Coverage |
|  | CodeMR | Analisi architetturale |
|  | SonarLint | Analisi statica |
| Logging | SLF4J | Logging centralizzato |
| Design | UMLet | Modellazione UML |
| PM | GitHub Projects | Agile / Kanban |

&nbsp;

# 📊 Stato Attuale del Progetto e Qualità del Codice

Il sistema ha raggiunto un elevato livello di stabilità, verificato tramite analisi statica e dinamica.

| Indicatore | Risultato |
|------------|------------|
| **Test Coverage** | **95.3%** (EclEmma) |
| **Unit Test** | **48 / 48** test superati (JUnit 5 + Mockito) |
| **Copertura Javadoc** | **100%** su classi e metodi |
| **Qualità Architetturale** | Nessun ciclo critico, basso accoppiamento (CodeMR) |
| **Analisi Statica** | Nessun code smell critico (SonarLint) |

Il progetto dimostra un’elevata attenzione alla **manutenibilità, modularità e testabilità**.

&nbsp;

# 👥 Team

Progetto sviluppato per il corso di **Progettazione Algoritmi e Computabilità**

- Davide Bonsembiante  
- Alessandro Biscaro  
- Alessandro Rocco  

&nbsp;

# 📄 Licenza

Progetto sviluppato a fini accademici.
