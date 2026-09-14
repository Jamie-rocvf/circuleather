-- Dummy data voor de `voorraad`-tabel (19 rijen)
-- Importeren via phpMyAdmin (tabblad Import / SQL) of:
--   docker exec -i <mysql-container> mysql -uroot -ppassword circuleather < voorraad_seed.sql

INSERT INTO voorraad (leertype, dikteMM, maatCMXCM, gewichtG, kleur, prijs) VALUES
('koe', 2, 50, 1500.0, 'bruin', 25.24),
('schaap', 1, 40, 600.5, 'wit', 18.50),
('geit', 1, 35, 450.0, 'zwart', 15.75),
('varken', 2, 45, 700.0, 'roze', 12.99),
('hert', 3, 60, 1800.0, 'donkerbruin', 34.90),
('paard', 4, 70, 2200.0, 'zwart', 42.00),
('buffel', 5, 90, 3200.0, 'grijs', 55.60),
('kalf', 1, 40, 500.0, 'beige', 20.10),
('koe', 3, 55, 1650.0, 'cognac', 28.75),
('schaap', 2, 42, 620.0, 'grijs', 19.30),
('geit', 2, 38, 470.0, 'rood', 17.20),
('varken', 1, 40, 630.0, 'wit', 11.50),
('hert', 4, 65, 1950.0, 'bruin', 37.40),
('paard', 3, 68, 2100.0, 'camel', 40.25),
('buffel', 4, 85, 3000.0, 'zwart', 52.00),
('kalf', 2, 42, 520.0, 'grijs', 21.60),
('koe', 4, 60, 1750.0, 'zwart', 31.10),
('schaap', 1, 38, 590.0, 'beige', 17.80),
('geit', 3, 50, 500.0, 'donkerbruin', 22.95);
