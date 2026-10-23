CREATE DATABASE IF NOT EXISTS kebab404
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_polish_ci;
USE kebab404;

CREATE TABLE IF NOT EXISTS produkty (
  id INT NOT NULL AUTO_INCREMENT,
  nazwa VARCHAR(100) NOT NULL,
  cena DECIMAL(8,2) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS zamowienia (
  id INT NOT NULL AUTO_INCREMENT,
  klient VARCHAR(50) NOT NULL,
  produkt_id INT NOT NULL,
  ilosc INT NOT NULL,
  sos VARCHAR(20) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'NOWE',
  data_zamowienia TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_zamowienia_produkty FOREIGN KEY (produkt_id)
    REFERENCES produkty(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO produkty (id, nazwa, cena) VALUES
  (1, 'Kebab NullPointer', 24.00),
  (2, 'SQL Deluxe', 28.00),
  (3, 'JavaScript Wrap', 32.00),
  (4, 'CSS Frytki', 12.00),
  (5, 'Python Shake', 15.00)
ON DUPLICATE KEY UPDATE nazwa = VALUES(nazwa), cena = VALUES(cena);

INSERT INTO zamowienia (klient, produkt_id, ilosc, sos, status)
SELECT 'Kubuś', 2, 2, 'Ostry', 'NOWE'
WHERE NOT EXISTS (SELECT 1 FROM zamowienia WHERE klient = 'Kubuś' AND produkt_id = 2);
INSERT INTO zamowienia (klient, produkt_id, ilosc, sos, status)
SELECT 'DebugMaster', 4, 3, 'Łagodny', 'GOTOWE'
WHERE NOT EXISTS (SELECT 1 FROM zamowienia WHERE klient = 'DebugMaster' AND produkt_id = 4);
INSERT INTO zamowienia (klient, produkt_id, ilosc, sos, status)
SELECT 'Ada', 1, 1, 'Atomowy', 'NOWE'
WHERE NOT EXISTS (SELECT 1 FROM zamowienia WHERE klient = 'Ada' AND produkt_id = 1);
