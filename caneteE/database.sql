CREATE DATABASE Events;
USE Events;

-- 1. Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    event_date DATE NOT NULL,
    location VARCHAR(100),
    reg_fee DECIMAL(10,2) NOT NULL,
    status TINYINT(1) DEFAULT 1
);

-- 2. Participants table
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(100),
    contact_no VARCHAR(30),
    status TINYINT(1) DEFAULT 1
);

-- 3. Registrations table
CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    participant_id INT NOT NULL,
    reg_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    discount_rate DECIMAL(5,2) DEFAULT 0,
    fee_original DECIMAL(10,2) NOT NULL,
    fee_paid DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (participant_id) REFERENCES participants(id)
);
