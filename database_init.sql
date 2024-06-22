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

-- Creazione della tabella entita
CREATE TABLE entita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    codice_identificativo VARCHAR(50) NOT NULL UNIQUE,
    descrizione TEXT,
    data_messa_in_servizio DATE,
    locazione VARCHAR(100),
    utente VARCHAR(100),
    potenza FLOAT,
    prodotti_testati TEXT,
    documentazione TEXT
);

-- Creazione della tabella tipologie
CREATE TABLE tipologie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);

-- Creazione della tabella entita_tipologie (relazione molti a molti tra entità e tipologie)
CREATE TABLE entita_tipologie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entita_id INT,
    tipologia_id INT,
    FOREIGN KEY (entita_id) REFERENCES entita(id) ON DELETE CASCADE,
    FOREIGN KEY (tipologia_id) REFERENCES tipologie(id) ON DELETE CASCADE
);

-- Creazione della tabella checklist
CREATE TABLE checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    scadenza DATE
);

-- Creazione della tabella punti_checklist
CREATE TABLE punti_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT,
    descrizione TEXT,
    tipo_risultato ENUM('binario', 'valore_numerico') NOT NULL DEFAULT 'binario',
    FOREIGN KEY (checklist_id) REFERENCES checklist(id) ON DELETE CASCADE
);

-- Creazione della tabella risultati_checklist
CREATE TABLE risultati_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT,
    punto_checklist_id INT,
    risultato TEXT NOT NULL,
    nota TEXT,
    FOREIGN KEY (checklist_id) REFERENCES checklist(id) ON DELETE CASCADE,
    FOREIGN KEY (punto_checklist_id) REFERENCES punti_checklist(id) ON DELETE CASCADE
);

-- Creazione della tabella checklist_completate (per tenere traccia delle checklist completate)
CREATE TABLE checklist_completate (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entita_id INT,
    checklist_id INT,
    data_completamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entita_id) REFERENCES entita(id) ON DELETE CASCADE,
    FOREIGN KEY (checklist_id) REFERENCES checklist(id) ON DELETE CASCADE
);

-- Creazione della tabella per le relazioni (esempio di struttura)
CREATE TABLE entita_relazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entita_id INT NOT NULL,
    entita_padre_id INT NOT NULL,
    FOREIGN KEY (entita_id) REFERENCES entita(id),
    FOREIGN KEY (entita_padre_id) REFERENCES entita(id)
);