-- Scout Warehouse Management System Database Schema
-- This script creates all necessary tables and adds an admin user

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create warehouses table
CREATE TABLE warehouses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create divisions table
CREATE TABLE divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create items table
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warehouse_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    quantity INT NOT NULL DEFAULT 1,
    is_borrowed TINYINT(1) DEFAULT 0,
    borrowed_by INT DEFAULT NULL,
    borrowed_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    division_id INT,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (borrowed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin user
-- Default credentials: admin@example.com / password123
-- Password is hashed with password_hash() in PHP, this is a sample hash for 'password123'
INSERT INTO users (name, email, password, is_admin) VALUES 
('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Add sample warehouses
INSERT INTO warehouses (name, location, description, created_by) VALUES
('Main Warehouse', 'Scout Clubhouse', 'Main warehouse for scout equipment', 1),
('Camping Equipment', 'Clubhouse Basement', 'Storage for camping equipment', 1);

-- Add sample items
INSERT INTO items (warehouse_id, name, description, quantity, created_by) VALUES
(1, 'Tents', 'Two-person tents', 10, 1),
(1, 'Cooking Pots', 'Stainless steel cooking pots', 5, 1),
(1, 'Ropes', '30m climbing ropes', 3, 1),
(2, 'Axes', 'Wood chopping axes', 4, 1),
(2, 'Compasses', 'Orienteering compasses', 15, 1);

-- Create an index on commonly searched fields
CREATE INDEX idx_items_warehouse ON items(warehouse_id);
CREATE INDEX idx_items_division ON items(division_id);
CREATE INDEX idx_items_borrowed ON items(is_borrowed, borrowed_by);

-- Display success message
SELECT 'Database setup completed successfully!' AS 'Message';
SELECT 'Default admin login: admin@example.com / password123' AS 'Admin Credentials';