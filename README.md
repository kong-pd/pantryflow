# PantryFlow

This is a small food pantry system — client can request food items, admin can manage inventory and requests. Nothing groundbreaking, but the boring parts all work: dual validation (JS + PHP), stock updates inside database transactions, session-based pickup history, login guard on admin pages.

## Run on XAMPP

Put folder at `E:\XAMPP\htdocs\pantryflow`, start Apache and MySQL, import `database/schema.sql` in phpMyAdmin.

My laptop runs Apache on port 8080:

```
http://localhost:8080/pantryflow/
```

Normal port 80:

```
http://localhost/pantryflow/
```

## Database

Default XAMPP setup, no changes needed:

```
Host: 127.0.0.1
Port: 3306
Database: pantryflow
Username: root
Password: (empty)
```

Override with env vars if needed: `PANTRYFLOW_DB_HOST`, `PANTRYFLOW_DB_PORT`, `PANTRYFLOW_DB_NAME`, `PANTRYFLOW_DB_USER`, `PANTRYFLOW_DB_PASSWORD`.

## Admin login

```
Username: pantry_admin
Password: help2026
```

Hardcoded on purpose — assessment asked for it, didn't want to build a whole users table just to act clever.

## Files

| File | What it does |
|---|---|
| `index.php` | Public inventory page |
| `request.php` | Request form + server-side processing |
| `history.php` | Session-only recent pickup references |
| `login.php` | Admin login |
| `dashboard.php` | Requests, low-stock list, add-item form |
| `add-item.php` | Saves new food item |
| `reject-request.php` | Rejects request, restores stock in a transaction |
| `inventory-action.php` | Archives, restores or deletes inventory record |
| `logout.php` | Ends admin session |
| `database/schema.sql` | Recreates database with sample data |
| `database/migrations/20260726_admin_lifecycle.sql` | Adds lifecycle columns to existing database without dropping rows |

Design document, diagrams and screenshots are in `docs/`. 

## Scope

One request, one food item. Simple one-to-many from `food_items` to `client_requests`. No cart, no payment, no email. This is a pantry system, not Shopee lah.

For a clean demo, reimport `database/schema.sql` — resets to six sample items and zero requests.
