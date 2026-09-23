-- Run this once (phpMyAdmin → SQL tab, or: mysql -u root < app/database/schema.sql)

CREATE DATABASE IF NOT EXISTS registration
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE registration;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(30)  NOT NULL UNIQUE,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);
