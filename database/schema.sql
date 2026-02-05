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
    name VARCHAR(100) NOT NULL,
    top_list VARCHAR(50) DEFAULT 'sanitary'
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
    balance DECIMAL(10, 2) DEFAULT 0,
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
    paid_amount DECIMAL(10, 2) DEFAULT 0,
    due_amount DECIMAL(10, 2) DEFAULT 0,
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

-- Insert Categories
INSERT INTO categories (name) VALUES 
('Sanitary');

-- Insert Products (PPR-C Pipes and Fittings)
INSERT INTO products (name, category_id, brand, size, type, cost_price, price, quantity, warranty) VALUES 
-- Pipes
('PPR-C Pipe', 1, 'MNR', '25mm x 900mm', 'PPR-C', 0.00, 900.00, 0, NULL),
('PPR-C Pipe', 1, 'MNR', '25mm x 1000mm', 'PPR-C', 0.00, 1000.00, 0, NULL),
('PPR-C Pipe', 1, 'MNR', '32mm x 1300mm', 'PPR-C', 0.00, 1300.00, 0, NULL),
('PPR-C Pipe', 1, 'MNR', '32mm x 1400mm', 'PPR-C', 0.00, 1400.00, 0, NULL),
('PPR-C Pipe', 1, 'MNR', '40mm x 2200mm', 'PPR-C', 0.00, 2200.00, 0, NULL),
('PPR-C Pipe', 1, 'MNR', '40mm x 2300mm', 'PPR-C', 0.00, 2300.00, 0, NULL),
('PPR-C Pipe', 1, 'MNR', '63mm x 5500mm', 'PPR-C', 0.00, 5500.00, 0, NULL),

-- Pipe Clamp
('Pipe Clamp', 1, 'MNR', '25mm', 'PPR-C', 0.00, 35.00, 0, NULL),
('Pipe Clamp', 1, 'MNR', '32mm', 'PPR-C', 0.00, 39.00, 0, NULL),
('Pipe Clamp', 1, 'MNR', '40mm', 'PPR-C', 0.00, 53.00, 0, NULL),

-- Plastic Union
('Plastic Union', 1, 'MNR', '25mm', 'PPR-C', 0.00, 211.00, 0, NULL),
('Plastic Union', 1, 'MNR', '32mm', 'PPR-C', 0.00, 288.00, 0, NULL),
('Plastic Union', 1, 'MNR', '40mm', 'PPR-C', 0.00, 560.00, 0, NULL),

-- P To B Union
('P To B Union', 1, 'MNR', '25mm x 1/2', 'PPR-C', 0.00, 645.00, 0, NULL),
('P To B Union', 1, 'MNR', '25mm x 3/4', 'PPR-C', 0.00, 645.00, 0, NULL),
('P To B Union', 1, 'MNR', '32mm x 1/2', 'PPR-C', 0.00, 645.00, 0, NULL),
('P To B Union', 1, 'MNR', '32mm x 3/4', 'PPR-C', 0.00, 645.00, 0, NULL),
('P To B Union', 1, 'MNR', '32mm x 1', 'PPR-C', 0.00, 645.00, 0, NULL),
('P To B Union', 1, 'MNR', '40mm x 1 1/4', 'PPR-C', 0.00, 1620.00, 0, NULL),
('P To B Union', 1, 'MNR', '40mm x 1', 'PPR-C', 0.00, 1620.00, 0, NULL),

-- Return Valve
('Return Valve', 1, 'MNR', '25mm', 'PPR-C', 0.00, 2255.00, 0, NULL),
('Return Valve', 1, 'MNR', '32mm', 'PPR-C', 0.00, 2298.00, 0, NULL),
('Return Valve', 1, 'MNR', '40mm', 'PPR-C', 0.00, 4680.00, 0, NULL),

-- Ball Valve
('Ball Valve', 1, 'MNR', '25mm', 'PPR-C', 0.00, 2120.00, 0, NULL),
('Ball Valve', 1, 'MNR', '32mm', 'PPR-C', 0.00, 2173.00, 0, NULL),
('Ball Valve', 1, 'MNR', '40mm', 'PPR-C', 0.00, 4505.00, 0, NULL),

