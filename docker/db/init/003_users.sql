-- Administrator: full control over LandingPageDB
CREATE USER IF NOT EXISTS 'Administrator'@'%' IDENTIFIED BY 'AdministratorPass';
GRANT ALL PRIVILEGES ON LandingPageDB.* TO 'Administrator'@'%';

-- Viewer: read-only on most tables, but can write to user_profiles and user_links
CREATE USER IF NOT EXISTS 'Viewer'@'%' IDENTIFIED BY 'ViewerPass';
GRANT SELECT ON LandingPageDB.* TO 'Viewer'@'%';
GRANT INSERT, UPDATE ON LandingPageDB.user_profiles TO 'Viewer'@'%';
GRANT INSERT, UPDATE, DELETE ON LandingPageDB.user_links TO 'Viewer'@'%';

FLUSH PRIVILEGES;