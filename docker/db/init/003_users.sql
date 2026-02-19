-- Administrator: full control over LandingPageDB
CREATE USER IF NOT EXISTS 'Administrator'@'%' IDENTIFIED BY 'AdministratorPass';
GRANT ALL PRIVILEGES ON LandingPageDB.* TO 'Administrator'@'%';

-- Viewer: read-only access to LandingPageDB
CREATE USER IF NOT EXISTS 'Viewer'@'%' IDENTIFIED BY 'ViewerPass';
GRANT SELECT ON LandingPageDB.* TO 'Viewer'@'%';

FLUSH PRIVILEGES;