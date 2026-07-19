-- UTENTI
INSERT INTO users (username, nome, cognome, email, password, ruolo, punti_loyalty) VALUES
                                                                                       ('admin',    'Marco',    'Rossi',    'admin@ristohub.it',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uJ65Td3i', 'admin',     0),
                                                                                       ('mario_c',  'Mario',    'Conti',    'mario@ristohub.it',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uJ65Td3i', 'cameriere', 0),
                                                                                       ('lucia_k',  'Lucia',    'Kovac',    'lucia@ristohub.it',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uJ65Td3i', 'cuoco',     0),
                                                                                       ('anna_v',   'Anna',     'Verdi',    'anna@example.it',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uJ65Td3i', 'cliente',   120),
                                                                                       ('luca_b',   'Luca',     'Bianchi',  'luca@example.it',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uJ65Td3i', 'cliente',   45),
                                                                                       ('sara_m',   'Sara',     'Mancini',  'sara@example.it',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uJ65Td3i', 'cliente',   200);
-- password di tutti: "password"

-- ASSOCIAZIONE UTENTI AI GRUPPI
INSERT INTO users_has_groups (users_id, groups_id) VALUES
                                                       (1, 1), -- admin     → gruppo admin
                                                       (2, 2), -- mario_c   → gruppo cameriere
                                                       (3, 3), -- lucia_k   → gruppo cuoco
                                                       (4, 4), -- anna_v    → gruppo cliente
                                                       (5, 4), -- luca_b    → gruppo cliente
                                                       (6, 4); -- sara_m    → gruppo cliente

-- CATEGORIE (già presenti nel seed, ma per sicurezza)
INSERT IGNORE INTO categorie (nome, descrizione, ordine) VALUES
('Antipasti', 'Antipasti della casa',        1),
('Primi',     'Primi piatti',                2),
('Secondi',   'Secondi piatti',              3),
('Dolci',     'Dolci e dessert',             4);

-- ALLERGENI
INSERT INTO allergeni (nome, icona) VALUES
                                        ('Glutine',    '🌾'),
                                        ('Lattosio',   '🥛'),
                                        ('Uova',       '🥚'),
                                        ('Frutta secca','🥜'),
                                        ('Pesce',      '🐟'),
                                        ('Molluschi',  '🦑'),
                                        ('Crostacei',  '🦐'),
                                        ('Soia',       '🫘'),
                                        ('Senape',     '🌭'),
                                        ('Sedano',     '🥬');

-- INGREDIENTI
INSERT INTO ingredienti (nome, quantita_magazzino, unita_misura, soglia_minima) VALUES
                                                                                    ('Farina 00',         15.0,  'kg',    3.0),
                                                                                    ('Uova fresche',      60.0,  'pz',   10.0),
                                                                                    ('Parmigiano',         4.5,  'kg',    1.0),
                                                                                    ('Guanciale',          3.0,  'kg',    0.5),
                                                                                    ('Pecorino',           2.0,  'kg',    0.5),
                                                                                    ('Pomodori San Marzano',8.0, 'kg',    2.0),
                                                                                    ('Mozzarella',         5.0,  'kg',    1.0),
                                                                                    ('Basilico fresco',    0.5,  'kg',    0.1),
                                                                                    ('Olio EVO',           6.0,  'l',     1.0),
                                                                                    ('Sale',               2.0,  'kg',    0.5),
                                                                                    ('Pepe nero',          0.3,  'kg',    0.1),
                                                                                    ('Aglio',              0.8,  'kg',    0.2),
                                                                                    ('Pancetta',           2.5,  'kg',    0.5),
                                                                                    ('Cipolla',            3.0,  'kg',    0.5),
                                                                                    ('Vino bianco',        4.0,  'l',     1.0),
                                                                                    ('Brodo di carne',     5.0,  'l',     1.0),
                                                                                    ('Riso Carnaroli',     4.0,  'kg',    1.0),
                                                                                    ('Burro',              1.5,  'kg',    0.3),
                                                                                    ('Vitello',            3.0,  'kg',    0.5),
                                                                                    ('Salmone',            2.0,  'kg',    0.5),
                                                                                    ('Limone',             1.0,  'kg',    0.2),
                                                                                    ('Mascarpone',         1.0,  'kg',    0.2),
                                                                                    ('Savoiardi',          0.8,  'kg',    0.2),
                                                                                    ('Caffè espresso',     0.5,  'kg',    0.1),
                                                                                    ('Cacao in polvere',   0.3,  'kg',    0.1),
                                                                                    ('Cioccolato fondente',0.8,  'kg',    0.2),
                                                                                    ('Panna fresca',       2.0,  'l',     0.5),
                                                                                    ('Prosciutto crudo',   2.0,  'kg',    0.5),
                                                                                    ('Rucola',             0.5,  'kg',    0.1),
                                                                                    ('Grana Padano',       2.0,  'kg',    0.5);

-- ASSOCIAZIONE INGREDIENTI → ALLERGENI
INSERT INTO ingredienti_has_allergeni (ingredienti_id, allergeni_id) VALUES
                                                                         (1,  1), -- Farina → Glutine
                                                                         (2,  3), -- Uova → Uova
                                                                         (3,  2), -- Parmigiano → Lattosio
                                                                         (5,  2), -- Pecorino → Lattosio
                                                                         (7,  2), -- Mozzarella → Lattosio
                                                                         (18, 2), -- Burro → Lattosio
                                                                         (22, 2), -- Mascarpone → Lattosio
                                                                         (23, 1), -- Savoiardi → Glutine
                                                                         (23, 3), -- Savoiardi → Uova
                                                                         (27, 2), -- Panna → Lattosio
                                                                         (30, 2); -- Grana Padano → Lattosio

-- PIATTI
INSERT INTO piatti (nome, descrizione, prezzo, categorie_id, img, disponibile) VALUES
                                                                                   ('Bruschetta al pomodoro',    'Pane tostato con pomodorini freschi, basilico e olio EVO',                          6.50,  1, 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=600', 1),
                                                                                   ('Tagliere misto',            'Selezione di salumi e formaggi locali con miele e noci',                            14.00, 1, 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600', 1),
                                                                                   ('Carpaccio di vitello',      'Fettine di vitello crudo con rucola, grana e limone',                               12.00, 1, 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?w=600', 1),
                                                                                   ('Salmone marinato',          'Salmone con aneto, capperi e salsa di senape',                                      11.00, 1, 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600', 1),
                                                                                   ('Cacio e Pepe',              'Spaghetti con pecorino romano e pepe nero macinato fresco',                         13.00, 2, 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=600', 1),
                                                                                   ('Carbonara',                 'Rigatoni con guanciale, uova, pecorino e pepe',                                     14.00, 2, 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=600', 1),
                                                                                   ('Amatriciana',               'Bucatini con guanciale, pomodoro San Marzano e pecorino',                           13.50, 2, 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=600', 1),
                                                                                   ('Risotto alla Milanese',     'Riso Carnaroli con zafferano e midollo, mantecato al burro',                        15.00, 2, 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?w=600', 1),
                                                                                   ('Pappardelle al ragù',       'Pasta all''uovo con ragù di carne lento 4 ore',                                     15.50, 2, 'https://images.unsplash.com/photo-1551183053-bf91798d6c19?w=600', 1),
('Scaloppine al limone',      'Medaglioni di vitello con salsa al limone e capperi',                               18.00, 3, 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600', 1),
('Salmone alla griglia',      'Filetto di salmone con verdure di stagione e limone',                               19.00, 3, 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=600', 1),
('Tagliata di manzo',         'Manzo al sangue con rucola, grana e pomodorini',                                    22.00, 3, 'https://images.unsplash.com/photo-1558030006-450675393462?w=600', 1),
('Pollo alla cacciatora',     'Pollo in umido con olive, capperi e pomodoro',                                      16.00, 3, 'https://images.unsplash.com/photo-1598103442097-8b74394b960e?w=600', 1),
('Tiramisù della casa',       'Ricetta originale con mascarpone, savoiardi e caffè',                                7.00, 4, 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=600', 1),
('Panna cotta ai frutti rossi','Panna cotta con coulis di lamponi e fragole',                                       6.50, 4, 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=600', 1),
('Fondente al cioccolato',    'Tortino con cuore morbido e gelato alla vaniglia',                                   7.50, 4, 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600', 1);

-- ASSOCIAZIONE PIATTI → INGREDIENTI
INSERT INTO piatti_has_ingredienti (piatti_id, ingredienti_id, quantita) VALUES
(1,  6, 0.1), (1,  8, 0.02),(1,  9, 0.02),              -- Bruschetta
(2,  28,0.08),(2,  3, 0.05),                             -- Tagliere
(3,  19,0.1), (3,  29,0.03),(3,  30,0.03),(3,  21,0.05),-- Carpaccio
(4,  20,0.1), (4,  21,0.05),                             -- Salmone marinato
(5,  1, 0.1), (5,  5, 0.05),(5,  11,0.005),             -- Cacio e Pepe
(6,  1, 0.1), (6,  4, 0.06),(6,  2, 0.1), (6,  5, 0.04),-- Carbonara
(7,  1, 0.1), (7,  4, 0.06),(7,  6, 0.15),(7,  5, 0.03),-- Amatriciana
(8,  17,0.08),(8,  16,0.1), (8,  18,0.02),(8,  3, 0.03),-- Risotto
(9,  1, 0.1), (9,  2, 0.1), (9,  13,0.1),               -- Pappardelle
(10, 19,0.15),(10, 21,0.05),(10, 18,0.02),               -- Scaloppine
(11, 20,0.18),(11, 21,0.05),                             -- Salmone griglia
(12, 19,0.2), (12, 29,0.03),(12, 30,0.03),               -- Tagliata
(13, 6, 0.1), (13, 9, 0.02),                             -- Pollo cacciatora
(14, 22,0.1), (14, 23,0.06),(14, 24,0.03),(14, 25,0.01),-- Tiramisù
(15, 27,0.1), (15, 18,0.01),                             -- Panna cotta
(16, 26,0.06),(16, 27,0.05),(16, 2, 0.05);               -- Fondente

-- TAVOLI
INSERT INTO tavoli (numero, capacita_max, posizione, stato) VALUES
(1,  2, 'interno',  'libero'),
(2,  2, 'interno',  'libero'),
(3,  4, 'interno',  'libero'),
(4,  4, 'interno',  'libero'),
(5,  4, 'esterno',  'libero'),
(6,  6, 'interno',  'libero'),
(7,  6, 'esterno',  'libero'),
(8,  8, 'interno',  'libero'),
(9,  2, 'privato',  'libero'),
(10, 10,'sala eventi','libero');

-- PRENOTAZIONI
INSERT INTO prenotazioni (users_id, tavoli_id, nome, telefono, data, ora, persone, note, stato) VALUES
(4, 3, 'Anna Verdi',    '333 1111111', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '20:00', 3, 'Anniversario, gradito un tavolo tranquillo', 'confermata'),
(5, 1, 'Luca Bianchi',  '333 2222222', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00', 2, '',                                           'in_attesa'),
(6, 6, 'Sara Mancini',  '333 3333333', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '20:30', 5, 'Un ospite celiaco',                           'in_attesa'),
(NULL,NULL,'Giuseppe Romano','333 4444444', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:30', 4, '',                                       'in_attesa'),
(4, 4, 'Anna Verdi',    '333 1111111', DATE_SUB(CURDATE(), INTERVAL 7 DAY), '20:00', 2, '',                                           'confermata'),
(5, 2, 'Luca Bianchi',  '333 2222222', DATE_SUB(CURDATE(), INTERVAL 3 DAY), '13:00', 2, '',                                           'confermata');

-- ORDINI COMPLETATI (per le statistiche)
INSERT INTO ordini (tavoli_id, users_id, stato, totale, created_at) VALUES
(3, 4, 'completato', 67.50, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 5, 'completato', 42.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 6, 'completato', 89.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 4, 'completato', 34.00, CURDATE()),
(6, 5, 'completato', 112.50,CURDATE());

-- DETTAGLIO ORDINI
INSERT INTO ordini_has_piatti (ordini_id, piatti_id, quantita, prezzo_unitario) VALUES
(1, 1,  2, 6.50), (1, 6,  2, 14.00),(1, 10, 1, 18.00),(1, 14, 1, 7.00),
(2, 5,  2, 13.00),(2, 11, 1, 19.00),(2, 15, 1, 6.50),
(3, 2,  1, 14.00),(3, 8,  2, 15.00),(3, 12, 2, 22.00),(3, 16, 1, 7.50),
(4, 3,  1, 12.00),(4, 7,  2, 13.50),(4, 14, 1, 7.00),
(5, 2,  2, 14.00),(5, 9,  3, 15.50),(5, 13, 2, 16.00),(5, 14, 3, 7.00),(5, 15, 2, 6.50);

-- AGGIORNA I TOTALI (ricalcola dalla tabella dettaglio)
UPDATE ordini o SET totale = (
    SELECT SUM(quantita * prezzo_unitario)
    FROM ordini_has_piatti WHERE ordini_id = o.id
);

-- AGGIUNGI PUNTI LOYALTY AI CLIENTI CHE HANNO ORDINATO
UPDATE users SET punti_loyalty = 120 WHERE username = 'anna_v';
UPDATE users SET punti_loyalty = 45  WHERE username = 'luca_b';
UPDATE users SET punti_loyalty = 200 WHERE username = 'sara_m';

-- RECENSIONI
INSERT INTO recensioni (users_id, voto, commento, created_at) VALUES
(4, 5, 'Cena meravigliosa, la carbonara è la migliore che abbia mai mangiato. Torneremo sicuramente!',    DATE_SUB(NOW(), INTERVAL 5 DAY)),
(5, 4, 'Ottimo ristorante, ambiente caldo e accogliente. Il tiramisù è da sogno.',                        DATE_SUB(NOW(), INTERVAL 3 DAY)),
(6, 5, 'Personale gentilissimo e cucina autentica. La tagliata era perfetta.',                            DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 5, 'Seconda visita, ancora una volta impeccabile. Il risotto alla milanese è eccezionale.',           DATE_SUB(NOW(), INTERVAL 8 DAY)),
(NULL,4,'Ottimo rapporto qualità-prezzo, porzioni generose e sapori veri. Consigliato!',                  DATE_SUB(NOW(), INTERVAL 1 DAY));