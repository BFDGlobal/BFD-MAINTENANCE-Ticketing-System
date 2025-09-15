-- Create database and use it
CREATE DATABASE IF NOT EXISTS repair_ticketing;
USE repair_ticketing;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client') NOT NULL
);

-- Create tickets table with new fields
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    department VARCHAR(100) NOT NULL,
    action_taken TEXT NOT NULL,
    status ENUM('Open', 'In Progress', 'Closed') DEFAULT 'Open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_at DATETIME NULL,
    downtime INT DEFAULT NULL,  
    -- New fields
    date DATE NULL,
    w0_number VARCHAR(50) NULL,
    accomplished_by VARCHAR(100) NULL,
    bfd_code VARCHAR(50) NULL,
    requested_by VARCHAR(100) NULL,
    remarks TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert some sample users for testing
INSERT INTO users (username, password, role) VALUES
('admin', MD5('admin123'), 'admin'),
('IT', MD5('it123'), 'admin2'),
('client1', MD5('client123'), 'client'),
('Emma', MD5('emma123'), 'client');

-- Insert a sample ticket with the new fields
INSERT INTO tickets (user_id, title, description, department, action_taken, date, w0_number, accomplished_by, bfd_code, requested_by, remarks) 
VALUES
(3, 'Printer Repair', 'The printer is not responding to commands', 'IT', 'Reboot the system and check the cables', '2025-09-12', 'W0-12345', 'John Doe', 'BFD001', 'Client1', 'Printer was fixed and tested');

