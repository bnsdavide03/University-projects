# Iterazione 2 – Analytics Service

## Introduzione generale

L’Iterazione 2 del progetto **JarFin** ha avuto come obiettivo la progettazione e l’implementazione dell’**Analytics Service**. Questo microservizio ricopre un ruolo cruciale nell’architettura del sistema: agisce come componente di *Business Intelligence*, trasformando i dati grezzi delle transazioni (gestiti dall'Accounting Service) in informazioni finanziarie strutturate e azionabili per l'utente finale.

Il servizio non si limita a una passiva aggregazione di dati, ma applica algoritmi di proiezione e logiche di valutazione per fornire un quadro completo della salute finanziaria (bilanci, stime di fine mese, livelli di allerta).
L'intero sviluppo è stato guidato da principi principi di **Design for Testability** e una modellazione del dominio ispirata a **DDD**, con particolare attenzione alla semantica dei tipi e alla chiarezza del modello.



## Scelte tecnologiche

In perfetta continuità con l’Iterazione 1, lo stack tecnologico si basa su **Java 21** e **Spring Boot 3.2.0**. Questa scelta garantisce omogeneità nell’ecosistema JarFin, semplificando la manutenzione e il deployment.

Per soddisfare i requisiti specifici di un servizio "consumatore" di dati, sono state adottate le seguenti tecnologie:

* **Spring Web**: Utilizzato per esporre le API REST che forniscono i report al client.
* **Spring RestTemplate**: Scelto come client HTTP sincrono per l’interrogazione dell'Accounting Service. È stata preferita una configurazione esplicita via `@Bean` per permettere una gestione centralizzata di timeout e configurazioni di rete.
* **Lombok**: Impiegato estensivamente per l'eliminazione del codice boilerplate. In particolare, l'annotazione `@RequiredArgsConstructor` è stata fondamentale per implementare la *Constructor Injection*, promuovendo l'immutabilità dei componenti Service e Controller.
* **JUnit 5 e Mockito**: Fondamentali per la strategia di testing. Dato che l'Analytics Service dipende da dati esterni, Mockito è stato essenziale per simulare le risposte di rete e testare la logica di business in isolamento.



## Architettura e Integrazione tra Microservizi

L’Analytics Service è progettato come un microservizio **Stateless** (senza stato): non possiede un database proprietario per la persistenza delle transazioni, ma elabora i dati in tempo reale recuperandoli dalla fonte di verità (l'Accounting Service).

### Pattern di Comunicazione
Il flusso dei dati segue un pattern di richiesta/risposta sincrono:
1.  Il **Client** richiede un report all'Analytics Service.
2.  L'**Analytics Service** invoca l’endpoint dell’Accounting Service, configurato esternamente, `/api/transactions` tramite `RestTemplate`.
3.  I dati JSON ricevuti vengono deserializzati in oggetti di dominio (DTO).
4.  Il motore di calcolo elabora le metriche.
5.  Il risultato viene restituito al Client.

### Configurazione (12-Factor App)
Per garantire la portabilità del servizio tra ambienti diversi (Sviluppo locale, Docker, Produzione), è stato applicato rigorosamente il principio di separazione della configurazione dal codice.
L'URL del servizio Accounting non è definito staticamente nelle classi Java, ma è iniettato tramite la proprietà `${ACCOUNTING_SERVICE_URL}` nel file `application.properties`.
Ciò consente, ad esempio, di passare da `http://localhost:8080` a `http://accounting-service:8080` (in una rete Docker interna) senza ricompilare l'applicazione.



## Modellazione del Dominio e Type Safety

Una delle decisioni progettuali più importanti di questa iterazione è stata l'adozione di un modello di dominio fortemente tipizzato ("Type Safe") e semanticamente inequivocabile.

### Gestione dei Tipi di Transazione (`TransactionType`)
Per evitare ambiguità nella distinzione tra entrate e uscite, il sistema rifiuta l'uso di convenzioni basate sul segno algebrico (es. "i numeri negativi sono spese"). È stato invece implementato un approccio esplicito basato su un Enum condiviso logicamente:

* **`TransactionType.INCOME`**: Identifica inequivocabilmente un'entrata.
* **`TransactionType.EXPENSE`**: Identifica inequivocabilmente un'uscita.

Questo approccio migliora la leggibilità del codice e la robustezza del sistema, prevenendo errori logici dovuti a conversioni di segno errate.

### Precisione Numerica (`BigDecimal`)
Trattandosi di un'applicazione finanziaria, l'uso dei tipi primitivi `double` o `float` è stato bandito per evitare i noti problemi di approssimazione della virgola mobile (standard IEEE 754).
Tutti gli importi monetari (`amount`, `totalBalance`, etc.) sono gestiti tramite la classe `java.math.BigDecimal`, garantendo una precisione decimale assoluta nelle operazioni di somma, sottrazione e divisione.

### Data Transfer Objects (DTO)

* **`TransactionDTO`**: Oggetto utilizzato per mappare la risposta dell'Accounting Service. Contiene l'importo (`BigDecimal`), la data (`LocalDate`), la categoria e, crucialmente, il tipo (`TransactionType`).
* **`FinancialReportDTO`**: Oggetto complesso restituito al client. Oltre ai totali numerici, incapsula informazioni qualitative come il livello di allerta (`alertLevel`) e i consigli finanziari (`financialAdvice`), disaccoppiando la logica di presentazione dai calcoli grezzi.



## Logica di Business e Algoritmi

La classe `AnalyticsService` rappresenta il cuore funzionale dell'applicazione. Le logiche implementate non si limitano all'aritmetica di base, ma includono algoritmi di proiezione deterministica basati sull’andamento temporale delle spese.

### 1. Aggregazione Condizionale
L'algoritmo di aggregazione itera sulle transazioni e applica una logica di smistamento basata sul tipo:
```java
if (transazione.getType() == TransactionType.INCOME) {
    // Incrementa Entrate
} else if (transazione.getType() == TransactionType.EXPENSE) {
    // Incrementa Uscite
    // Aggiorna mappa categorie
}

```

Le spese vengono inoltre raggruppate dinamicamente in una mappa `Map<String, BigDecimal>`, fornendo un breakdown dettagliato per categoria (es. "Cibo", "Utenze").

### 2. Proiezione Temporale Adattiva

Il servizio calcola una stima delle spese previste a fine mese (`projectedMonthlyExpenses`). Per massimizzare la precisione, l'algoritmo non utilizza costanti fisse (es. 30 giorni), ma calcola la durata esatta del mese corrente utilizzando le API di `java.time` (`LocalDate.lengthOfMonth()`).

La formula implementata è:
$$ \text{Proiezione} = \left( \frac{\text{Spese Totali}}{\text{Giorno Corrente}} \right) \times \text{Giorni Totali nel Mese} $$

Questo garantisce che la proiezione sia accurata sia a Febbraio (28/29 giorni) che nei mesi da 30 o 31 giorni.

### 3. Valutazione della Salute Finanziaria (Alert System)

Il servizio calcola il *Savings Rate* (Tasso di Risparmio) percentuale e lo confronta con soglie predefinite per generare un feedback qualitativo:

* **Risparmio > 20%**: Livello `GREEN` (Consiglio di investimento).
* **Risparmio > 0%**: Livello `YELLOW` (Avviso di prudenza).
* **Bilancio Negativo**: Livello `RED` (Allarme critico).

In assenza totale di entrate ma con spese registrate, il sistema entra in uno stato di allerta critica `RED - Critical`, forzando il savings rate a un valore negativo simbolico per evidenziare la gravità della situazione.


## Testing e Quality Assurance

La strategia di test è stata progettata per garantire l'affidabilità dei calcoli matematici e la resilienza del servizio.

### Unit Testing Isolato

È stato sviluppato un test suite completo (`AnalyticsServiceTest`) utilizzando **Mockito**.
Poiché l'Analytics Service dipende da una risorsa esterna (Accounting Service), non sarebbe stato affidabile testarlo facendo vere chiamate HTTP (che potrebbero fallire per motivi di rete).

Invece, il componente `RestTemplate` viene "mockato" (simulato) per restituire un set di dati controllato (es. uno stipendio `INCOME` e una bolletta `EXPENSE`).
Il test verifica che:

1. Il servizio consumi correttamente i dati mockati.
2. La logica di aggregazione separi correttamente Entrate e Uscite in base al tipo.
3. Il calcolo del saldo finale sia matematicamente corretto.
4. Il sistema di Alert generi il livello di rischio appropriato.

Questo approccio garantisce che la logica di business sia corretta al 100% indipendentemente dallo stato dei servizi esterni.



## Conclusione dell’iterazione

Al termine dell’Iterazione 2, l’Analytics Service si presenta come un componente maturo e "Enterprise-ready".
L'architettura adottata garantisce:

* **Robustezza**: Grazie all'uso di `TransactionType` e `BigDecimal`.
* **Manutenibilità**: Grazie alla chiara separazione delle responsabilità e al codice pulito (Lombok).
* **Scalabilità**: Grazie alla configurazione externalizzata e alla natura stateless del servizio.

Il sistema è ora pronto per la fase successiva di containerizzazione tramite Docker, avendo risolto a monte tutte le problematiche di integrazione e coerenza dei dati.