CREATE DATABASE match_game;

USE match_game;

CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    score VARCHAR(10) NOT NULL,
    first_scorer VARCHAR(50) NOT NULL,
    first_assist VARCHAR(50) NOT NULL,
    points INT DEFAULT 0,
    FOREIGN KEY (player_id) REFERENCES players(id)
);

CREATE TABLE result (
    id INT PRIMARY KEY,
    score VARCHAR(10) NOT NULL,
    first_scorer VARCHAR(50) NOT NULL,
    first_assist VARCHAR(50) NOT NULL
);