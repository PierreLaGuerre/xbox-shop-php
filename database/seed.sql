INSERT INTO producto (nombre, descripcion, precio, stock, stock_inicial, ean13, imagen) VALUES
('Eclipse Protocol', 'Co-op space adventure focused on exploration and strategy.', 39.95, 18, 18, '8412345678905', NULL),
('Neon Apex', 'Night arcade racing with urban tracks and time-trial challenges.', 29.90, 24, 24, '9780201379624', NULL),
('Verdant Realms', 'Fantasy RPG centred on decisions, exploration and party building.', 49.99, 12, 12, '4006381333931', NULL),
('Signal Lost', 'Single-player narrative sci-fi thriller.', 19.95, 30, 30, '5901234123457', NULL),
('Forge Tactics', 'Turn-based strategy with quick matches and customisable units.', 34.50, 16, 16, '5012345678900', NULL),
('Crimson Circuit', 'Arena shooter with fast movement, neon arenas and local score chasing.', 24.95, 20, 20, '7333412345678', NULL),
('Orbital Drift', 'Zero-gravity racing with boost gates, rivals and split-second shortcuts.', 27.50, 14, 14, '7351357913578', NULL),
('Byte Raiders', 'Retro action adventure about hacking terminals and looting corrupted vaults.', 22.90, 22, 22, '7398765432109', NULL),
('Shadow Co-op', 'Stealth missions for two players with gadgets, timing puzzles and silent takedowns.', 44.95, 10, 10, '7311122233348', NULL)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre), descripcion = VALUES(descripcion), precio = VALUES(precio),
    stock = VALUES(stock), stock_inicial = VALUES(stock_inicial), imagen = VALUES(imagen);
