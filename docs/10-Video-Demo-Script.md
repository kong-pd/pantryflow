# PantryFlow Video Demo Script

Target length: about 4 minutes 35 seconds. This leaves a small safety margin below the five-minute limit.

## Before Recording

- Import `database/schema.sql` so the demo starts with six items and no requests.
- Start Apache and MySQL.
- Close notifications and unnecessary browser tabs.
- Keep phpMyAdmin, the application and a few important code files ready.
- Record at 1080p if possible and make sure the address bar is readable.

## Timed Walkthrough

### 0:00-0:20 — Introduction

“Hi, this is PantryFlow, my Web Application Programming final assessment. It is built with HTML, CSS, JavaScript, PHP, PDO and MySQL on XAMPP. I will demonstrate the public request flow, administrator functions and the main security controls.”

### 0:20-0:55 — Inventory

- Open the inventory page.
- Point out that the six cards are loaded from MySQL.
- Show available, low-stock, out-of-stock and expired labels.
- Resize the browser briefly to show the responsive layout.

### 0:55-1:30 — Request Validation

- Open Request food.
- Submit an empty form or choose an invalid date to show browser feedback.
- Select Rice and show that the quantity maximum comes from current stock.
- Briefly say that PHP repeats the validation because JavaScript can be bypassed.

### 1:30-2:05 — Successful Request

- Enter a real-looking name and contact number.
- Choose a future pickup date and request two Rice.
- Submit and show the success message.
- Return to inventory or refresh the form to show Rice reduced from 20 to 18.
- Mention the transaction and row lock: request insert and stock decrement either complete together or roll back.

### 2:05-2:35 — Protected Admin Access

- Log out if needed.
- Type `/dashboard.php` directly.
- Show the redirect and “Please log in” message.
- Log in with `pantry_admin` / `help2026`.

### 2:35-3:20 — Dashboard

- Show the request table and the request just created.
- Show the low-stock list for quantities below five.
- Add a sample item with valid data and show the confirmation.
- If time allows, try one invalid quantity to show server validation.

### 3:20-4:05 — Database and Code

- In phpMyAdmin, show `food_items` and `client_requests` plus the foreign key.
- Open the transaction section in `request.php`.
- Point out `beginTransaction`, `FOR UPDATE`, prepared statements, guarded stock update, `commit` and rollback.
- Briefly show the shared session guard and escaped output helper.

### 4:05-4:35 — Documentation and Close

- Show the four diagrams and the README setup notes.
- Say: “This completes the required public, database and administrator workflows. Thank you.”

## After Recording

- Check that the whole video is under five minutes.
- Watch once with sound on and confirm passwords or personal notifications are not exposed.
- Upload it where the assessor can open it without requesting access.
- Paste the final URL into `docs/09-Technical-Report-Draft.md` and the final PDF.
