-- Importáld phpMyAdminban az SQL vagy az Importálás fülön.
CREATE DATABASE IF NOT EXISTS pofazo3000
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pofazo3000;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    last_seen DATETIME NOT NULL,
    INDEX (last_seen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    message VARCHAR(500) NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT messages_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
