# PantryFlow

Built this for my WAP final assessment. It is basically a small food pantry system. Nothing very groundbreaking lah, but the boring parts like validation, stock update, login guard and database transaction are all working.

## What this thing can do

- Show food inventory from MySQL.
- Mark items as available, low stock, out of stock or expired.
- Let a client request one food item for a future pickup date.
- Check the same request in JavaScript and PHP, because browser validation alone cannot trust one.
- Save the request and reduce stock in one database transaction.
- Let the pantry admin see requests, check low-stock items and add new stock.
- Block the admin pages when nobody is logged in.

## Run on XAMPP

1. Put the folder at `E:\XAMPP\htdocs\pantryflow`.
2. Start Apache and MySQL in XAMPP Control Panel.
3. Open phpMyAdmin and import `database/schema.sql`.
4. Open the site in browser.

My laptop uses Apache port 8080, so I use:

```text
http://localhost:8080/pantryflow/
```

If Apache is using the normal port 80, use:

```text
http://localhost/pantryflow/
```

No need to edit the PHP just because the port is different. The config only keeps `/pantryflow` as the application path.

## Database config

The default setup is the usual XAMPP one:

```text
Host: 127.0.0.1
Port: 3306
Database: pantryflow
Username: root
Password: empty
```

If another machine is different, the values can be changed with `PANTRYFLOW_DB_HOST`, `PANTRYFLOW_DB_PORT`, `PANTRYFLOW_DB_NAME`, `PANTRYFLOW_DB_USER` and `PANTRYFLOW_DB_PASSWORD` environment variables.

## Admin login

```text
Username: pantry_admin
Password: help2026
```

Yes, the login is hardcoded on purpose. That is what the assessment asked for, so I did not build a whole users table just to act clever haha.

## Main files

| File | What it does |
|---|---|
| `index.php` | Public inventory page |
| `request.php` | Request form and server-side processing |
| `login.php` | Admin login |
| `dashboard.php` | Requests, low-stock list and add-item form |
| `add-item.php` | Saves a new food item |
| `logout.php` | Ends the admin session |
| `database/schema.sql` | Recreates the database and sample data |

The design notes, diagrams, report draft and screenshots are all inside `docs/`. I kept them in the repo because future me will definitely forget why some boring decision was made.

## Small scope notes

One request has one food item only, so the database has a simple one-to-many relationship from `food_items` to `client_requests`. No cart, no payment, no email and no customer account. This one is a pantry assessment, not Shopee lah.

For a clean demo, import `database/schema.sql` again. It resets the tables to six sample food items and zero client requests.
