INSERT INTO roles (name) VALUES ('IT'), ('Teacher'), ('Student'), ('Administration');

INSERT INTO blocks (`key`, name) VALUES
('shortcuts', 'Quick Links'),
('notices', 'Notices'),
('canteen', 'Canteen Menu'),
('phonelist', 'Phone List');

INSERT INTO role_layout (role_id, block_id, display_order) VALUES
(1, 1, 1),
(1, 2, 2),
(1, 3, 3),
(2, 2, 1),
(2, 4, 2);

INSERT INTO def_links (name, url) VALUES
('Google', 'https://www.google.com'),
('Facebook', 'https://www.facebook.com'),
('Twitter', 'https://www.twitter.com'),
('Instagram', 'https://www.instagram.com'),
('Youtube', 'https://www.youtube.com');