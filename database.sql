CREATE DATABASE IF NOT EXISTS ristohub_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ristohub_ai;

-- User management (obbligatorio)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ruolo ENUM('admin','cameriere','cuoco','cliente') NOT NULL DEFAULT 'cliente',
    punti_loyalty INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descrizione VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users_has_groups (
    users_id INT NOT NULL,
    groups_id INT NOT NULL,
    PRIMARY KEY (users_id, groups_id),
    FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (groups_id) REFERENCES user_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE services (
    username VARCHAR(50) NOT NULL PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE services_has_groups (
    services_username VARCHAR(50) NOT NULL,
    groups_id INT NOT NULL,
    PRIMARY KEY (services_username, groups_id),
    FOREIGN KEY (services_username) REFERENCES services(username) ON DELETE CASCADE,
    FOREIGN KEY (groups_id) REFERENCES user_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Core ristorante
CREATE TABLE categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descrizione VARCHAR(255),
    ordine INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE piatti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    prezzo DECIMAL(10,2) NOT NULL,
    img VARCHAR(255),
    disponibile TINYINT(1) DEFAULT 1,
    categorie_id INT,
    FOREIGN KEY (categorie_id) REFERENCES categorie(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ingredienti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    quantita_magazzino DECIMAL(10,2) DEFAULT 0,
    unita_misura VARCHAR(20),
    soglia_minima DECIMAL(10,2) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE piatti_has_ingredienti (
    piatti_id INT NOT NULL,
    ingredienti_id INT NOT NULL,
    quantita DECIMAL(8,2),
    PRIMARY KEY (piatti_id, ingredienti_id),
    FOREIGN KEY (piatti_id) REFERENCES piatti(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredienti_id) REFERENCES ingredienti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE allergeni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    icona VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ingredienti_has_allergeni (
    ingredienti_id INT NOT NULL,
    allergeni_id INT NOT NULL,
    PRIMARY KEY (ingredienti_id, allergeni_id),
    FOREIGN KEY (ingredienti_id) REFERENCES ingredienti(id) ON DELETE CASCADE,
    FOREIGN KEY (allergeni_id) REFERENCES allergeni(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tavoli (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    capacita_max INT NOT NULL DEFAULT 2,
    posizione VARCHAR(50),
    stato ENUM('libero','occupato','prenotato') DEFAULT 'libero'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE prenotazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    users_id INT,
    tavoli_id INT,
    nome VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    data DATE NOT NULL,
    ora TIME NOT NULL,
    persone INT NOT NULL,
    note TEXT,
    stato ENUM('in_attesa','confermata','annullata') DEFAULT 'in_attesa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (tavoli_id) REFERENCES tavoli(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ordini (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tavoli_id INT NOT NULL,
    users_id INT,
    stato ENUM('inviato','in_preparazione','pronto','completato') DEFAULT 'inviato',
    note TEXT,
    totale DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tavoli_id) REFERENCES tavoli(id) ON DELETE CASCADE,
    FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ordini_has_piatti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ordini_id INT NOT NULL,
    piatti_id INT NOT NULL,
    quantita INT NOT NULL DEFAULT 1,
    prezzo_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (ordini_id) REFERENCES ordini(id) ON DELETE CASCADE,
    FOREIGN KEY (piatti_id) REFERENCES piatti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE recensioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    users_id INT,
    voto INT NOT NULL CHECK (voto BETWEEN 1 AND 5),
    commento TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dati iniziali
INSERT INTO user_groups (nome, descrizione) VALUES
('admin', 'Amministratori del sistema'),
('cameriere', 'Camerieri del ristorante'),
('cuoco', 'Cuochi del ristorante'),
('cliente', 'Clienti registrati');

INSERT INTO services (username) VALUES
('admin.php'),
('cameriere.php'),
('cuoco.php'),
('utente.php');

INSERT INTO services_has_groups (services_username, groups_id) VALUES
('admin.php', 1),
('cameriere.php', 2),
('cuoco.php', 3),
('utente.php', 4);

INSERT INTO categorie (nome, descrizione, ordine) VALUES
('Antipasti', 'Antipasti della casa', 1),
('Primi', 'Primi piatti', 2),
('Secondi', 'Secondi piatti', 3),
('Dolci', 'Dolci e dessert', 4);