-- P Ball Valve
('P Ball Valve', 1, 'MNR', '25mm', 'PPR-C', 0.00, 682.00, 0, NULL),
('P Ball Valve', 1, 'MNR', '32mm', 'PPR-C', 0.00, 1148.00, 0, NULL),
('P Ball Valve', 1, 'MNR', '40mm', 'PPR-C', 0.00, 1148.00, 0, NULL),

-- End Plug
('End Plug', 1, 'MNR', '25mm', 'PPR-C', 0.00, 30.00, 0, NULL),
('End Plug', 1, 'MNR', '32mm', 'PPR-C', 0.00, 39.00, 0, NULL),
('End Plug', 1, 'MNR', '40mm', 'PPR-C', 0.00, 48.00, 0, NULL),

-- End Cap
('End Cap', 1, 'MNR', '25mm', 'PPR-C', 0.00, 38.00, 0, NULL),
('End Cap', 1, 'MNR', '32mm', 'PPR-C', 0.00, 72.00, 0, NULL),
('End Cap', 1, 'MNR', '40mm', 'PPR-C', 0.00, 138.00, 0, NULL),

-- Cross Tee
('Cross Tee', 1, 'MNR', '25mm', 'PPR-C', 0.00, 162.00, 0, NULL),
('Cross Tee', 1, 'MNR', '32mm', 'PPR-C', 0.00, 249.00, 0, NULL),
('Cross Tee', 1, 'MNR', '40mm', 'PPR-C', 0.00, 249.00, 0, NULL),

-- Elbow 45
('Elbow 45', 1, 'MNR', '25mm', 'PPR-C', 0.00, 83.00, 0, NULL),
('Elbow 45', 1, 'MNR', '32mm', 'PPR-C', 0.00, 132.00, 0, NULL),
('Elbow 45', 1, 'MNR', '40mm', 'PPR-C', 0.00, 208.00, 0, NULL),

-- Elbow (90 degree)
('Elbow', 1, 'MNR', '25mm', 'PPR-C', 0.00, 55.00, 0, NULL),
('Elbow', 1, 'MNR', '32mm', 'PPR-C', 0.00, 109.00, 0, NULL),
('Elbow', 1, 'MNR', '40mm', 'PPR-C', 0.00, 211.00, 0, NULL),

-- Female Tee
('Female Tee', 1, 'MNR', '25mm x 1/2', 'PPR-C', 0.00, 316.00, 0, NULL),
('Female Tee', 1, 'MNR', '25mm x 3/4', 'PPR-C', 0.00, 378.00, 0, NULL),
('Female Tee', 1, 'MNR', '32mm x 1/2', 'PPR-C', 0.00, 430.00, 0, NULL),
('Female Tee', 1, 'MNR', '40mm x 1 1/4', 'PPR-C', 0.00, 1398.00, 0, NULL),

-- Male Socket
('Male Socket', 1, 'MNR', '25mm x 1/2', 'PPR-C', 0.00, 370.00, 0, NULL),
('Male Socket', 1, 'MNR', '32mm x 1', 'PPR-C', 0.00, 737.00, 0, NULL),
('Male Socket', 1, 'MNR', '40mm x 1 1/4', 'PPR-C', 0.00, 1808.00, 0, NULL),

-- Female Socket
('Female Socket', 1, 'MNR', '25mm x 1/2', 'PPR-C', 0.00, 264.00, 0, NULL),
('Female Socket', 1, 'MNR', '32mm x 1/2', 'PPR-C', 0.00, 395.00, 0, NULL),
('Female Socket', 1, 'MNR', '40mm x 1 1/4', 'PPR-C', 0.00, 1346.00, 0, NULL),

-- Cross Bend
('Cross Bend', 1, 'MNR', '25mm', 'PPR-C', 0.00, 146.00, 0, NULL),
('Cross Bend', 1, 'MNR', '32mm', 'PPR-C', 0.00, 222.00, 0, NULL),
('Cross Bend', 1, 'MNR', '40mm', 'PPR-C', 0.00, 329.00, 0, NULL),

