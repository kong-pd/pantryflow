# PantryFlow Video Demo Script

Target length: **4 minutes 35 seconds**. Keep a small margin below the assessment's five-minute maximum.

## Before recording

- Start Apache and MySQL in XAMPP.
- Decide whether to keep the current demonstration rows or import `database/schema.sql` for the six-item baseline. Importing the schema resets local data, so do it only intentionally.
- Close notifications and unrelated browser tabs.
- Keep the application, phpMyAdmin and the important PHP files ready.
- Record at 1080p if possible and make sure the address bar and form feedback are readable.

## Timed walkthrough

### 0:00-0:20 - Context

“Hi, this is PantryFlow, my WAP final assessment. It is a small client-server pantry service built with HTML, CSS, JavaScript, PHP, PDO and MySQL on XAMPP. I will show the public request flow, protected operations workspace and how stock integrity is maintained.”

### 0:20-0:55 - Public inventory

- Open Availability.
- Point out that the list and summary counts come from MySQL.
- Show available, low-stock, unavailable and expired treatment.
- Briefly narrow the window or use device mode to show the mobile hierarchy.

### 0:55-1:30 - Request validation

- Select an available item and open Request a pickup.
- Trigger one empty or invalid-date submission to show browser feedback.
- Choose an item and show that the maximum quantity follows current stock.
- Say plainly: JavaScript helps the person, but PHP repeats every important check because browser code can be bypassed.

### 1:30-2:05 - Successful reservation

- Enter sample contact details, a future pickup date and a valid quantity.
- Submit and show the compact confirmation state.
- Open My pickups to show the session-based visitor receipt/history.
- Return to Availability and point out the reduced quantity.
- Mention that the request insert and stock decrement run in one transaction with a row lock.

### 2:05-2:30 - Protected access

- Use the Staff access link in the footer.
- If not signed in, briefly show that direct operations access redirects to login.
- Log in with `pantry_admin` / `help2026`.

### 2:30-3:25 - Operations workspace

- Show the high-density Upcoming requests table, Low stock watch and All food items list.
- Point out that public contact details are visible only in the protected workspace.
- Add one valid sample food item and show the short-lived confirmation.
- Archive that temporary item, then show the Restore/Delete lifecycle controls.
- Reject a pending demonstration request and explain that the transaction restores reserved stock once. Do not reject a row you need to keep for marking.

### 3:25-4:10 - Database and code

- In phpMyAdmin, show `food_items`, `client_requests`, their primary/foreign keys and status/lifecycle columns.
- In `request.php`, point out `beginTransaction`, `FOR UPDATE`, prepared statements, guarded stock update, commit and rollback.
- Briefly show the shared authentication guard and output escaping helper.

### 4:10-4:35 - Documentation and close

- Show the architecture, ERD, sitemap, request flow and administrator lifecycle diagrams.
- Show the final Word report and repository README.
- Close with: “The result stays small on purpose, but the stock and request state changes are handled as proper server-side transactions. Thank you.”

## After recording

- Confirm the final duration is below five minutes.
- Watch once with sound on and check that no unrelated passwords or notifications appear.
- Upload it with permissions that let the assessor open it without requesting access.
- Replace `[PASTE SHAREABLE VIDEO URL HERE]` in `PantryFlow-Technical-Report.docx`, add the official cover and export the final PDF.
