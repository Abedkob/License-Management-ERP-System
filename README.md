
# 🪪 License Management ERP System

A **PHP-based ERP web application** for managing software licenses (activation codes), client payments, two-factor authenticated user access, and full audit logging — built from scratch with a custom MVC architecture, PDO, and Composer-managed dependencies.

Designed for organizations that need to **issue, track, and bill** digital licenses while keeping a complete trail of every administrative action.

---

## 📌 Overview

Managing software licenses at scale is more than "generate a key." You need to:
- Issue and revoke activation codes per client.
- Track payments and billing tied to each license.
- Restrict who can see/edit/delete what (role / route-based permissions).
- Keep a full audit log of every user action for compliance.
- Lock down accounts with **two-factor authentication**.

This project bundles all of that into a single self-hosted PHP application with a server-rendered dashboard.

---

## ✨ Key Features

### 🔐 Authentication & Security
- **Username + password login** with **bcrypt** password hashing (`password_hash` / `PASSWORD_BCRYPT`).
- **Two-Factor Authentication (TOTP)** powered by [PHPGangsta/GoogleAuthenticator](https://github.com/PHPGangsta/GoogleAuthenticator) — compatible with Google Authenticator, Authy, 1Password, etc.
- **Session-based access control** with `requireAuth()` and `requirePermission($route)` guards on every protected route.
- **Per-route permission system** — fine-grained control (e.g. a user can view payments but not delete them).
- Helper script `hash_passwords.php` that migrates legacy plain-text passwords in the `users` table to bcrypt hashes idempotently.

### 🔑 Activation Code (License) Management
- Create, edit, delete, and list activation codes from the dashboard.
- **Bulk update** and **bulk delete** for managing many codes at once.
- **DataTable** endpoint (`/activation-codes/datatable`) for fast server-side paginated listings.
- **Export** activation codes (`/export`) for reporting / handover.

### 💳 Payments Manager
- Create, edit, and delete payments.
- Link a payment to a specific license (`/payments-manager/create-payment-for-license`).
- Client search and validation endpoints (`search-clients`, `validate-client`, `available-clients`) for live AJAX lookups.
- DataTable-powered listing.

### 📊 Dashboard & Statistics
- Aggregated metrics page (`/dashboard`) showing license and payment activity at a glance.
- Single dashboard view (`views/dashboard.php`, ~40 KB) renders the operational overview.

### 📜 Audit Logging & User Actions
- **User-action tracking**: every navigation and action is recorded via `/user-actions/track-page-view` and `/user-actions/track-action`.
- **Activity log viewer** at `/user-actions/activity-logs`.
- **Admin logs module** (`/logs`, `/logs/datatable`) with filterable actions and descriptions.
- Batch endpoint (`/user-actions/store-batch`) so the frontend can flush queued events efficiently.

### 🏗️ Architecture
- Custom **MVC structure** with PSR-4 autoloading under the `App\` namespace.
- Single front controller (`public/index.php`) handling all routing.
- Apache `.htaccess` rewrites everything to `public/index.php`.
- Models use **PDO with prepared statements** (no string-concatenated SQL).

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 7.4+ (PHP 8.x recommended) |
| Database | MySQL / MariaDB (via PDO) |
| Web server | Apache with `mod_rewrite` (XAMPP / LAMP / WAMP) |
| Dependency manager | Composer (PSR-4 autoloading) |
| 2FA library | `phpgangsta/googleauthenticator` |
| Frontend | Server-rendered PHP views (HTML, CSS, JS), DataTables, AJAX |
| Architecture | Custom MVC, single front-controller routing |

---

## 📂 Project Structure

```

License-Management-ERP-System/

├── api/

│   ├── db.php                       # Lightweight PDO bootstrap for AJAX endpoints

│   └── get_license.php              # Public-ish license lookup endpoint

├── config/

│   └── database.php                 # DB credentials (host, dbname, user, pass)

├── public/

│   ├── .htaccess                    # Apache rewrite -> index.php (front controller)

│   ├── config.php                   # Public-side config / base URL helper

│   └── index.php                    # Front controller: routing, auth & permission guards

├── src/

│   ├── Controllers/

│   │   ├── AuthController.php             # Login, logout, 2FA setup & verify

│   │   ├── DashboardController.php        # Dashboard metrics & rendering

│   │   ├── ActivationCodeController.php   # CRUD + bulk + export for licenses

│   │   ├── PaymentsController.php         # CRUD + client lookup + license-payment linking

│   │   ├── LogsController.php             # Admin logs viewer + DataTable

│   │   └── UserActionController.php       # Permission checks, action tracking, batch

│   └── Models/

│       ├── User.php                       # Users + auth

│       ├── ActivationCode.php             # License records

│       ├── Payment.php                    # Payments

│       └── UserAction.php                 # Audit-log persistence

├── views/

│   ├── auth/

│   │   ├── login.php                  # Login form

│   │   └── 2fa.php                    # TOTP code entry

│   ├── dashboard.php                  # Main dashboard

│   ├── activation_codes/              # License CRUD views (index/create/edit)

│   ├── payments_manager/              # Payment CRUD views

│   ├── user-actions/                  # Activity log views

│   ├── logs/                          # System log views

│   ├── layouts/                       # Header/footer partials (login + main)

│   └── errors/                        # Custom error pages

├── vendor/                            # Composer dependencies (gitignored ideally)

├── hash_passwords.php                 # One-off script to bcrypt legacy passwords

├── composer.json                      # PSR-4 autoload + dependencies

├── composer.lock

└── README.md

```

---

## 🚀 Getting Started

### Prerequisites

- **PHP 7.4+** (PHP 8.1+ recommended) with extensions: `pdo_mysql`, `mbstring`, `openssl`, `session`, `json`.
- **MySQL 5.7+** or **MariaDB 10.3+**.
- **Apache** with `mod_rewrite` enabled (XAMPP / LAMP / WAMP all work).
- **Composer** — https://getcomposer.org
- A TOTP authenticator app (Google Authenticator, Authy, 1Password, etc.).

### 1. Clone the repository

```bash

git clone https://github.com/Abedkob/License-Management-ERP-System.git

cd License-Management-ERP-System

```

### 2. Install dependencies

```bash

composer install

```

This installs `phpgangsta/googleauthenticator` and sets up PSR-4 autoloading for the `App\` namespace mapped to `src/`.

### 3. Create the database

Create a MySQL database (default name: `auth_system`) and import your schema. The application expects (at minimum) these tables:

- `users` — user accounts, hashed passwords, 2FA secrets.
- `activation_codes` — license records.
- `payments` — payment records (with FK to clients/licenses).
- `user_actions` — audit log of user activity.
- `permissions` / route mapping for the per-route permission system.

> A migration / seed SQL file is recommended — see the **Roadmap** below.

### 4. Configure the database connection

Edit **`config/database.php`**:

```php
<?php

return [

    'host'     => 'localhost',

    'dbname'   => 'auth_system',

    'username' => 'root',

    'password' => '',

];

```

### 5. Hash existing passwords (only if you imported plain-text passwords)

```bash

php hash_passwords.php

```

This idempotently bcrypts any plain-text passwords already present in `users`.

### 6. Point Apache at `public/`

The document root **must** be the `public/` directory. Two common setups:

**XAMPP / WAMP / MAMP (quickest):** drop the project into `htdocs/` and visit:

```

http://localhost/License-Management-ERP-System/public/

```

**Apache vhost (recommended):**

```apache
<VirtualHost *:80>

    ServerName license-erp.local

    DocumentRoot "/path/to/License-Management-ERP-System/public"

    <Directory "/path/to/License-Management-ERP-System/public">

        AllowOverride All

        Require all granted

    </Directory>
</VirtualHost>
```

Add `127.0.0.1 license-erp.local` to your hosts file and visit `http://license-erp.local`.

### 7. Log in & set up 2FA

Navigate to `/login`, sign in with a seeded user, then complete the 2FA flow at `/2fa` by scanning the QR code with your authenticator app.

---

## 🗺️ Application Routes

| Method | Route | Purpose |
|---|---|---|
| GET | `/` · `/login` | Login page |
| POST | `/login/submit` | Submit credentials |
| GET | `/2fa` | Show 2FA challenge |
| POST | `/2fa/verify` | Verify TOTP code |
| GET | `/logout` | End session |
| GET | `/dashboard` | Main metrics dashboard |
| GET | `/activation-codes` | List licenses |
| GET/POST | `/activation-codes/create` | Create license |
| GET/POST | `/activation-codes/edit?id=` | Edit license |
| GET | `/activation-codes/delete?id=` | Delete license |
| GET | `/activation-codes/datatable` | Server-side DataTable JSON |
| POST | `/activation-codes/bulk-update` | Bulk-update licenses |
| POST | `/activation-codes/bulk-delete` | Bulk-delete licenses |
| GET | `/export` | Export licenses |
| GET | `/payments-manager` | List payments |
| GET/POST | `/payments-manager/create` | Create payment |
| GET | `/payments-manager/edit?id=` | Edit payment |
| POST | `/payments-manager/update?id=` | Update payment |
| POST | `/payments-manager/delete` | Delete payment |
| GET | `/payments-manager/datatable` | DataTable JSON |
| GET | `/payments-manager/get-clients` | Client list |
| GET | `/payments-manager/search-clients` | Search clients (AJAX) |
| GET | `/payments-manager/validate-client` | Validate client (AJAX) |
| GET | `/payments-manager/available-clients` | Available clients |
| GET | `/payments-manager/get-payment` | Fetch single payment |
| GET | `/payments-manager/create-payment-for-license-form` | Form for license-tied payment |
| POST | `/payments-manager/create-payment-for-license` | Submit license-tied payment |
| GET | `/logs` | Admin logs |
| GET | `/logs/datatable` | Logs DataTable JSON |
| GET | `/logs/get-actions` | Distinct actions filter |
| GET | `/logs/get-description` | Description lookup |
| GET | `/user-actions/activity-logs` | Personal activity log |
| GET | `/user-actions/create-form` | Form to create user action / permission |
| POST | `/user-actions/store-batch` | Batch persist actions |
| POST | `/user-actions/store` | Persist single action |
| GET | `/user-actions/track-page-view` | Track navigation |
| GET | `/user-actions/track-action` | Track button/click |
| POST | `/user-actions/delete` | Delete action |
| GET | `/user-actions/permissions` | Get current user permissions |

All non-public routes are protected by `requireAuth()` (session + 2FA verified) and most are further gated by `requirePermission($route)`.

---

## 👤 Author

**Abed Al-Nabi Koubeissy**
Computer Science student — Phoenicia University, Lebanon

- 🌐 Portfolio: https://abedkob-portfolio-eight.vercel.app
- 💻 GitHub: https://github.com/Abedkob
- 📧 Email: abedkoubiessy@gmail.com

---

## ⚠️ Known Limitations

- **Database credentials are committed in `config/database.php`** — should be moved to environment variables or a `.env` file (e.g. via `vlucas/phpdotenv`).
- **No migrations or seed file** — the schema must be created manually.
- **`vendor/` is committed** — should be in `.gitignore` and installed via `composer install` instead.
- **No CSRF tokens** on POST forms — should be added before any production deployment.
- **No automated tests** yet.
- **`hash_passwords.php` lives at the web root**, which is risky on a misconfigured server. It should be moved out of `public/`.

---

## 🗺️ Roadmap

- [ ] Move DB credentials and secrets into `.env` (via `phpdotenv`).
- [ ] Provide a `database/schema.sql` plus a seed script.
- [ ] Add `.gitignore` entries for `vendor/`, `*.log`, and IDE files.
- [ ] Add CSRF tokens on every POST form.
- [ ] Add PHPUnit tests for controllers and models.
- [ ] Dockerize (PHP-FPM + Apache + MySQL via `docker-compose`).
- [ ] Email notifications on license creation / expiration.
- [ ] Multi-language UI (English / Arabic).

---

## 📜 License

Released under the **MIT License** — free to use, modify, and adapt for educational and commercial purposes.
