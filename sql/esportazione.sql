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
INSERT INTO skin (id, nome, campione, prezzo, quantita, immagine) VALUES
(1, 'Gladiator Draven', 'Draven', 6.99, 5, 'droben.jpg'),
(2, 'Crime City Twitch', 'Twitch', 7.99, 7, 'twitch.png'),
(3, 'Grim Reaper Karthus', 'Karthus', 7.99, 8, 'karthus.png'),
(4, 'Prestige Shaco', 'Shaco', 14.99, 3, 'shaco1.jpg'),
(5, 'Lost Chapter Karthus', 'Karthus', 9.99, 4, 'karthus2.jpg'),
(6, 'Winterblessed Shaco', 'Shaco', 9.99, 5, 'shaco2.jpg'),
(7, 'Arcanist Shaco', 'Shaco', 5.99, 7, 'shaco3.png'),
(8, 'Elderwood Karthus', 'Karthus', 9.99, 8, 'karthus3.jpg'),
(9, 'Sentinel Rengar', 'Rengar', 8.99, 8, 'rengar.jpg'),
(10, 'Pretty Kitty Rengar', 'Rengar', 8.99, 7, 'rengar2.png'),
(11, 'Ninja Rammus', 'Rammus', 5.99, 6, 'rammus.png'),
(12, 'Lunar Guardian Khazix', 'Khazix', 7.99, 5, 'khazix.png'),
(13, 'Crime City Shaco', 'Shaco', 8.99, 4, 'shaco4.png'),
(14, 'High Noon Twitch', 'Twitch', 8.99, 3, 'twitch2.png'),
(15, 'Ruined Draven', 'Draven', 8.99, 2, 'draven2.png'),
(16, 'Frejlord Sylas', 'Sylas', 6.99, 4, 'sylas.png'),
(17, 'Debonoir Draven', 'Draven', 8.99, 0, 'draven3.png'),
(18, 'Ice King Twitch', 'Twitch', 8.99, 2, 'twitch3.png'),
(19, 'Odyssey Khazix', 'Khazix', 7.99, 7, 'khazix2.png'),
(20, 'Death Blossom Khazix', 'Khazix', 6.99, 4, 'khazix3.png'),
(21, 'King Rammus', 'Rammus', 4.99, 3, 'rammus2.png'),
(22, 'Frejlord Rammus', 'Rammus', 6.99, 5, 'rammus3.png'),
(23, 'Dunkmaster Ivern', 'Ivern', 10.99, 3, 'ivern.png');


CREATE TABLE carrello (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    id_skin INT NOT NULL,
    quantita INT DEFAULT 1,
    FOREIGN KEY (id_utente) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (id_skin) REFERENCES skin(id) ON DELETE CASCADE
);

CREATE TABLE login_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
);