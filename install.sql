-- install.sql — School Landing Page database initialisation
-- Run via install.ps1, or manually:
--   mysql -u root -p < install.sql
--
-- App DB users are created by install.ps1 (it generates random passwords).
-- This file only handles schema and seed data.

CREATE DATABASE IF NOT EXISTS LandingPageDB
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE LandingPageDB;

-- ─────────────────────────────────────────────────────────────────────────────
-- SCHEMA
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS settings (
    setting_key   VARCHAR(64)   NOT NULL PRIMARY KEY,
    setting_value TEXT          NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS def_links (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(128)  NOT NULL,
    url         VARCHAR(2048) NOT NULL,
    icon_path   VARCHAR(512)  NULL,
    sort_order  INT           NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS themes (
    id                    INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name                  VARCHAR(64)  NOT NULL,
    colour1               BINARY(3)    NOT NULL COMMENT 'background',
    colour2               BINARY(3)    NOT NULL COMMENT 'foreground',
    colour3               BINARY(3)    NOT NULL COMMENT 'card/container',
    highlight_colour      BINARY(3)    NOT NULL,
    primary_text_colour   BINARY(3)    NOT NULL,
    secondary_text_colour BINARY(3)    NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_profiles (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username       VARCHAR(64)   NOT NULL UNIQUE,
    display_name   VARCHAR(128)  NULL,
    theme_id       INT UNSIGNED  NULL,
    background_key VARCHAR(32)   NULL,
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_links (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED  NOT NULL,
    name       VARCHAR(128)  NOT NULL,
    url        VARCHAR(2048) NOT NULL,
    icon_path  VARCHAR(512)  NULL,
    sort_order INT           NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES user_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────────────
-- DEFAULT SETTINGS
-- ─────────────────────────────────────────────────────────────────────────────

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('setup_complete',    '0'),
    ('school_name',       'My School'),
    ('auth_mode',         'remote_user'),
    ('active_theme',      '1'),
    ('active_background', 'blobs'),
    ('ldap_host',         ''),
    ('ldap_port',         '389'),
    ('ldap_base_dn',      ''),
    ('ldap_bind_dn',      ''),
    ('ldap_bind_password',''),
    ('ldap_user_filter',  '(sAMAccountName={username})'),
    ('ldap_domain',       '');

-- ─────────────────────────────────────────────────────────────────────────────
-- DEFAULT LINKS (WA DOE school systems)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT IGNORE INTO def_links (id, name, url, sort_order) VALUES
    (1,  'Connect',    'https://connect.det.wa.edu.au',                      1),
    (2,  'DAM',        'https://dam.det.wa.edu.au',                          2),
    (3,  'HRMIS',      'https://hrmis2.eduworks.wa.gov.au',                  3),
    (4,  'OneDrive',   'https://onedrive.live.com',                          4),
    (5,  'IKON',       'https://ikon.det.wa.edu.au',                         5),
    (6,  'RTP',        'https://rtp.det.wa.edu.au',                          6),
    (7,  'Teams',      'https://teams.microsoft.com',                        7),
    (8,  'YouTube',    'https://youtube.com',                                8),
    (9,  'Canva',      'https://canva.com',                                  9),
    (10, 'Office 365', 'https://office.com',                                10),
    (11, 'CSC SSH',    'https://ssh.csc.det.wa.edu.au',                     11),
    (12, 'Outlook',    'https://outlook.office.com',                        12);

-- ─────────────────────────────────────────────────────────────────────────────
-- THEMES (15 pre-built + 1 default)
-- Colours stored as BINARY(3): 3 raw bytes = RGB
-- ─────────────────────────────────────────────────────────────────────────────

-- Helper: UNHEX('RRGGBB') converts hex colour to 3-byte binary

INSERT IGNORE INTO themes
    (id, name, colour1, colour2, colour3, highlight_colour, primary_text_colour, secondary_text_colour)
VALUES
-- 1  Midnight Neon
(1,  'Midnight Neon',      UNHEX('0d0d1a'), UNHEX('1a1a2e'), UNHEX('16213e'), UNHEX('e94560'), UNHEX('eaeaea'), UNHEX('a0a0b0')),
-- 2  Warm Minimal
(2,  'Warm Minimal',       UNHEX('fdf6ec'), UNHEX('f5ebe0'), UNHEX('ede0d4'), UNHEX('d4a373'), UNHEX('3d2b1f'), UNHEX('7a5c45')),
-- 3  Forest Calm
(3,  'Forest Calm',        UNHEX('1b2d1b'), UNHEX('2d4a2d'), UNHEX('3a5a3a'), UNHEX('7ec850'), UNHEX('e8f5e8'), UNHEX('a8c8a8')),
-- 4  Cyberpunk Pop
(4,  'Cyberpunk Pop',      UNHEX('0f0e17'), UNHEX('1a1a2e'), UNHEX('16213e'), UNHEX('f7c59f'), UNHEX('fffffe'), UNHEX('a7a9be')),
-- 5  Ocean Breeze
(5,  'Ocean Breeze',       UNHEX('e8f4f8'), UNHEX('d6ecf3'), UNHEX('c4e4ed'), UNHEX('0077b6'), UNHEX('03045e'), UNHEX('0077b6')),
-- 6  Coffee House
(6,  'Coffee House',       UNHEX('2c1a0e'), UNHEX('3d2b1f'), UNHEX('4a3728'), UNHEX('c9833a'), UNHEX('f5e6d3'), UNHEX('b8967a')),
-- 7  Modern Tech
(7,  'Modern Tech',        UNHEX('f0f4f8'), UNHEX('e2e8f0'), UNHEX('cbd5e0'), UNHEX('4299e1'), UNHEX('1a202c'), UNHEX('4a5568')),
-- 8  Pastel UI
(8,  'Pastel UI',          UNHEX('fff0f3'), UNHEX('fce4ec'), UNHEX('f8bbd0'), UNHEX('e91e8c'), UNHEX('3e0022'), UNHEX('880e4f')),
-- 9  Desert Sunset
(9,  'Desert Sunset',      UNHEX('2d1b0e'), UNHEX('4a2c0e'), UNHEX('6b3a1a'), UNHEX('ff7043'), UNHEX('fff8f0'), UNHEX('ffccbc')),
-- 10 Clean Corporate
(10, 'Clean Corporate',    UNHEX('f8f9fa'), UNHEX('e9ecef'), UNHEX('dee2e6'), UNHEX('0d6efd'), UNHEX('212529'), UNHEX('6c757d')),
-- 11 Retro Terminal
(11, 'Retro Terminal',     UNHEX('001100'), UNHEX('002200'), UNHEX('003300'), UNHEX('00ff41'), UNHEX('00ff41'), UNHEX('00bb2d')),
-- 12 Luxury Dark
(12, 'Luxury Dark',        UNHEX('0a0a0a'), UNHEX('1a1a1a'), UNHEX('242424'), UNHEX('c9a96e'), UNHEX('f5f5f5'), UNHEX('999999')),
-- 13 Soft Nature
(13, 'Soft Nature',        UNHEX('f4f9f0'), UNHEX('e8f3e0'), UNHEX('d4e8c8'), UNHEX('5a8a3c'), UNHEX('1e3d0f'), UNHEX('4a7030')),
-- 14 Bold Editorial
(14, 'Bold Editorial',     UNHEX('1c1c1c'), UNHEX('2a2a2a'), UNHEX('383838'), UNHEX('ff4500'), UNHEX('ffffff'), UNHEX('aaaaaa')),
-- 15 Playful Gradient
(15, 'Playful Gradient',   UNHEX('1a0533'), UNHEX('2d0b5c'), UNHEX('3d1275'), UNHEX('ff6b9d'), UNHEX('ffffff'), UNHEX('cc99ee'));
