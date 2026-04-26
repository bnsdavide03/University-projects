
CREATE TABLE `casa_automobilistica` (
  `nomeCasa` varchar(32) NOT NULL,
  `mail` varchar(30) NOT NULL,
  `sitoWeb` varchar(30) NOT NULL,
  `nazionalita` varchar(20) NOT NULL,
  `nomeFondatore` varchar(30) DEFAULT NULL,
  `annoFondazione` int(11) DEFAULT NULL,
  `descrizione` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`nomeCasa`)
);


CREATE TABLE `modello_veicolo` (
  `nomeModello` varchar(32) NOT NULL,
  `annoInizioProd` int(11) NOT NULL,
  `annoFineProd` int(11) DEFAULT NULL,
  `descrizione` varchar(128) NOT NULL,
  `nomeCasa` varchar(32) NOT NULL,
  PRIMARY KEY (`nomeModello`),
  KEY `nomeCasa` (`nomeCasa`),
  FOREIGN KEY (`nomeCasa`) REFERENCES `casa_automobilistica` (`nomeCasa`) ON DELETE CASCADE
);


CREATE TABLE `veicolo` (
  `targa` varchar(20) NOT NULL,
  `annoImmatricolazione` int(11) NOT NULL,
  `cilindrata` int(11) NOT NULL,
  `alimentazione` varchar(20) NOT NULL,
  `prezzo` int(11) NOT NULL,
  `colore` varchar(20) NOT NULL,
  `numPorte` int(11) NOT NULL,
  `note` varchar(150) DEFAULT NULL,
  `modello` varchar(32) NOT NULL,
  `versione` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`targa`),
  KEY `modello` (`modello`),
  FOREIGN KEY (`modello`) REFERENCES `modello_veicolo` (`nomeModello`) ON DELETE CASCADE
);


CREATE TABLE `venditore` (
  `idVenditore` int(11) NOT NULL AUTO_INCREMENT,
  `codFiscale` varchar(16) NOT NULL,
  `tipoVenditore` varchar(16) NOT NULL,
  `nome` varchar(32) NOT NULL,
  `cognome` varchar(32) NOT NULL,
  `ragioneSociale` varchar(16) NOT NULL,
  `pIVA` varchar(16) NOT NULL,
  `telefono` varchar(16) NOT NULL,
  PRIMARY KEY (`idVenditore`),
  UNIQUE (`codFiscale`)
);


CREATE TABLE `vendere` (
  `targa` varchar(20) NOT NULL,
  `idVenditore` int(11) NOT NULL,
  `dataInserimento` date NOT NULL,
  `dataVendita` date DEFAULT NULL,
  PRIMARY KEY (`targa`,`idVenditore`),
  KEY `targa` (`targa`),
  KEY `idVenditore` (`idVenditore`),
  FOREIGN KEY (`targa`) REFERENCES `veicolo` (`targa`) ON DELETE CASCADE, 
  FOREIGN KEY (`idVenditore`) REFERENCES `venditore` (`idVenditore`) ON DELETE CASCADE
);


CREATE TABLE `cliente` (
    `idCliente` INT AUTO_INCREMENT,
    `nome` VARCHAR(100),
    `cognome` VARCHAR(100),
    `email` VARCHAR(100) NULL,
    `telefono` VARCHAR(15),
    `codFiscale` VARCHAR(16),
    `data_nascita` DATE,
    `via` VARCHAR(100),
    `numero_civico` VARCHAR(10),
    `paese` VARCHAR(100),
    PRIMARY KEY (`idCliente`),
    UNIQUE (`codFiscale`)
);

CREATE TABLE `contratto` (
    `targa` varchar(20) NOT NULL,
    `numero` INT NOT NULL,
    `data_contratto` DATE,
    `totale_contratto` DECIMAL(10, 2),
    `durata_contratto` INT,
    `metodo_pagamento` VARCHAR(50),
    `idVenditore` INT,
    `idCliente` INT,
    PRIMARY KEY (`numero`, `targa`),
    FOREIGN KEY (`idVenditore`) REFERENCES `venditore`(`idVenditore`) ON DELETE CASCADE,
    FOREIGN KEY (`idCliente`) REFERENCES `cliente`(`idCliente`) ON DELETE CASCADE
);


