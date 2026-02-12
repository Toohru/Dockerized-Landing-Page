USE LandingPageDB;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    extension VARCHAR(50) NOT NULL,
    mobile VARCHAR(50) NOT NULL,
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(50) NOT NULL,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE role_layout (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    block_id INT NOT NULL,
    display_order INT NOT NULL,
    enabled BOOLEAN NOT NULL DEFAULT 1,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (block_id) REFERENCES blocks(id)
);

CREATE TABLE def_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    url VARCHAR(255) NOT NULL
);
