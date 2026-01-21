-- Database: sanitary_store_db

CREATE DATABASE IF NOT EXISTS sanitary_store_db;
USE sanitary_store_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Suppliers Table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    type ENUM('regular', 'contractor') DEFAULT 'regular',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category_id INT,
    brand VARCHAR(100),
    size VARCHAR(50),
    type VARCHAR(50), -- e.g. Ceramic, Plastic, Steel
    cost_price DECIMAL(10, 2) DEFAULT 0, -- Added cost price
    price DECIMAL(10, 2) NOT NULL,
    quantity INT DEFAULT 0,
    warranty VARCHAR(100),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Sales Table
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    user_id INT, -- Staff who made the sale
    subtotal DECIMAL(10, 2),
    tax DECIMAL(10, 2),
    discount DECIMAL(10, 2),
    total_amount DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sale Items Table
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10, 2), -- Price at the time of sale
    total DECIMAL(10, 2),
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Stock Logs Table (Added)
CREATE TABLE IF NOT EXISTS stock_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT, 
    quantity INT, 
    type ENUM('in', 'out'), 
    reason VARCHAR(255), 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Insert Default Admin User (Password: admin123)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert Sample Categories
INSERT INTO categories (name) VALUES 
('Pipes'), ('Taps'), ('WC/Commode'), ('Wash Basins'), ('Showers'), ('Water Tanks'), ('Accessories');

-- Insert Sample Products
INSERT INTO products (name, category_id, brand, size, type, cost_price, price, quantity, warranty) VALUES 
('PVC Pipe 4 inch', 1, 'Popular', '4 inch', 'PVC', 400.00, 500.00, 100, '5 Years'),
('Chrome Basin Mixer', 2, 'Faisal', 'Standard', 'Brass', 2000.00, 2500.00, 50, '1 Year'),
('Ceramic Commode', 3, 'Porta', 'Standard', 'Ceramic', 10000.00, 12000.00, 20, '10 Years'),
('Fancy Wash Basin', 4, 'Marachi', 'Medium', 'Ceramic', 3500.00, 4500.00, 30, '3 Years');

-- Insert Sample Customers
INSERT INTO customers (name, type) VALUES 
('Walk-in Customer', 'regular'),
('Ali Plumber', 'contractor');
