<h1 align="center">📦 Inventory & POS System (Laravel 12 + Vue 3)</h1>
<p align="center">A lightweight but powerful business management system built with modern Laravel + Vue.</p> <p align="center"> <a href="https://github.com/mazhar1419">GitHub</a> • <a href="https://www.linkedin.com/in/mazhar1419">LinkedIn</a> • <a href="https://www.reddit.com/user/doanldPutjonginTrump/">Reddit</a> </p>
🚀 Overview

This is a modern Inventory, Purchase, POS, Customer, Supplier & Reporting System crafted using Laravel 12 + Vue 3.
Built clean, modular, scalable — suitable for small/medium business operations.

Includes:

✔ Authentication

✔ Product & Stock Management

✔ Purchases

✔ Sales / POS

✔ Damages

✔ Suppliers & Customers

✔ Reports

The system is designed for real businesses, focusing on efficiency and simplicity.

🔐 Authentication (Manual — No Packages)

Session-based login

Custom User model, migration & factory

Secure: CSRF protection, login throttling

Vue-based login page

Default Credentials:

Email: admin@mail.com

Password: admin123

🛒 POS (Point of Sale)

Product search

Add-to-cart with real-time calculation

Prevent overselling

Select customer

Checkout + invoice popup

Auto stock deduction

📦 Products Module

Full CRUD

SKU, cost price, selling price

Toggle stock tracking

Integrated with POS & Purchases

🧾 Purchases Module

Select supplier (required)

Add multiple purchase lines

Stock auto-increments

Cost price auto-updates

Purchase listing with pagination

Supplier relation included

👥 Customers & Suppliers

Full CRUD

Integrated across POS, Purchases & Reports

❗ Damage / Write-Off

Deduct damaged items

Record quantity + note

Stock auto-updated

📊 Reports Module
1. Sales by Date

Filter by date range

Group by day

CSV export (date, sales_count, total_amount, total_paid)

2. Stock Report

Summary of all product stock

Low-stock filter

CSV export

3. Product-Based Reports (recommended extension)

Sales by product

Purchases by product

Damages by product

Show all if product not selected

📁 Project Structure
resources/
  js/
    components/
      InventoryApp.vue
      products/
      purchases/
      pos/
      reports/
      customers/
      suppliers/
      damages/
  views/
    app.blade.php

app/
  Http/
    Controllers/
      Api/
        ProductController.php
        SaleController.php
        PurchaseController.php
        ReportController.php
        SupplierController.php
        CustomerController.php
        DamageController.php
      Auth/
        LoginController.php

  Models/
    User.php
    Product.php
    Sale.php
    SaleItem.php
    Purchase.php
    PurchaseItem.php
    Supplier.php
    Customer.php
    Damage.php

database/
  migrations/
  factories/

⚙️ Installation Guide
1. Clone Repository
git clone your-repo-url
cd project-folder

2. Backend Setup
composer install
cp .env.example .env
php artisan key:generate


Configure your database in .env.

3. Migrate
php artisan migrate

4. Seed Default Admin
php artisan tinker
User::factory()->create();

5. Frontend Setup
npm install
npm run dev

6. Start Server
php artisan serve

🔑 Default Login
Email	Password
admin@mail.com
	admin123
🧪 API Endpoints
Products
GET    /api/products
POST   /api/products
PUT    /api/products/{id}
DELETE /api/products/{id}

Sales (POS)
POST /api/sales
GET  /api/sales

Purchases
POST /api/purchases
GET  /api/purchases

Reports
GET /api/reports/sales-by-date
GET /api/reports/sales-by-date-export
GET /api/reports/stock
GET /api/reports/stock-export

🧱 Technical Notes

Pure Eloquent ORM

Session-based authentication

No third-party auth packages

CSRF protection everywhere

Purchases increase stock

Sales decrease stock

Damages decrease stock

Vue components auto-refresh

Minimal clean UI

💡 Future Improvements

Role-based access

Barcode scanner

Product images

Printable POS receipts

Multi-branch inventory

PWA (offline support)

❤️ Credits & Author

Developed by Mazharul Islam with AI assistance.
Clean architecture, scalable modules, and real-world usability.
