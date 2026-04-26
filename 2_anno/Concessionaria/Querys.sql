-- Ottenere l'elenco di tutte le case automobilistiche con la loro nazionalità e il numero di modelli prodotti da ciascuna casa, includendo le case che non hanno modelli:

SELECT c.nomeCasa, c.nazionalita, COUNT(m.nomeModello) AS numero_modelli
FROM casa_automobilistica c
LEFT JOIN modello_veicolo m ON c.nomeCasa = m.nomeCasa
GROUP BY c.nomeCasa, c.nazionalita;



-- Contare il numero di veicoli venduti per ogni venditore, filtrando per veicoli venduti nel 2021 e ordinando per numero di veicoli venduti in ordine decrescente:

SELECT vend.idVenditore, vend.nome, vend.cognome, COUNT(*) AS numero_veicoli_venduti
FROM vendere ve
JOIN venditore vend ON ve.idVenditore = vend.idVenditore
WHERE YEAR(ve.dataVendita) = 2021
GROUP BY vend.idVenditore, vend.nome, vend.cognome
ORDER BY numero_veicoli_venduti DESC;



-- Elencare tutti i modelli di veicoli prodotti da una specifica casa automobilistica e il numero di veicoli immatricolati per ogni modello, mostrando solo i modelli che hanno veicoli associati:

SELECT m.nomeModello, COUNT(v.targa) AS numero_veicoli
FROM modello_veicolo m
JOIN veicolo v ON m.nomeModello = v.modello
WHERE m.nomeCasa = 'Mercedes'
GROUP BY m.nomeModello;



-- Elencare tutti i veicoli, insieme ai loro dettagli, che sono in vendita da un venditore specifico e includere informazioni sulla casa automobilistica:

CREATE VIEW DettagliVeicoloVenditore AS
SELECT v.targa, v.annoImmatricolazione, v.cilindrata, v.alimentazione, v.prezzo, v.colore, v.numPorte, v.note, v.modello, v.versione, ve.idVenditore, vend.nome, vend.cognome
FROM veicolo v
JOIN vendere ve ON v.targa = ve.targa
JOIN venditore vend ON ve.idVenditore = vend.idVenditore;


SELECT d.targa, d.annoImmatricolazione, d.cilindrata, d.alimentazione, d.prezzo, d.colore, d.numPorte, d.note, d.modello, d.versione, ca.nomeCasa, ca.nazionalita
FROM DettagliVeicoloVenditore d
JOIN modello_veicolo m ON d.modello = m.nomeModello
JOIN casa_automobilistica ca ON m.nomeCasa = ca.nomeCasa
WHERE d.nome = 'Massimo' AND d.cognome = 'Ranieri';



-- Trovare il numero totale di veicoli venduti da ciascun venditore e l'importo totale delle vendite:

CREATE VIEW VenditePerVenditore AS
SELECT venditore.idVenditore, venditore.nome, venditore.cognome, ve.targa, ve.dataVendita, v.prezzo
FROM venditore
JOIN vendere ve ON venditore.idVenditore = ve.idVenditore
JOIN veicolo v ON ve.targa = v.targa;


SELECT vv.idVenditore, vv.nome, vv.cognome, COUNT(vv.targa) AS totale_veicoli_venduti, SUM(vv.prezzo) AS importo_totale_vendite
FROM VenditePerVenditore vv
WHERE vv.dataVendita IS NOT NULL
GROUP BY vv.idVenditore, vv.nome, vv.cognome;



-- Aggiorna il prezzo di tutti i veicoli del modello 'Model S' a 90000

UPDATE veicolo
SET prezzo = 90000
WHERE modello = 'Model S';



-- Aggiorna la descrizione di tutti i modelli della casa automobilistica 'Audi'

UPDATE modello_veicolo
SET descrizione = 'macchina Tedesca di qualità ottima'
WHERE nomeCasa = 'Audi';



-- Eliminare tutti i veicoli che non sono stati venduti

DELETE FROM veicolo
WHERE targa IN (
    SELECT v.targa
    FROM veicolo v JOIN vendere ve ON v.targa = ve.targa
    WHERE ve.dataVendita IS NULL
);



-- Eliminare tutti i veicoli che sono stati venduti

DELETE veicolo
FROM veicolo
JOIN vendere ON veicolo.targa = vendere.targa
WHERE vendere.dataVendita IS NOT NULL;



-- Elimina tutte le tabelle legate alla casa automobilistica

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS casa_automobilistica;
DROP TABLE IF EXISTS modello_veicolo;
DROP TABLE IF EXISTS veicolo;
SET FOREIGN_KEY_CHECKS = 1;



-- Modifica 'telefono' come chiave primaria nella tabella 'cliente'

ALTER TABLE cliente
ADD UNIQUE (telefono);
