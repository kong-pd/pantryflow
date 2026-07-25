# PantryFlow - Functional Requirements

## 1. Conventions

- **Priority: Must** means required for the assessed baseline.
- Acceptance criteria are written as observable outcomes and must be testable through the UI and/or database.
- Requirement IDs are stable and should be referenced by tests, user stories, and the technical report.

## 2. Public Inventory

### FR-001 - Display Food Inventory

**Priority:** Must  
**Rubric:** A1

The system shall retrieve food items from the database and display each item's name, available quantity, and expiry date when present.

**Acceptance criteria:**

1. The page obtains active item data from MySQL rather than a hardcoded HTML list.
2. Each displayed item contains a name and quantity.
3. A missing expiry date is displayed as `No expiry date` or an equivalent clear label.
4. The page remains usable on desktop and mobile-width screens.

### FR-002 - Display Inventory Status

**Priority:** Must  
**Rubric:** A1, A5

The system shall classify and visibly label inventory states.

**Acceptance criteria:**

1. An item whose expiry date is before today is labelled `Expired` and styled prominently.
2. An item with quantity below 5 is labelled `Low stock`.
3. An item with quantity equal to 0 is labelled `Out of stock`.
4. Status is communicated using text as well as colour.

## 3. Client Request

### FR-003 - Present the Client Request Form

**Priority:** Must  
**Rubric:** A2

The system shall provide a request form containing client name, contact number, pickup date, food item, and requested quantity.

**Acceptance criteria:**

1. The item dropdown is populated from the database.
2. Only active items with quantity greater than 0 and no past expiry date are selectable.
3. The item option displays enough information to make a useful selection, such as item name and available quantity.
4. Requested quantity has a minimum value of 1.

### FR-004 - Perform Client-Side Validation

**Priority:** Must  
**Rubric:** A2

The browser shall validate request input before submission using vanilla JavaScript and appropriate HTML attributes.

**Acceptance criteria:**

1. All fields are required.
2. Client name cannot contain only whitespace.
3. Contact number accepts digits and common separators such as `+`, spaces, and hyphens, with a total length of 8 to 20 characters.
4. Pickup date must be later than today.
5. Requested quantity must be a positive integer.
6. Clear field-specific messages are shown for invalid input.

### FR-005 - Perform Server-Side Validation

**Priority:** Must  
**Rubric:** A3

PHP shall independently validate every submitted value and shall not rely on browser validation for correctness or security.

**Acceptance criteria:**

1. Missing or malformed values are rejected.
2. Pickup date later than today is enforced on the server.
3. The selected item must exist and remain active.
4. The selected item must not be expired and must have available stock.
5. Requested quantity must be a positive integer not exceeding current stock.
6. Invalid submissions do not insert a request or change inventory quantity.

### FR-006 - Store a Valid Request and Reduce Stock

**Priority:** Must  
**Rubric:** A3, B1, B2

The system shall store a valid client request and reduce the selected item's quantity by the requested amount.

**Acceptance criteria:**

1. Request insertion and inventory decrement execute in one transaction.
2. Current stock is checked while processing the request.
3. Exactly one `client_requests` row is created for a successful submission.
4. Inventory is reduced by exactly the requested quantity and never becomes negative.
5. Both changes are rolled back if any processing step fails.

### FR-007 - Notify the Client

**Priority:** Must  
**Rubric:** A3

The system shall show a clear result after request submission.

**Acceptance criteria:**

1. A successful request opens a dedicated receipt view containing the item, quantity, pickup date, request reference, and current status.
2. A validation or stock failure produces a safe, understandable error message.
3. Submission follows POST-Redirect-GET, so refreshing the result page does not submit again.
4. Database credentials, SQL text, stack traces, and internal exception details are not exposed.

## 4. Administrator Authentication

### FR-008 - Authenticate the Administrator

**Priority:** Must  
**Rubric:** A4

The system shall authenticate an administrator using the required hardcoded credentials.

**Acceptance criteria:**

1. Username `pantry_admin` and password `help2026` succeed.
2. Other credentials fail with a generic error message.
3. Successful login stores an authenticated administrator marker in `$_SESSION`.
4. The session identifier is regenerated after successful login.
5. Login follows POST-Redirect-GET.

### FR-009 - Protect Administrator Pages

**Priority:** Must  
**Rubric:** A4

The system shall prevent unauthenticated users from accessing administrator functions.

**Acceptance criteria:**

1. Direct access to the dashboard or add-item handler without authentication redirects to login.
2. Authentication checks execute before protected content is output.
3. An authenticated administrator can access all required dashboard functions.

