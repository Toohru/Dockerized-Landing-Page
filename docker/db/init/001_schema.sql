USE LandingPageDB;

CREATE TABLE def_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon_path VARCHAR(255) DEFAULT NULL
);

CREATE TABLE themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    colour1 BINARY(3) NOT NULL,
    colour2 BINARY(3) NOT NULL,
    colour3 BINARY(3) NOT NULL,
    primary_text_colour BINARY(3) NOT NULL,
    secondary_text_colour BINARY(3) NOT NULL,
    highlight_colour BINARY(3) NOT NULL
);

CREATE TABLE settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL
);

INSERT INTO settings (setting_key, setting_value) VALUES
    ('active_theme', '1'),
    ('active_background', 'blobs');