CREATE TABLE `assicurazione` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `data_inizio` DATE,
    `data_fine` DATE,
    `condizioni` VARCHAR(100),
    `copertura` VARCHAR(100),
    `tipo` VARCHAR(50),
    `targa_veicolo` varchar(20) NOT NULL,
    FOREIGN KEY (`targa_veicolo`) REFERENCES `veicolo`(`targa`) ON DELETE CASCADE
);


CREATE TABLE `produce` (
    `nome_casa_automobilistica` varchar(32) NOT NULL,
    `nome_modello_veicolo` varchar(32) NOT NULL,
    PRIMARY KEY (`nome_casa_automobilistica`, `nome_modello_veicolo`),
    FOREIGN KEY (`nome_casa_automobilistica`) REFERENCES `casa_automobilistica`(`nomeCasa`) ON DELETE CASCADE,
    FOREIGN KEY (`nome_modello_veicolo`) REFERENCES `modello_veicolo`(`nomeModello`) ON DELETE CASCADE
);


CREATE TABLE `appartiene` (
    `nome_modello_veicolo` varchar(32) NOT NULL,
    `targa_veicolo` varchar(20) NOT NULL,
    PRIMARY KEY (`nome_modello_veicolo`, `targa_veicolo`),
    FOREIGN KEY (`nome_modello_veicolo`) REFERENCES `modello_veicolo`(`nomeModello`) ON DELETE CASCADE,
    FOREIGN KEY (`targa_veicolo`) REFERENCES `veicolo`(`targa`) ON DELETE CASCADE
);


INSERT INTO `casa_automobilistica` VALUES 
('Alfa Romeo','assistenza@alfaromeo.com','alfaromeo.com','it',NULL,NULL,NULL),
('Audi','audi@audi.com','audi.com','de','August Horch',1932,NULL),
('BMW','bmw@bmw.com','bmw.com','de','idk',1900,NULL),
('Ferrari','ferrari@ferrari.it','ferrari.com','it','Enzo Ferrari',1940,NULL),
('Fiat','assistenza@fiat.com','fiat.com','it',NULL,NULL,NULL),
('Ford','ford@ford.com','ford.com','us','Henry Ford',1903,NULL),
('Jaguar','jaguar@jaguar.com','jaguar.com','us','Marco Jaguar',1951,'Boh'),
('Lamborghini','lamborghini@mail.com','lamborghini.com','it','Ferruccio Lamborghini',1963,NULL),
('Lancia','assistenza@lancia.com','lancia.com','it',NULL,NULL,NULL),
('Mercedes','assistenza@mercedes.com','mercedes-benz.com','it',NULL,NULL,NULL),
('Tesla','musk@tesla.com','tesla.com','us','Elon Musk',2009,'boh');


INSERT INTO `modello_veicolo` VALUES 
('A1',2020,NULL,'','Audi'),
('A160',2020,NULL,'','Mercedes'),
('A180',2020,NULL,'','Mercedes'),
('A200',2020,NULL,'','Mercedes'),
('A3',2020,NULL,'','Audi'),
('A35',2020,NULL,'','Mercedes'),
('A4',2020,NULL,'','Audi'),
('A45',2020,NULL,'','Mercedes'),
('A6',2018,NULL,'','Audi'),
('Delta',1979,1993,'','Lancia'),
('Giulietta',2010,2020,'','Alfa Romeo'),
('GT',2020,NULL,'','Mercedes'),
('Model S',2012,NULL,'','Tesla'),
('Punto',1993,2018,'','Fiat'),
('R8',2020,NULL,'','Audi'),
('RSQ8',2020,NULL,'','Audi');


