Inventory & POS System (Laravel 12 + Vue 3)

A lightweight but powerful Inventory, Purchase, POS, Customer, Supplier & Reporting System built using Laravel 12 + Vue 3.
Includes full authentication, products, purchases, sales, damages, suppliers, customers, and reporting modules.

🚀 Features
Authentication (Manual / No Packages)

Session-based login

Logout

CSRF protection

Custom User migration, model & factory

Vue login page

Email: admin@mail.com
, Password: admin123

POS System

Product search

Add to cart

Real-time cart calculation

Restricts overselling

Customer selection

Checkout with invoice popup

Stock auto-deduct

Products Module

CRUD

SKU, cost price, sell price

Stock tracking toggle

Integrated with POS & Purchases

Purchases Module

Select supplier (required)

Add multiple purchase lines

Quantity increases stock

Unit cost updates product cost price

Purchase list with pagination

Supplier relation included

Customers & Suppliers

Full CRUD

Used in POS and Purchases

Displayed in reports

Damage / Write-Off

Deduct damaged items

Record note and qty

Stock auto-updated

List view and form

Reports Module
1. Sales by Date

Filter by date range

Group by day

CSV export

Fields:

date

sales_count

total_amount

total_paid

2. Stock Report

Product stock summary

Low-stock filter

CSV export

3. Product-Based Reports

(Recommended extension)

Sales by product

Purchases by product

Damages by product

If product not selected → show all

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

🔐 Authentication Setup
Routes
POST /login
POST /logout

LoginController

Auth::attempt()

Session regeneration

Logout

redirect()->intended('/')

User Factory

Creates:

email: admin@mail.com
password: admin123


Seed via:

php artisan tinker
User::factory()->create();

⚙️ Installation
1. Clone
git clone your-repo-url
cd project-folder

2. Backend Install
composer install
cp .env.example .env
php artisan key:generate


Configure DB credentials.

3. Migrate
php artisan migrate

4. Seed admin user
php artisan tinker
User::factory()->create();

5. Frontend Install
npm install
npm run dev

6. Run server
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

No third-party auth packages used

Pure Eloquent ORM

Session-based authentication

CSRF required for all POST requests

Vue logout uses meta CSRF token

Purchases increase stock

Sales decrease stock

Damages decrease stock

Stock displayed everywhere

Vue components auto-refresh after save

Clean UI using basic CSS only

💡 Future Improvements

Role-based authorization

Barcode scanner input

Product images

Printable POS receipts

Multi-branch inventory

PWA (offline mode)

❤️ Credits

Developed by Mazharul Islam with the support of AI assistance.
Modular architecture, clean codebase, scalable design.
