CREATE DATABASE IF NOT EXISTS store_skin;
USE store_skin;


CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ruolo ENUM('utente', 'admin') DEFAULT 'utente'
);


INSERT INTO utenti (username, password, ruolo) VALUES
('admin', '$2y$12$/inb7bDGIsZPm0yoEM8sNODSynSgendtMVM7RtGdQEuP1750d4r9C', 'admin');               -- password: admin123



CREATE TABLE skin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    campione VARCHAR(100) NOT NULL,
    prezzo DECIMAL(6,2) NOT NULL,
    quantita INT DEFAULT 0,
    immagine VARCHAR(255) 
);


CREATE TABLE carrello (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    id_skin INT NOT NULL,
    quantita INT DEFAULT 1,
    FOREIGN KEY (id_utente) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (id_skin) REFERENCES skin(id) ON DELETE CASCADE
);