INSERT INTO `veicolo` VALUES 
('AA202ZS',1991,1995,'Benzina',16300,'rosso',5,'Usata come nuova','Delta','HF Integrale (1991)'),
('AB27ANI',2018,0,'Elettrica',69000,'bianco',5,'Nuova','Model S','2018'),
('ED554FR',2002,1242,'Benzina',4500,'arrurro',5,'funziona perfettamente','Punto','1999-2003'),
('FF690AF',2021,5204,'Benzina',210000,'blu',3,'nuova','R8','2020'),
('FF691AF',2021,999,'Benzina',22500,'gialla',5,'nuova','A1','2020'),
('FF692AF',2021,1500,'Benzina',35000,'grigia',5,'nuova','A3','2020'),
('FF693AF',2021,2000,'Benzina',50000,'nera',5,'nuova','A4','2020'),
('FF694AF',2021,4000,'Benzina',150000,'verde',5,'nuova','RSQ8','2020'),
('FF695AF',2021,1500,'Benzina',30000,'nera',5,'nuova','A160','2020'),
('FF696AF',2021,2000,'Benzina',40000,'bianco',5,'nuova','A180','2020'),
('FF697AF',2021,2500,'Benzina',45000,'nera',5,'nuova','A200','2020'),
('FF698AF',2021,2000,'Benzina',55000,'giallo',5,'nuova','A35','2020'),
('FF699AF',2021,2500,'Benzina',70000,'nera',5,'nuova','A45','2020'),
('FF700AF',2021,3000,'Benzina',150000,'nera',5,'nuova','GT','2020'),
('FV234DD',2014,1368,'Benzina',14500,'bianco',5,'Come nuova','Giulietta','2014'),
('GG234LDF',2018,300,'diesel',55000,'black',5,'Nuova','A6','2018');


INSERT INTO `venditore` VALUES 
(1,'65PN9N45W5NTGQNH','privato','Massimo','Ranieri','Valcava Srls','14852036410','+39257186325'),
(2,'PS9NZSFRWYWZ97XC','privato','Vittorio','Sgarbi','Sgarbi SpA','11194852054','+39495204860'),
(3,'3GNNYTQMVAZ923Y6','privato','Gino','Cartonio','Pigna Srls','11145862014','+39571369204'),
(4,'FRQCBPUT28BSF383','privato','Lorenzo','De Medici','De Medici Srl','11145821475','+39035482165');


INSERT INTO `vendere` VALUES 
('AA202ZS',1,'2021-05-24','2021-05-24'),
('AB27ANI',4,'2021-05-20','2021-07-28'),
('ED554FR',2,'2021-05-24','2021-10-02'),
('FF690AF',4,'2021-05-20','2021-08-19'),
('FF691AF',1,'2021-05-20','2021-06-27'),
('FF692AF',4,'2021-05-20','2021-06-10'),
('FF693AF',4,'2021-05-20',NULL),
('FF694AF',2,'2021-05-24',NULL),
('FF695AF',2,'2021-05-24',NULL),
('FF696AF',3,'2021-05-24',NULL),
('FF697AF',3,'2021-05-24',NULL),
('FF698AF',3,'2021-05-24',NULL),
('FF699AF',1,'2021-05-24',NULL),
('FF700AF',1,'2021-05-24',NULL),
('FV234DD',3,'2021-05-24',NULL),
('GG234LDF',2,'2021-05-24',NULL);


INSERT INTO `cliente` (`nome`, `cognome`, `email`, `telefono`, `codFiscale`, `data_nascita`, `via`, `numero_civico`, `paese`) VALUES
('Mario', 'Rossi', 'mario.rossi@libero.com', '+391234567890', 'RSSMRA80A01H501U', '1980-01-01', 'Via Roma', '10', 'Roma'),
('Luigi', 'Bianchi', 'luigi.bianchi@gmail.com', '+390987654321', 'BNCGLG85B01H501U', '1985-02-01', 'Corso Italia', '15', 'Milano'),
('Giulia', 'Verdi', 'giulia.verdi@mail.com', '+391122334455', 'VRDGLI90C01H501U', '1990-03-01', 'Via Milano', '20', 'Torino'),
('Andrea', 'Esposito', 'andrea.esposito@gmail.com', '+392233445566', 'SPSNDR75D01H501U', '1975-04-01', 'Via Napoli', '25', 'Napoli'),
('Sara', 'Russo', 'sara.russo@tiscali.com', '+393344556677', 'RSSSRA88E01H501U', '1988-05-01', 'Via Firenze', '30', 'Firenze'),
('Marco', 'Ferrari', 'marco.ferrari@tiscali.com', '+394455667788', 'FRRMRC70F01H501U', '1970-06-01', 'Via Bologna', '35', 'Bologna');


