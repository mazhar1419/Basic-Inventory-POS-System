<h1 align="center">📦 Inventory & POS System (Laravel 12 + Vue 3)</h1>
<p align="center">A lightweight but powerful business management system built with modern Laravel + Vue.</p> <p align="center"> <a href="github.com">GitHub</a> • <a href="www.linkedin.com">LinkedIn</a> • <a href="www.reddit.com">Reddit</a> </p>
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
```
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
```

---

---

## 🛠️ Installation & Setup Guide

### Prerequisites

Ensure you have the following installed on your system:

* **PHP** (version 8.x recommended)
* **Composer**
* **Node.js & npm**
* **Git**

### Step-by-Step Commands

Here are the commands to install, configure, and run the project:

| Step | Command Type | Command |
| :--- | :--- | :--- |
| **1. Clone & Setup** | Shell | `git clone your-repo-url` |
| | Shell | `cd project-folder` |
| **2. Backend Install** | Shell | `composer install` |
| | Shell | `cp .env.example .env` |
| | Shell | `php artisan key:generate` |
| **3. Database** | Shell | `php artisan migrate` |
| **4. Create Admin** | Shell | `php artisan tinker` |
| | Tinker | `User::factory()->create();` |
| | Tinker | `exit` |
| **5. Frontend Install** | Shell | `npm install` |
| | Shell | `npm run dev` |
| **6. Start Server** | Shell | `php artisan serve` |

---

## 🧪 API Endpoints (Gateway)

| Feature | Method | Endpoint | Description |
| :--- | :--- | :--- | :--- |
| **Products** | **GET** | `/api/products` | Retrieve a list of all products. |
| **Products** | **POST** | `/api/products` | Create a new product. |
| **Products** | **PUT** | `/api/products/{id}` | Update an existing product by ID. |
| **Products** | **DELETE** | `/api/products/{id}` | Delete a product by ID. |
| **Sales (POS)** | **POST** | `/api/sales` | Record a new sale/transaction. |
| **Sales (POS)** | **GET** | `/api/sales` | Retrieve a list of all sales records. |
| **Purchases** | **POST** | `/api/purchases` | Record a new purchase (stock inflow). |
| **Purchases** | **GET** | `/api/purchases` | Retrieve a list of all purchase records. |
| **Reports** | **GET** | `/api/reports/sales-by-date` | Get sales report data grouped by date. |
| **Reports** | **GET** | `/api/reports/sales-by-date-export` | Export sales report data (e.g., as CSV/Excel). |
| **Reports** | **GET** | `/api/reports/stock` | Get current stock levels and summary. |
| **Reports** | **GET** | `/api/reports/stock-export` | Export stock report data. |

---

## 🧱 Technical Notes

* **Manual authentication** (no package)
* Session-based login with **CSRF protection**
* Pure **Eloquent ORM**
* Purchases **increase stock**
* POS and Damages **decrease stock**
* Vue app auto-refreshes components
* Clean minimal UI

---

## 💡 Future Enhancements

* Role-based permissions
* Barcode scanner support
* Product images
* Printable receipts
* Multi-branch inventory
* Offline-ready PWA

---

## ❤️ Credits

Built by **Mazharul Islam** with support from AI tools. Designed for speed, simplicity, and real-world business needs.
