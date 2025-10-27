# Hostel Management System Prototype

This repository hosts the early stages of a hostel supply management system built with PHP. The current focus is **Phase 1**, which establishes authentication, role management, and a starter dashboard layout for future delivery and approval workflows.

## Features (Phase 1)

- Configurable database connection (SQLite by default with optional MySQL and PostgreSQL support) powering the user store with the following roles:
- SQLite-backed user store with the following roles:
  - `delivery_person`
  - `sanitary_seller`
  - `ac_servicer`
  - `checker`
  - `admin`
- Secure login using hashed passwords and PHP sessions plus self-service sign-up for non-admin roles.
- Role-aware dashboard with contextual guidance for the next development phases.
- Admin dashboard that surfaces hostel expenses and allows pricing each service category.
  - `checker`
  - `admin`
- Secure login using hashed passwords and PHP sessions.
- Role-aware dashboard with contextual guidance for the next development phases.
- Minimal styling for a clean baseline user interface.

## Getting Started

### Requirements

- PHP 8.1 or later with the SQLite extension enabled.

### Installation

1. Choose where to store your data by copying the example environment file and editing it with your preferred database settings:

   ```bash
   cp .env.example .env
   ```

   By default the application uses SQLite and will create the database file defined by `DB_DATABASE`. You can switch to MySQL or PostgreSQL by updating `DB_DRIVER` and the corresponding host, port, username, and password variables in `.env`.

2. Install dependencies and initialize the database:
1. Install dependencies and initialize the database:

   ```bash
   php scripts/setup.php
   ```

   The script creates the necessary schema and seeds user accounts, service catalogs, and sample expense data. When using SQLite the file is written to the path configured in `.env` (defaulting to `storage/app.sqlite`). The database file is intentionally excluded from version control, so rerun the setup script whenever you need a fresh copy.
   The script creates the SQLite database (stored at `storage/app.sqlite`) and seeds user accounts, service catalogs, and sample expense data. The database file is intentionally excluded from version control, so rerun the setup script whenever you need a fresh copy.

| Role                | Email                     | Password       |
| ------------------- | ------------------------- | -------------- |
| Admin               | `admin@hostel.local`      | `admin123`     |
| Checker             | `checker@hostel.local`    | `checker123`   |
| Delivery Person     | `delivery@hostel.local`   | `delivery123`  |
| Sanitary Seller     | `sanitary@hostel.local`   | `sanitary123`  |
| AC Servicer         | `acservice@hostel.local`  | `acservice123` |

   After logging in as an administrator you can open `/admin_dashboard.php` from the top navigation to:

   - View the aggregate expense total across all recorded services.
   - Review the latest expense entries by service.
   - Adjust the standard price for each service category (eggs, chicken, vegetables, sanitary supplies, AC servicing, etc.).

3. Start the built-in PHP development server from the `public/` directory:
   The script creates the SQLite database (stored at `storage/app.sqlite`) and seeds three example accounts:

   | Role             | Email                   | Password     |
   | ---------------- | ----------------------- | ------------ |
   | Admin            | `admin@hostel.local`    | `admin123`   |
   | Checker          | `checker@hostel.local`  | `checker123` |
   | Delivery Person  | `delivery@hostel.local` | `delivery123`|

2. Start the built-in PHP development server from the `public/` directory:

   ```bash
   php -S localhost:8000 -t public
   ```

4. Visit [http://localhost:8000](http://localhost:8000) and either log in with one of the seeded accounts or open `/signup.php` to create a delivery, sanitary seller, AC servicer, or checker account for testing.
3. Visit [http://localhost:8000](http://localhost:8000) and either log in with one of the seeded accounts or open `/signup.php` to create a delivery, sanitary seller, AC servicer, or checker account for testing.
3. Visit [http://localhost:8000](http://localhost:8000) and log in with one of the seeded accounts.

## Roadmap

Future phases will add delivery logging, approval workflows, transaction reports, printing, and extended hostel management capabilities.
