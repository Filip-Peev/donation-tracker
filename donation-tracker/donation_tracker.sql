CREATE DATABASE IF NOT EXISTS donation_tracker
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE donation_tracker;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    contact_name VARCHAR(100),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    type ENUM('laptop', 'router', 'tablet', 'desktop', 'monitor', 'other') NOT NULL DEFAULT 'other',
    serial_number VARCHAR(100),
    description TEXT,
    donor_name VARCHAR(100),
    donor_email VARCHAR(100),
    donation_date DATE NOT NULL,
    location_id INT,
    status ENUM('donated', 'in_use', 'returned', 'retired', 'lost') NOT NULL DEFAULT 'donated',
    qr_token VARCHAR(64) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inspections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    inspector_name VARCHAR(100) DEFAULT NULL,
    inspected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('working', 'damaged', 'missing', 'replaced') NOT NULL,
    photo_path VARCHAR(255),
    notes TEXT,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB;
