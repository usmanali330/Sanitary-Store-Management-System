🏢 Sanitary Store Management System

Sanitary Store Management System is a complete web-based solution for managing sanitary and plumbing stores. It allows the admin and staff to manage products, inventory, suppliers, customers, sales, and reports efficiently. The system also includes a POS (Point of Sale) module for generating invoices and billing.

This project is built using PHP, MySQL, JavaScript, and CSS, and runs perfectly on XAMPP. It is fully responsive, professional, and easy to use.

💻 Features
1️⃣ User Authentication

Admin login/logout

Staff login/logout

Role-based access control

Forgot password functionality

2️⃣ Dashboard

Overview of total products, total sales today, monthly revenue

Low stock alerts

Best-selling products

Recent transactions

3️⃣ Product Management

Add, edit, delete products

Manage product details: name, category, brand, size, type, price, quantity, warranty, image

Barcode support (optional)

Category management (Pipes, Taps, WC/Commode, Wash Basins, Showers, Water Tanks, Accessories)

4️⃣ Inventory / Stock Management

Track stock in and out

Automatic stock updates after sales

Low stock notifications

Maintain stock history

5️⃣ Sales / POS System

Add products to cart

Quantity and size selection

Apply discounts and taxes

Generate invoices (printable & downloadable PDF)

Auto-update inventory post-sale

6️⃣ Customer Management

Add/edit customer information

Track customer purchase history

Special records for contractors/plumbers (optional)

7️⃣ Supplier Management

Add/edit supplier details

Track purchase history, restocking, and supplier payments

8️⃣ Reports & Analytics

Daily, weekly, monthly sales reports

Product-wise and category-wise profit reports

Stock valuation reports

Export reports to Excel or PDF

9️⃣ Optional Features

Online product catalog for customers

Cart & checkout system for online orders

Multi-language support (English/Urdu)

Dark / light mode toggle

Share invoices via email or WhatsApp

🛠️ Technology Stack

Frontend: HTML, CSS (Bootstrap/Tailwind optional), JavaScript

Backend: PHP (OOP or procedural)

Database: MySQL

Server: XAMPP (Apache + MySQL)

📂 Folder Structure (Example)
SanitaryStoreManagement/
├── assets/          # CSS, JS, Images
├── includes/        # Header, Footer, Database connection
├── admin/           # Admin pages
├── staff/           # Staff pages
├── customer/        # Customer pages (optional)
├── index.php        # Login page
├── dashboard.php    # Main Dashboard
├── config.php       # Database connection
└── README.md

⚡ Installation & Setup

Install XAMPP and start Apache & MySQL.

Download or clone the repository:

git clone <repository_link>


Copy the project folder into C:\xampp\htdocs\.

Create a MySQL database (e.g., sanitary_store) using phpMyAdmin.

Import the provided SQL file (database.sql) into your database.

Update the database credentials in config.php.

Open your browser and visit:

http://localhost/SanitaryStoreManagement/


Login with default credentials (if provided) or create a new admin account.

👨‍💻 How to Use

Login as Admin or Staff.

Add product categories and products.

Manage inventory and suppliers.

Process sales using POS system and generate invoices.

View reports to analyze sales and stock.

Optionally, enable online catalog for customers.

🔐 Security

All database queries use prepared statements to prevent SQL injection.

Role-based access control ensures proper authorization.

📄 License

This project is open-source and free to use, modify, and distribute.
