# Aspirian.pk Online Test System

A complete PHP + MySQL Online Test System with a Student Interface and Admin Panel.

---

## 📁 File Structure

```
Online Test System/
├── index.php                  ← Student login
├── register.php               ← Student registration
├── dashboard.php              ← Student dashboard (topic selection)
├── test.php                   ← Test interface with MCQs
├── result.php                 ← Result display after submission
├── logout.php                 ← Student logout
├── config.php                 ← Database connection & constants
├── functions.php              ← Reusable helper functions
├── update_admin_password.php  ← One-time admin password setup utility
├── database.sql               ← Full database schema + sample MCQs
├── .htaccess                  ← Apache security rules
│
├── admin/
│   ├── login.php              ← Admin login
│   ├── dashboard.php          ← Admin dashboard & stats
│   ├── add_mcq.php            ← Add a new MCQ
│   ├── edit_mcq.php           ← Edit an existing MCQ
│   ├── delete_mcq.php         ← Delete an MCQ
│   ├── mcqs.php               ← Browse & manage all MCQs
│   ├── upload_csv.php         ← Bulk-import MCQs via CSV
│   ├── results.php            ← View all student results
│   ├── students.php           ← View & manage students
│   ├── logout.php             ← Admin logout
│   ├── _header.php            ← Shared admin layout header
│   └── _footer.php            ← Shared admin layout footer
│
└── assets/
    ├── css/
    │   └── style.css          ← Main stylesheet (responsive)
    └── js/
        └── timer.js           ← Countdown timer for tests
```

---

## 🚀 Installation Guide

### Requirements
- PHP 8.0 or higher
- MySQL 5.7+ / MariaDB 10.3+
- Apache (XAMPP / WAMP / LAMP) or any PHP-capable web server

### Steps

#### 1. Place Files
Copy the entire `Online Test System` folder into your web server root:
- XAMPP: `C:\xampp\htdocs\`
- WAMP: `C:\wamp64\www\`
- Linux/cPanel: `public_html/`

#### 2. Create the Database
1. Open **phpMyAdmin** → click **New**
2. Create a database named `aspirian_test_system`
3. Select it → click **Import** → choose `database.sql` → click **Go**

#### 3. Configure Database Credentials
Open `config.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'aspirian_test_system');
```

#### 4. Set Admin Password
Open your browser and visit:
```
http://localhost/Online Test System/update_admin_password.php
```
This sets the admin password to `admin123`.  
**Delete `update_admin_password.php` after running it.**

#### 5. Update Site URL (optional)
In `config.php`, update `SITE_URL` to match your domain:
```php
define('SITE_URL', 'http://localhost/Online Test System');
// For live server:
// define('SITE_URL', 'https://aspirian.pk');
```

---

## 🔑 Default Credentials

| Role  | Email                 | Password |
|-------|-----------------------|----------|
| Admin | admin@aspirian.pk     | admin123 |

---

## 📋 Included Test Topics & Sample Data

Each topic comes with **5 sample MCQs** in `database.sql`:
- MS Word
- MS Excel
- PowerPoint
- Internet
- Urdu InPage
- Introduction to Computer

---

## 📤 Bulk MCQ Upload (CSV)

CSV format (header row required):
```
topic,question,option_a,option_b,option_c,option_d,correct_option
MS Word,Shortcut to bold text?,Ctrl+I,Ctrl+B,Ctrl+U,Ctrl+S,b
```

Upload via: **Admin Panel → Upload CSV**

---

## 🔒 Security Features

- bcrypt password hashing (`password_hash` / `password_verify`)
- CSRF token protection on all forms
- Prepared statements for all database queries (no SQL injection)
- Session-based authentication with role separation
- `.htaccess` blocks direct access to sensitive files
- XSS prevention via `htmlspecialchars()` on all output

---

## ⏱️ Test Timer

- Each test has a **30-minute countdown** (configurable in `config.php`)
- Timer auto-submits the form when it reaches zero
- Browser warns if user tries to leave mid-test

---

## ⚙️ Configuration Reference (`config.php`)

| Constant        | Default                        | Description                    |
|----------------|-------------------------------|--------------------------------|
| `DB_HOST`      | `localhost`                   | Database host                  |
| `DB_USER`      | `root`                        | Database username               |
| `DB_PASS`      | (empty)                       | Database password               |
| `DB_NAME`      | `aspirian_test_system`        | Database name                  |
| `SITE_NAME`    | `Aspirian.pk`                 | Site display name               |
| `SITE_URL`     | `http://localhost/...`        | Base URL (no trailing slash)    |
| `TEST_TIME`    | `1800`                        | Test duration in seconds        |
| `MCQS_PER_TEST`| `10`                          | MCQs shown per test session     |

---

## 📦 Deploying to Live Server (cPanel)

1. Upload all files to `public_html/` (or a subdirectory)
2. Create a MySQL database via cPanel → **MySQL Databases**
3. Import `database.sql`
4. Update `config.php` credentials
5. Update `SITE_URL` in `config.php`
6. Visit `update_admin_password.php` and then delete it
7. Done ✅

---

© 2026 Aspirian.pk. All rights reserved.
