/**
 * Script di creazione del database
 * 
 * Questo file contiene tutti i comandi SQL necessari per creare la struttura
 * del database utilizzato dall'applicazione Quiz Online.
 * Include:
 * - Creazione delle tabelle (Utente, Quiz, Domanda, Risposta, Partecipazione, RispostaUtenteQuiz)
 * - Definizione delle chiavi primarie e delle relazioni
 * - Impostazione dei vincoli di integrità referenziale
 * - Creazione degli indici per ottimizzare le prestazioni
 * - Inserimento di dati iniziali (se necessario)
 */


drop database if exists quiz_online;
drop user if exists 'quiz_user'@'localhost';
drop user if exists 'quiz_user'@'%';
-- Creazione del database
CREATE DATABASE quiz_online CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'quiz_user'@'localhost' IDENTIFIED BY '!*5633hH$rU7^t%65@J@';
GRANT ALL PRIVILEGES ON quiz_online.* TO 'quiz_user'@'localhost';
FLUSH PRIVILEGES;
-- Selezione del database
USE quiz_online;

-- Tabella Utente
CREATE TABLE Utente (
    nomeUtente VARCHAR(50) PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    eMail VARCHAR(100) NOT NULL UNIQUE
);

-- Tabella Quiz
CREATE TABLE Quiz (
    codice INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(255) NOT NULL,
    dataInizio DATE NOT NULL,
    dataFine DATE NOT NULL,
    creatore VARCHAR(50) NOT NULL,
    FOREIGN KEY (creatore) REFERENCES Utente(nomeUtente) ON DELETE CASCADE
);

-- Tabella Domanda
CREATE TABLE Domanda (
    quiz INT NOT NULL,
    numero INT NOT NULL,
    testo TEXT NOT NULL,
    PRIMARY KEY (quiz, numero),
    FOREIGN KEY (quiz) REFERENCES Quiz(codice) ON DELETE CASCADE
);

-- Tabella Risposta
CREATE TABLE Risposta (
    quiz INT NOT NULL,
    domanda INT NOT NULL,
    numero INT NOT NULL,
    testo TEXT NOT NULL,
    tipo ENUM('Corretta', 'Sbagliata') NOT NULL,
    punteggio INT DEFAULT 0,
    PRIMARY KEY (quiz, domanda, numero),
    FOREIGN KEY (quiz, domanda) REFERENCES Domanda(quiz, numero) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabella Partecipazione
CREATE TABLE Partecipazione (
    codice INT AUTO_INCREMENT PRIMARY KEY,
    utente VARCHAR(50) NOT NULL,
    quiz INT NOT NULL,
    data DATE NOT NULL,
    FOREIGN KEY (utente) REFERENCES Utente(nomeUtente) ON DELETE CASCADE,
    FOREIGN KEY (quiz) REFERENCES Quiz(codice) ON DELETE CASCADE
);

-- Tabella RispostaUtenteQuiz
CREATE TABLE RispostaUtenteQuiz (
    partecipazione INT NOT NULL,
    quiz INT NOT NULL,
    domanda INT NOT NULL,
    risposta INT NOT NULL,
    PRIMARY KEY (partecipazione, quiz, domanda, risposta),
    FOREIGN KEY (partecipazione) REFERENCES Partecipazione(codice) ON DELETE CASCADE,
    FOREIGN KEY (quiz, domanda, risposta) REFERENCES Risposta(quiz, domanda, numero) ON DELETE CASCADE
);
