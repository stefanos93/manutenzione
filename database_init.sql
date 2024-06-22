CREATE DATABASE IF NOT EXISTS manutenzione;
USE manutenzione;

-- Creazione della tabella utenti
CREATE TABLE IF NOT EXISTS utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    livello_accesso ENUM('base', 'manutentore', 'admin') NOT NULL DEFAULT 'base'
);

-- Creazione della tabella tipologie
CREATE TABLE IF NOT EXIST tipologie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descrizione TEXT
);

-- Creazione della tabella entita
CREATE TABLE IF NOT EXIST entita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    codice_identificativo VARCHAR(50) NOT NULL UNIQUE,
    descrizione TEXT,
    data_messa_in_servizio DATE,
    locazione VARCHAR(100),
    utente VARCHAR(100),
    potenza FLOAT,
    prodotti_testati TEXT,
    documentazione TEXT,
    tipologia_id INT NOT NULL,
    FOREIGN KEY (tipologia_id) REFERENCES tipologie(id)
);

-- Creazione della tabella per le relazioni (esempio di struttura)
CREATE TABLE IF NOT EXIST entita_relazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entita_id INT NOT NULL,
    entita_padre_id INT NOT NULL,
    FOREIGN KEY (entita_id) REFERENCES entita(id),
    FOREIGN KEY (entita_padre_id) REFERENCES entita(id)
);

-- Creazione della tabella checklist
CREATE TABLE IF NOT EXIST checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    scadenza DATE
);

CREATE TABLE IF NOT EXIST checklist_tipologie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT,
    tipologia_id INT,
    FOREIGN KEY (checklist_id) REFERENCES checklist(id) ON DELETE CASCADE,
    FOREIGN KEY (tipologia_id) REFERENCES tipologie(id) ON DELETE CASCADE
)

-- Creazione della tabella punti_checklist
CREATE TABLE IF NOT EXIST punti_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT,
    descrizione TEXT,
    tipo_risultato ENUM('binario', 'valore_numerico') NOT NULL DEFAULT 'binario',
    FOREIGN KEY (checklist_id) REFERENCES checklist(id) ON DELETE CASCADE
);

-- Creazione della tabella per i risultati delle checklist
CREATE TABLE IF NOT EXIST risultati_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT,
    entita_id INT,
    data_completamento DATE,
    risultato TEXT,
    stato ENUM('incompleta', 'completa') NOT NULL DEFAULT 'incompleta',
    FOREIGN KEY (checklist_id) REFERENCES checklist(id) ON DELETE CASCADE,
    FOREIGN KEY (entita_id) REFERENCES entita(id) ON DELETE CASCADE
);

-- Creazione della tabella per le scadenze delle checklist
CREATE TABLE IF NOT EXIST scadenze_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT,
    entita_id INT,
    data_scadenza DATE,
    FOREIGN KEY (checklist_id) REFERENCES checklist(id) ON DELETE CASCADE,
    FOREIGN KEY (entita_id) REFERENCES entita(id) ON DELETE CASCADE
);
