# Project Plan: Multi-Server Queue Simulator (C3)

## 1. Obiettivo del Progetto
Il progetto consiste nello sviluppo di un simulatore a eventi discreti in C++ per un sistema composto da $N$ serventi, ciascuno dotato di una propria coda FIFO (First-In-First-Out) di capacità infinita. L'obiettivo principale è analizzare l'efficienza di diverse politiche di instradamento dei pacchetti in termini di tempi di attesa e bilanciamento del carico.

## 2. Specifiche Matematiche e Statistiche
Il simulatore deve modellare i processi stocastici secondo le seguenti definizioni:
*   **Processo di Arrivo**: Gli arrivi dei pacchetti al sistema seguono un processo di Poisson con tasso $\lambda$.
*   **Tempi di Servizio**: Il tempo di servizio del servente $i$-esimo segue una distribuzione esponenziale negativa con valor medio $1/\mu_{i}$.

## 3. Logiche di Instradamento (Routing)
Il software deve permettere di selezionare in input una delle seguenti politiche per l'assegnazione dei pacchetti in arrivo alle $N$ code:
1.  **Instradamento Casuale (Random)**: Il pacchetto viene assegnato a un servente scelto uniformemente a caso.
2.  **Round-Robin**: Il pacchetto viene assegnato ai serventi seguendo un ordine sequenziale ciclico.
3.  **Coda più Corta (Join the Shortest Queue)**: Il pacchetto viene indirizzato alla coda che, al momento dell'arrivo, presenta il minor numero di elementi presenti.

## 4. Requisiti di Interfaccia (I/O)

### 4.1 Parametri di Input
Il sistema deve accettare obbligatoriamente i seguenti dati:
*   Numero $N$ di serventi.
*   Tasso di arrivo globale $\lambda$.
*   Tassi di servizio $\mu_{i}$ per ogni singolo servente.
*   Selezione della politica di assegnazione.

### 4.2 Metriche di Output
Al termine della simulazione, il programma deve calcolare e visualizzare:
1.  **Tempo medio di permanenza** dei pacchetti nel sistema.
2.  **Lunghezza media** di ogni singola coda.
3.  **Indice di sbilanciamento**: Calcolato come la differenza tra la massima e la minima lunghezza media di coda registrata tra i serventi.

## 5. Linee Guida di Implementazione
*   **Linguaggio**: C++ con approccio orientato agli oggetti (classi separate per eventi, code, serventi e motore di simulazione).
*   **Generazione Casuale**: Utilizzo della libreria standard `<random>` per gestire le distribuzioni esponenziali in modo accurato.
*   **Gestione Errori**: In caso di parametri mancanti o non validi, il software deve effettuare assunzioni ragionevoli, documentandole tramite commenti nel codice e nella relazione finale.