# DepEd AMS (Asset Management System) 📦

![Laravel](https://img.shields.io/badge/Laravel-11.x%20%7C%2012.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)

A comprehensive, role-based Asset and Supply Management System designed for the Department of Education (DepEd). This system streamlines inventory tracking, barcode generation, stock transactions, and Requisition and Issue Slips (RIS) management across different divisions and offices.

---

## ✨ Core Features

### 🔐 Role-Based Access Control (RBAC)
* **Admin (System Owner):** Full access to user management, system settings, report generation, and overarching transaction controls (including deleting erroneous logs).
* **Personnel / Staff (Inventory Manager):** Access to manage assets, supplies, process barcode scans, and review RIS requests.
* **Frontuser (Requestor):** End-users restricted to their specific division/office. Can request supplies via RIS, view their own request history, and track status.

### 📦 Inventory & Barcode Management
* **Auto-Generated Barcodes:** Automatically generates unique tracking codes (e.g., `SUP-YYYYMMDD-XXXX`) upon item creation.
* **Barcode Master List:** A dedicated registry of all generated barcodes rendered dynamically using `JsBarcode`. Includes individual print capabilities.
* **Scanner Integration:** AJAX-powered endpoint to process live barcode scans for Stock IN and Stock OUT operations.
* **Dynamic Media Handling:** Integrated image uploads for user profiles and inventory items with lightbox previews.

### 🔍 Advanced Global Search
* **Live AJAX Search:** A debounced, global search bar located in the header.
* **Cross-Table Queries:** Instantly searches across Assets, Supplies, Transactions, and RIS Requests.
* **Smart Routing:** Clicking a search result dynamically calculates the exact pagination page (`?page=X`) where the item resides, ensuring users don't get lost in large datasets.

### 📊 Tracking & Reports
* **Transaction History:** Detailed logs of every movement (Added, Stock IN, Stock OUT) including timestamps, personnel tracking, and RIS references.
* **Low Stock Alerts:** Dashboard warnings when consumable supplies drop below critical thresholds.
* **Advanced Pagination:** Custom scrollable pagination with sticky arrows and state-preserving filters.

---

## 🛠️ Tech Stack

* **Backend:** PHP 8.x, Laravel Framework
* **Frontend:** HTML5, CSS3, JavaScript (Vanilla + jQuery)
* **UI Framework:** Bootstrap 5.3
* **Icons:** FontAwesome 6
* **Database:** MySQL
* **Libraries:** `JsBarcode` (Barcode Rendering)

---

## 🚀 Installation & Setup Guide

Follow these steps to set up the project locally on your machine.

### 1. Prerequisites
Ensure you have the following installed:
* [PHP >= 8.2](https://www.php.net/)
* [Composer](https://getcomposer.org/)
* [Node.js & NPM](https://nodejs.org/)
* [MySQL](https://www.mysql.com/) (or XAMPP/Laragon)

### 2. Clone the Repository
```bash
git clone [https://github.com/yourusername/your-repo-name.git](https://github.com/yourusername/your-repo-name.git)
cd your-repo-name
```


### 3. Install Dependencies
Install the required PHP and Node packages:
``` bash
composer install
npm install
npm run build
```

### 4. Environment Configuration
Copy the example environment file and generate a new application key:
``` bash
cp .env.example .env
php artisan key:generate
```


### Open the .env file and configure your database settings:
``` bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ams_inventory
DB_USERNAME=root
DB_PASSWORD=your_password
```


### 5. Database Migration & Storage Link
Run the migrations to create the necessary tables. (If you have seeders for default users/data, append --seed)
``` bash
php artisan migrate
```

###  CRITICAL STEP: Create the symbolic link to ensure uploaded images (User avatars, Supply photos, Asset photos) are publicly accessible:
``` bash
php artisan storage:link
```


### 6. Run the Application
Start the Laravel development server:
``` bash
php artisan serve
```


### Key Directory Structure Highlights
app/Http/Controllers/ - Contains core logic (e.g., GlobalSearchController.php, BarcodeController.php).

app/Http/Controllers/Admin/ - Admin-exclusive logic with elevated privileges.

resources/views/layouts/ - Contains the master Blade layouts (header, sidebar, admin_sidebar).

resources/views/admin/ - Admin interface Blade templates.

public/uploads/ - Destination for direct image uploads (like User Profiles).

🛡️ Security Notes
Idle Timeout: The system includes a JavaScript-based idle timer that automatically redirects inactive users to an idle-screen to protect terminal sessions.

Strict Role Routing: Routes are protected by Laravel middleware (auth and custom role checks) ensuring Frontusers cannot access Admin or Staff endpoints.

📝 License
This project is proprietary and built specifically for internal organizational use.

Developed with ❤️ for efficient inventory management.
