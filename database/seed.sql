INSERT INTO producto (nombre, descripcion, precio, stock, stock_inicial, ean13, imagen) VALUES
('Eclipse Protocol', 'Aventura espacial cooperativa de exploración y estrategia.', 39.95, 18, 18, '8412345678905', NULL),
('Neon Apex', 'Carreras arcade nocturnas con circuitos urbanos y desafíos contrarreloj.', 29.90, 24, 24, '9780201379624', NULL),
('Verdant Realms', 'RPG de fantasía centrado en decisiones, exploración y creación de equipo.', 49.99, 12, 12, '4006381333931', NULL),
('Signal Lost', 'Thriller narrativo de ciencia ficción para una persona.', 19.95, 30, 30, '5901234123457', NULL),
('Forge Tactics', 'Estrategia por turnos con partidas rápidas y unidades personalizables.', 34.50, 16, 16, '5012345678900', NULL)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre), descripcion = VALUES(descripcion), precio = VALUES(precio),
    stock = VALUES(stock), stock_inicial = VALUES(stock_inicial), imagen = VALUES(imagen);