### FR-010 - Log Out the Administrator

**Priority:** Must  
**Rubric:** A4

The system shall provide a logout function.

**Acceptance criteria:**

1. Logout removes authentication state and destroys or invalidates the session.
2. After logout, protected pages cannot be opened without logging in again.

## 5. Administrator Dashboard

### FR-011 - View Client Requests

**Priority:** Must  
**Rubric:** A5

The administrator shall be able to view all submitted client requests.

**Acceptance criteria:**

1. Each row displays client name, contact number, food item name, requested quantity, pickup date, and request status.
2. Results are loaded from the database using a join between requests and food items.
3. Empty-state text is shown when no request exists.

### FR-012 - View Low-Stock Items

**Priority:** Must  
**Rubric:** A5

The administrator shall be able to view food items with quantity below 5.

**Acceptance criteria:**

1. The query applies the threshold `quantity < 5` exactly to active inventory records.
2. Each result displays name and current quantity.
3. Empty-state text is shown when no item is low in stock.

### FR-013 - Add a Food Item

**Priority:** Must  
**Rubric:** A5, B2

The administrator shall be able to add a new food item.

**Acceptance criteria:**

1. The form accepts name, initial quantity, and optional expiry date.
2. Name is required and quantity is a non-negative integer.
3. Server-side validation runs before insertion.
4. The insertion uses a PDO prepared statement.
5. A successful insertion appears in both the administrator inventory ledger and the public inventory page.
6. Submission follows POST-Redirect-GET.

## 6. Security Behaviour

### FR-014 - Prevent SQL Injection

**Priority:** Must  
**Rubric:** A3, B2

All SQL statements containing submitted or URL-derived values shall use PDO prepared statements with bound parameters.

### FR-015 - Prevent Reflected and Stored XSS

**Priority:** Must  
**Rubric:** A3

All untrusted text displayed in HTML shall be escaped with `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')` or an equivalent shared helper.

## 7. Recent Pickup Continuity

### FR-016 - Revisit Recent Confirmations

**Priority:** Should
**Rubric:** Usability enhancement

The system shall let a visitor revisit requests created in the current browser session without creating a public client-request directory.

**Acceptance criteria:**

1. A successful request identifier is retained in the server-side PHP Session.
2. The visitor can move between public pages and Team access, then return through `My pickups`.
3. A confirmation can be opened only when its identifier belongs to the current session history.
4. Recent pickup details are read from the database and do not display the client's name or contact number.
5. At most ten recent request identifiers are retained.
6. Missing or deleted requests are removed from the session history safely.

## 8. Administrator Record Lifecycle

### FR-017 - Reject a Pending Request

**Priority:** Should
**Rubric:** Administrator workflow enhancement

The administrator shall be able to reject a pending pickup request without deleting its audit record.

**Acceptance criteria:**

1. Only an authenticated administrator can invoke the action through POST.
2. The request status changes from `pending` to `rejected` and `reviewed_at` is recorded.
3. The requested quantity is restored to the referenced food item in the same database transaction.
4. The request row remains visible with a rejected status.
5. Repeating the action does not restore stock a second time.

### FR-018 - Archive and Restore Inventory

**Priority:** Should
**Rubric:** Administrator workflow enhancement

The administrator shall be able to retire an item from public use without removing its historical record, and restore it later.

**Acceptance criteria:**

1. Archiving sets `is_active` to `0` and records `archived_at`.
2. An archived item is absent from the public inventory and request dropdown.
3. The item remains visible in the administrator inventory ledger and in historical request joins.
4. Restoring sets `is_active` to `1` and clears `archived_at`.

### FR-019 - Delete an Unreferenced Archived Item

**Priority:** Could
**Rubric:** Administrator workflow enhancement

The administrator may permanently remove a mistaken item only when it is archived and has never been referenced by a request.

**Acceptance criteria:**

1. An active item cannot be permanently deleted.
2. An item with one or more request references cannot be permanently deleted.
3. The UI asks for confirmation before permanent deletion.
4. The foreign key remains a final database-level safeguard.

## 9. Requirement-to-Rubric Summary

| Rubric Item | Requirements |
|---|---|
| A1 | FR-001, FR-002 |
| A2 | FR-003, FR-004 |
| A3 | FR-005, FR-006, FR-007, FR-014, FR-015 |
| A4 | FR-008, FR-009, FR-010 |
| A5 | FR-011, FR-012, FR-013 |
| B1 | FR-006 and `05-Database-Schema.md` |
| B2 | FR-006, FR-013, FR-014 |

FR-016 to FR-019 are enhancements beyond the assessed baseline and do not replace any rubric requirement.
