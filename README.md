# Hostel Management System Prototype

This repository hosts the early stages of a hostel supply management system built with PHP. The current focus is **Phase 1**, which establishes authentication, role management, and a starter dashboard layout for future delivery and approval workflows.

## Features (Phase 1)

- SQLite-backed user store with the following roles:
  - `delivery_person`
  - `checker`
  - `admin`
- Secure login using hashed passwords and PHP sessions.
- Role-aware dashboard with contextual guidance for the next development phases.
- Minimal styling for a clean baseline user interface.
- Tuition, exam, and activities fee payments through the SSLCommerz gateway.

## Getting Started

### Requirements

- PHP 8.1 or later with the SQLite extension enabled.

### Installation

1. Install dependencies and initialize the database:

   ```bash
   php scripts/setup.php
   ```

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

3. Visit [http://localhost:8000](http://localhost:8000) and log in with one of the seeded accounts.

### Configuring SSLCommerz

The payment page relies on SSLCommerz credentials. Set the following environment variables before opening `/payment.php`:

- `SSLCOMMERZ_STORE_ID` – your store ID.
- `SSLCOMMERZ_STORE_PASSWORD` (or `SSLCOMMERZ_STORE_PASSWD`) – the store password.
- `SSLCOMMERZ_SANDBOX` – optional flag (`true`/`false`) to toggle between the sandbox (`true`, default) and live gateways.
- `SSLCOMMERZ_CURRENCY` – optional ISO currency code, defaults to `BDT`.

For quick testing you can use sandbox credentials from the SSLCommerz merchant panel. When the credentials are missing the portal will show a helpful error instead of redirecting to the gateway.

## Roadmap

Future phases will add delivery logging, approval workflows, transaction reports, printing, and extended hostel management capabilities.
