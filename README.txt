Sanitary Store Management System
================================

Introduction
------------
This is a complete web-based Sanitary Store Management System built with PHP, MySQL, JavaScript, and CSS. It includes features for managing products, inventory, suppliers, customers, and sales (POS).

Installation Instructions (XAMPP)
---------------------------------

1. **Setup Database**:
    - Open XAMPP Control Panel and start **Apache** and **MySQL**.
    - Go to phpMyAdmin (http://localhost/phpmyadmin).
    - Create a new database named `sanitary_store_db`.
    - Import the file `database/schema.sql` into this database.
    - (Optional) If you want the stock logs table and cost price features immediately without manual updates, run these SQL commands in the SQL tab of phpMyAdmin:
        ```sql
        CREATE TABLE IF NOT EXISTS stock_logs (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT, quantity INT, type ENUM('in', 'out'), reason VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (product_id) REFERENCES products(id));
        ALTER TABLE products ADD COLUMN cost_price DECIMAL(10, 2) DEFAULT 0 AFTER type;
        ```
    (Note: The AI agent has likely already run these commands for you if you were monitoring the process).

2. **Configure Connection**:
    - The database connection is configured in `includes/config.php`.
    - Default settings: Host: localhost, User: root, Pass: (empty), DB: sanitary_store_db.
    - If you have a password for root, update this file.

3. **Run the Application**:
    - Open your browser and go to: `http://localhost/intigravity/`
    - You will be redirected to the Login page.

4. **Login Credentials**:
    - **Username**: admin
    - **Password**: admin123

Features Overview
-----------------
- **Dashboard**: Overview of sales, low stock, and top metrics.
- **POS (Point of Sale)**: Fast billing system with barcode search support.
- **Products**: Add, edit, delete products with images.
- **Inventory**: Track stock levels, add stock (Purchase), and view logs.
- **Sales History**: View past records and print invoices.
- **Reports**: View stock valuation and profit (if cost price is used).
- **Users**: Admin can manage staff accounts.

Troubleshooting
---------------
- **Images not uploading**: Ensure the `checks\uploads` folder exists and has write permissions.
- **Database Error**: Check `includes/config.php` credentials.
