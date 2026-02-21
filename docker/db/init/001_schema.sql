USE LandingPageDB;

-- ── Site settings (global) ──
CREATE TABLE settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL
);

INSERT INTO settings (setting_key, setting_value) VALUES
    ('active_theme', '1'),
    ('active_background', 'none'),
    ('setup_complete', '0'),
    ('school_name', ''),
    ('auth_mode', 'none'),
    ('ldap_host', ''),
    ('ldap_port', '389'),
    ('ldap_base_dn', ''),
    ('ldap_bind_dn', ''),
    ('ldap_bind_password', ''),
    ('ldap_user_filter', '(sAMAccountName={username})'),
    ('ldap_domain', '');

-- ── Default links (site-wide, managed by admin) ──
CREATE TABLE def_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(2048) NOT NULL,
    icon_path VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0
);

-- ── Themes (shared across all users) ──
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

-- ── User profiles (auto-created on first visit) ──
CREATE TABLE user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    display_name VARCHAR(255) DEFAULT NULL,
    theme_id INT DEFAULT NULL,
    background_key VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE SET NULL
);

-- ── User-specific links (overrides default links per user) ──
CREATE TABLE user_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(2048) NOT NULL,
    icon_path VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES user_profiles(id) ON DELETE CASCADE
);