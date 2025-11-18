-- Base de données CineClub
CREATE DATABASE IF NOT EXISTS cineclub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cineclub;

-- Table Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('membre', 'admin') DEFAULT 'membre',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Films
CREATE TABLE films (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(200) NOT NULL,
    realisateur VARCHAR(150) NOT NULL,
    annee INT NOT NULL,
    genre VARCHAR(50) NOT NULL,
    synopsis TEXT,
    duree INT,
    statut ENUM('propose', 'en_vote', 'programme', 'visionne') DEFAULT 'propose',
    user_id INT NOT NULL,
    date_proposition DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_statut (statut),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Votes
CREATE TABLE votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    film_id INT NOT NULL,
    date_vote DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (user_id, film_id),
    INDEX idx_film (film_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Seances
CREATE TABLE seances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    film_id INT NOT NULL,
    date_seance DATETIME NOT NULL,
    lieu VARCHAR(200) NOT NULL,
    capacite_max INT DEFAULT 50,
    places_restantes INT DEFAULT 50,
    FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE,
    INDEX idx_date (date_seance),
    INDEX idx_film (film_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Participations
CREATE TABLE participations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    seance_id INT NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seance_id) REFERENCES seances(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (user_id, seance_id),
    INDEX idx_seance (seance_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Reviews
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    film_id INT NOT NULL,
    note INT CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT,
    date_review DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (user_id, film_id),
    INDEX idx_film (film_id),
    INDEX idx_note (note)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion de données de test

-- Admin par défaut (password: admin123)
INSERT INTO users (nom, prenom, email, password, role) VALUES
('Admin', 'CineClub', 'admin@cineclub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Membres de test (password: password)
INSERT INTO users (nom, prenom, email, password, role) VALUES
('Dupont', 'Marie', 'marie.dupont@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'membre'),
('Martin', 'Pierre', 'pierre.martin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'membre'),
('Bernard', 'Sophie', 'sophie.bernard@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'membre');

-- Films proposés
INSERT INTO films (titre, realisateur, annee, genre, synopsis, duree, statut, user_id) VALUES
('Inception', 'Christopher Nolan', 2010, 'Science-Fiction', 'Un voleur qui vole des secrets via le rêve reçoit la tâche inverse de planter une idée dans l''esprit d''un PDG.', 148, 'en_vote', 2),
('Parasite', 'Bong Joon-ho', 2019, 'Thriller', 'Une famille pauvre s''infiltre dans la vie d''une famille riche avec des conséquences inattendues.', 132, 'en_vote', 3),
('La La Land', 'Damien Chazelle', 2016, 'Musical', 'Une pianiste de jazz et une actrice en herbe tombent amoureux à Los Angeles tout en poursuivant leurs rêves.', 128, 'propose', 4),
('Le Parrain', 'Francis Ford Coppola', 1972, 'Crime', 'Le patriarche d''une dynastie criminelle transfère le contrôle de son empire à son fils réticent.', 175, 'visionne', 2),
('Pulp Fiction', 'Quentin Tarantino', 1994, 'Crime', 'Les vies de deux tueurs à gages, d''un boxeur et d''une paire de braqueurs s''entrecroisent.', 154, 'visionne', 3);

-- Votes
INSERT INTO votes (user_id, film_id) VALUES
(2, 1), (3, 1), (4, 1),
(2, 2), (4, 2);

-- Séances
INSERT INTO seances (film_id, date_seance, lieu, capacite_max, places_restantes) VALUES
(4, '2025-11-25 20:00:00', 'Salle Lumière, Cinéma Central', 50, 45),
(5, '2025-12-02 19:30:00', 'Salle Méliès, Cinéma Central', 40, 38);

-- Participations
INSERT INTO participations (user_id, seance_id) VALUES
(2, 1), (3, 1), (4, 1), (2, 2), (4, 2);

-- Reviews
INSERT INTO reviews (user_id, film_id, note, commentaire) VALUES
(2, 4, 5, 'Un chef-d''œuvre absolu ! Performance magistrale de Marlon Brando.'),
(3, 4, 5, 'Le meilleur film de gangsters jamais réalisé. Mise en scène parfaite.'),
(4, 5, 4, 'Dialogues brillants et structure narrative innovante. Du pur Tarantino !'),
(2, 5, 4, 'Film culte qui a révolutionné le cinéma des années 90.');