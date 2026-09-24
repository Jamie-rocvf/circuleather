-- Gebruikers-tabel voor de inlog/registratie-functionaliteit, plus 4 testaccounts.
-- mag_insert / mag_orders bepalen of de "insert"- en "orders"-knop in de header zichtbaar zijn.
-- Er is (nog) geen adminscherm om dit aan te passen: dat doe je voorlopig rechtstreeks in
-- phpMyAdmin door de 0/1-waarde in die kolommen aan te passen per gebruiker.

CREATE TABLE gebruikers (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    mag_insert TINYINT(1) NOT NULL DEFAULT 0,
    mag_orders TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

-- Wachtwoord voor alle 4 testaccounts: Welkom01!
INSERT INTO gebruikers (username, password, mag_insert, mag_orders) VALUES
('admin', '$2b$12$tLwsZC5Iwlputwj/uUS3R.zOWzE1WWGdAFHj4JB83EMzQlKCzmeQu', 1, 1),
('invoerder', '$2b$12$tLwsZC5Iwlputwj/uUS3R.zOWzE1WWGdAFHj4JB83EMzQlKCzmeQu', 1, 0),
('orderbeheerder', '$2b$12$tLwsZC5Iwlputwj/uUS3R.zOWzE1WWGdAFHj4JB83EMzQlKCzmeQu', 0, 1),
('viewer', '$2b$12$tLwsZC5Iwlputwj/uUS3R.zOWzE1WWGdAFHj4JB83EMzQlKCzmeQu', 0, 0);