-- Tee
('Tee', 1, 'MNR', '25mm', 'PPR-C', 0.00, 77.00, 0, NULL),
('Tee', 1, 'MNR', '32mm', 'PPR-C', 0.00, 136.00, 0, NULL),
('Tee', 1, 'MNR', '40mm', 'PPR-C', 0.00, 242.00, 0, NULL),

-- Reducer Elbow
('Reducer Elbow', 1, 'MNR', '32mm x 25mm', 'PPR-C', 0.00, 104.00, 0, NULL),
('Reducer Elbow', 1, 'MNR', '40mm x 32mm', 'PPR-C', 0.00, 196.00, 0, NULL),
('Reducer Elbow', 1, 'MNR', '40mm x 25mm', 'PPR-C', 0.00, 208.00, 0, NULL),

-- Wall Shower
('Wall Shower Adjustment', 1, 'MNR', '25mm x 1/2', 'PPR-C', 0.00, 668.00, 0, NULL),
('Wall Shower Adjustment', 1, 'MNR', '25mm x 3/4', 'PPR-C', 0.00, 734.00, 0, NULL),

-- Socket
('Socket', 1, 'MNR', '25mm', 'PPR-C', 0.00, 48.00, 0, NULL),
('Socket', 1, 'MNR', '32mm', 'PPR-C', 0.00, 75.00, 0, NULL),
('Socket', 1, 'MNR', '40mm', 'PPR-C', 0.00, 138.00, 0, NULL),

-- Reducer Tee
('Reducer Tee', 1, 'MNR', '32mm x 25mm', 'PPR-C', 0.00, 146.00, 0, NULL),
('Reducer Tee', 1, 'MNR', '40mm x 32mm', 'PPR-C', 0.00, 238.00, 0, NULL),
('Reducer Tee', 1, 'MNR', '40mm x 25mm', 'PPR-C', 0.00, 236.00, 0, NULL),

-- Gate Valve
('Gate Valve', 1, 'MNR', '25mm', 'PPR-C', 0.00, 1215.00, 0, NULL),
('Gate Valve', 1, 'MNR', '32mm', 'PPR-C', 0.00, 1718.00, 0, NULL),
('Gate Valve', 1, 'MNR', '40mm', 'PPR-C', 0.00, 3685.00, 0, NULL),
('Gate Valve', 1, 'MNR', '40mm x 1', 'PPR-C', 0.00, 2713.00, 0, NULL),

-- Female Elbow
('Female Elbow', 1, 'MNR', '25mm x 1/2', 'PPR-C', 0.00, 295.00, 0, NULL),
('Female Elbow', 1, 'MNR', '32mm x 1/2', 'PPR-C', 0.00, 413.00, 0, NULL),
('Female Elbow', 1, 'MNR', '40mm x 1 1/4', 'PPR-C', 0.00, 1370.00, 0, NULL),

-- Reducer Socket
('Reducer Socket', 1, 'MNR', '32mm x 25mm', 'PPR-C', 0.00, 84.00, 0, NULL),
('Reducer Socket', 1, 'MNR', '40mm x 32mm', 'PPR-C', 0.00, 147.00, 0, NULL),
('Reducer Socket', 1, 'MNR', '40mm x 25mm', 'PPR-C', 0.00, 158.00, 0, NULL),

-- Under Ground
('Under Ground', 1, 'MNR', '25mm', 'PPR-C', 0.00, 1690.00, 0, NULL),
('Under Ground', 1, 'MNR', '32mm', 'PPR-C', 0.00, 2215.00, 0, NULL),
('Under Ground', 1, 'MNR', '40mm', 'PPR-C', 0.00, 3870.00, 0, NULL),

-- Stop Cock
('Stop Cock', 1, 'MNR', '25mm', 'PPR-C', 0.00, 1226.00, 0, NULL),
('Stop Cock', 1, 'MNR', '32mm', 'PPR-C', 0.00, 1810.00, 0, NULL),
('Stop Cock', 1, 'MNR', '40mm', 'PPR-C', 0.00, 3750.00, 0, NULL);

-- Insert Sample Customers
INSERT INTO customers (name, type) VALUES 
('Walk-in Customer', 'regular'),
('Ali Plumber', 'contractor');
