<h1 align="center">📦 Inventory & POS System (Laravel 12 + Vue 3)</h1>
<p align="center">A lightweight but powerful business management system built with modern Laravel + Vue.</p> <p align="center"> <a href="https://github.com/mazhar1419">GitHub</a> • <a href="https://www.linkedin.com/in/mazhar1419">LinkedIn</a> • <a href="https://www.reddit.com/user/doanldPutjonginTrump/">Reddit</a> </p>
👋 About the Developer

I'm Mazharul Islam, a full-stack developer & system builder with 5+ years of experience creating:

HRM, CRM, Inventory, POS

Custom business systems

Real-time applications

SaaS tools

Currently focusing on:

Advanced systems (OS, networking, cryptography)

Building high-quality SaaS starter kits

Developing modular open-source tools

Working with international clients

🚀 System Overview

This is a complete Inventory, Purchase, POS, Customer, Supplier & Reporting System
built using Laravel 12 + Vue 3.

Perfect for:

Small/medium shops

Retail businesses

Agencies selling systems

SaaS inventory startups

Includes:

✔ Authentication (manual, secure)

✔ Product, stock & category management

✔ POS (sales)

✔ Purchases

✔ Damages / Write-off

✔ Suppliers & customers

✔ Reporting module

🔐 Authentication (Manual)

Custom login (no packages)

Session-based auth

CSRF protection

Vue login UI

Default credentials:

Email	Password
admin@mail.com
	admin123
🛒 POS (Point of Sale)

Fast product search

Add to cart

Live cart calculation

Prevent overselling

Customer selection

Checkout with invoice popup

Stock auto deduction

📦 Products Module

CRUD

SKU, cost, selling price

Toggle stock tracking

Integrated with purchases & POS

🧾 Purchases Module

Select supplier

Add multiple items

Stock increases

Cost price auto-updates

Purchase list with pagination

👥 Customers & Suppliers

CRUD operations

Used across POS & purchases

❗ Damage / Write-Off

Deduct damaged stock

Add notes

Auto stock update

📊 Reporting Module
Sales by Date

Filter by date range

Group by day

CSV export

Stock Report

Summary of product quantities

Low stock filter

CSV export

Product-Based Reports

Sales by product

Purchases by product

Damage by product

📁 Project File Structure
.
├── app
│   ├── Http
│   │   ├── Controllers
│   │   │   ├── Api
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── SaleController.php
│   │   │   │   ├── PurchaseController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── SupplierController.php
│   │   │   │   ├── CustomerController.php
│   │   │   │   └── DamageController.php
│   │   │   └── Auth
│   │   │       └── LoginController.php
│   │   └── Middleware
│   ├── Models
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Sale.php
│   │   ├── SaleItem.php
│   │   ├── Purchase.php
│   │   ├── PurchaseItem.php
│   │   ├── Supplier.php
│   │   ├── Customer.php
│   │   └── Damage.php
│   └── Providers
│
├── database
│   ├── factories
│   │   └── UserFactory.php
│   ├── migrations
│   └── seeders
│
├── public
│   ├── index.php
│   └── assets
│       ├── images
│       ├── css
│       └── js
│
├── resources
│   ├── js
│   │   ├── app.js
│   │   ├── components
│   │   │   ├── InventoryApp.vue
│   │   │   ├── products
│   │   │   ├── pos
│   │   │   ├── purchases
│   │   │   ├── reports
│   │   │   ├── customers
│   │   │   ├── suppliers
│   │   │   └── damages
│   └── views
│       └── app.blade.php
│
├── routes
│   ├── api.php
│   ├── web.php
│   └── auth.php
│
├── .env.example
├── .gitignore
├── composer.json
├── package.json
├── vite.config.js
└── README.md

📦 File Details Table
No.	File / Folder	Description
1	app/	Laravel application core
2	Controllers/Api	Handles all API logic
3	Models/	Eloquent ORM models
4	database/migrations	DB structure definitions
5	resources/js/components/	Vue 3 components
6	resources/views/app.blade.php	Main blade entry
7	public/	Public assets + index.php
8	routes/api.php	API routes
9	routes/web.php	Authentication + app entry
10	.env.example	Environment template
11	composer.json	PHP dependencies
12	package.json	JS dependencies
13	README.md	Documentation
⚙️ Installation Guide
1. Clone Repo
git clone your-repo-url
cd project-folder

2. Backend Setup
composer install
cp .env.example .env
php artisan key:generate

3. Migrate
php artisan migrate

4. Create Admin User
php artisan tinker
User::factory()->create();

5. Frontend Setup
npm install
npm run dev

6. Start Server
php artisan serve

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

Manual authentication (no package)

Session-based login

Pure Eloquent ORM

CSRF protection

Purchases increase stock

POS and Damages decrease stock

Vue app auto-refreshes components

Clean minimal UI

💡 Future Enhancements

Role-based permissions

Barcode scanner support

Product images

Printable receipts

Multi-branch inventory

Offline-ready PWA

❤️ Credits

Built by Mazharul Islam with support from AI tools.
Designed for speed, simplicity, and real-world business needs.