INSERT INTO `contratto` (`targa`, `data_contratto`, `numero`, `totale_contratto`, `durata_contratto`, `metodo_pagamento`, `idVenditore`, `idCliente`) VALUES
('AA202ZS', '2021-05-20', 2, 15000.00, 36, 'Finanziamento', 1, 1),
('AB27ANI', '2021-05-24', 2, 70000.00, 48, 'Contanti', 4, 2),
('ED554FR', '2021-05-20', 2, 6000.00, 12, 'Leasing', 2, 3),
('FF690AF', '2021-05-24', 2, 200000.00, 60, 'Finanziamento', 4, 4),
('FF691AF', '2021-05-20', 2, 25000.00, 24, 'Contanti', 1, 5),
('FF692AF', '2021-05-24', 2, 30000.00, 36, 'Finanziamento', 4, 6);


INSERT INTO `assicurazione` (`data_inizio`, `data_fine`, `condizioni`, `copertura`, `tipo`, `targa_veicolo`) VALUES
('2021-01-01', '2023-01-01', 'Estensione di garanzia di 2 anni', 'Tutto incluso', 'Estesa', 'FF694AF'),
('2022-02-01', '2024-02-01', 'Garanzia base di 2 anni', 'Solo motore e parti elettroniche', 'Base', 'FF696AF'),
('2023-03-01', '2025-03-01', 'Garanzia premium di 2 anni', 'Tutto incluso con assistenza stradale', 'Premium', 'FV234DD'),
('2021-04-01', '2023-04-01', 'Estensione di garanzia di 2 anni', 'Tutto incluso', 'Estesa', 'AB27ANI'),
('2022-05-01', '2024-05-01', 'Garanzia base di 2 anni', 'Solo motore e parti elettroniche', 'Base', 'AA202ZS'),
('2023-06-01', '2025-06-01', 'Garanzia premium di 2 anni', 'Tutto incluso con assistenza stradale', 'Premium', 'GG234LDF');


INSERT INTO `produce` (`nome_casa_automobilistica`, `nome_modello_veicolo`) VALUES
('Fiat', 'Punto'),
('Tesla', 'Model S'),
('Audi', 'RSQ8'),
('Audi', 'R8'),
('Audi', 'A6'),
('Audi', 'A4'),
('Audi', 'A3'),
('Audi', 'A1'),
('Mercedes', 'A160'),
('Mercedes', 'A180'),
('Mercedes', 'A200'),
('Mercedes', 'A35'),
('Mercedes', 'A45'),
('Mercedes', 'GT'),
('Lancia', 'Delta'),
('Alfa Romeo', 'Giulietta');


INSERT INTO `appartiene` (`nome_modello_veicolo`, `targa_veicolo`) VALUES
('Punto', 'ED554FR'),
('Model S', 'AB27ANI'),
('RSQ8', 'FF694AF'),
('R8', 'FF690AF'),
('A6', 'GG234LDF'),
('A4', 'FF693AF'),
('A3', 'FF692AF'),
('A1', 'FF691AF'),
('A160', 'FF695AF'),
('A180', 'FF696AF'),
('A200', 'FF697AF'),
('A35', 'FF698AF'),
('A45', 'FF699AF'),
('GT', 'FF700AF'),
('Delta', 'AA202ZS'),
('Giulietta', 'FV234DD');

