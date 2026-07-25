# PantryFlow - User Stories and Acceptance Scenarios

## 1. Actors

- **Visitor:** A person who needs to view available food and submit a pickup request.
- **Pantry Administrator:** A trusted user who monitors requests and inventory.
- **Assessor:** A reviewer who needs visible evidence that all required functions work.

## 2. Visitor Stories

### US-001 - View Inventory

**Story:** As a visitor, I want to view pantry items and their current status so that I know what may be requested.

**Related requirements:** FR-001, FR-002  
**Rubric:** A1

**Acceptance scenarios:**

- Given inventory records exist, when I open the home page, then I see each item's name, quantity, and expiry information.
- Given an item is expired, when the list is displayed, then I see a visible `Expired` label and distinct styling.
- Given an item has fewer than 5 units, when the list is displayed, then I see a `Low stock` label.
- Given an item has zero units, when the list is displayed, then I see `Out of stock`.

### US-002 - Select a Requestable Item

**Story:** As a visitor, I want the request form to show current requestable items so that I do not select unavailable food.

**Related requirements:** FR-003  
**Rubric:** A2

**Acceptance scenarios:**

- Given a non-expired item has stock, when I open the request form, then it appears in the database-populated dropdown.
- Given an item is expired or has no stock, when I open the form, then it is not selectable.

### US-003 - Receive Immediate Input Guidance

**Story:** As a visitor, I want invalid fields identified before submission so that I can correct mistakes quickly.

**Related requirements:** FR-004  
**Rubric:** A2

**Acceptance scenarios:**

- Given one or more fields are empty, when I submit, then the browser shows field-specific errors.
- Given the contact format is invalid, when I submit, then the contact field is rejected.
- Given pickup date is today or earlier, when I submit, then the pickup date is rejected.
- Given quantity is not a positive integer, when I submit, then the quantity is rejected.

### US-004 - Submit a Valid Food Request

**Story:** As a visitor, I want to submit a valid request and receive confirmation so that I know the pantry received it.

**Related requirements:** FR-005, FR-006, FR-007  
**Rubric:** A3

**Acceptance scenarios:**

- Given all data is valid and stock is sufficient, when I submit, then one request is stored and stock decreases by the requested quantity.
- Given processing succeeds, when I reach the redirected page, then I see a clear success message.
- Given I refresh the result page, then the request and stock update are not repeated.

### US-005 - Be Protected From Invalid Stock Requests

**Story:** As a visitor, I want the system to reject impossible requests so that the displayed inventory remains trustworthy.

**Related requirements:** FR-005, FR-006  
**Rubric:** A3

**Acceptance scenarios:**

- Given requested quantity exceeds current stock, when I submit, then I see an error and no database change occurs.
- Given the selected item has expired or no longer exists, when I submit, then the request is rejected.
- Given client-side validation is bypassed, when invalid data reaches PHP, then server-side validation still rejects it.

## 3. Administrator Stories

### US-006 - Log In

**Story:** As a pantry administrator, I want to authenticate so that only authorised users can access management information.

**Related requirements:** FR-008, FR-009  
**Rubric:** A4

**Acceptance scenarios:**

- Given I enter the required credentials, when I submit login, then I am redirected to the dashboard.
- Given I enter incorrect credentials, when I submit, then I remain unauthenticated and see a generic error.
- Given I am logged out, when I directly request the dashboard URL, then I am redirected to login.

### US-007 - Log Out

**Story:** As a pantry administrator, I want to log out so that another person using the browser cannot access protected pages.

**Related requirements:** FR-010  
**Rubric:** A4

**Acceptance scenarios:**

- Given I am authenticated, when I log out, then my authentication state is removed.
- Given I have logged out, when I revisit a protected URL, then I must log in again.

### US-008 - Review Client Requests

**Story:** As a pantry administrator, I want to view submitted requests so that I can prepare food for pickup.

**Related requirements:** FR-011  
**Rubric:** A5

**Acceptance scenarios:**

- Given requests exist, when I open the dashboard, then I see client name, contact, item, quantity, and pickup date.
- Given no requests exist, when I open the dashboard, then I see a clear empty state.

### US-009 - Monitor Low Stock

**Story:** As a pantry administrator, I want to see items below the required stock threshold so that I know what needs replenishment.

**Related requirements:** FR-012  
**Rubric:** A5

**Acceptance scenarios:**

- Given an item's quantity is below 5, when I open the dashboard, then it appears in the low-stock list.
- Given every quantity is at least 5, when I open the dashboard, then I see a clear empty state.

### US-010 - Add Inventory

**Story:** As a pantry administrator, I want to add a new food item so that new donations can be represented in the system.

**Related requirements:** FR-013  
**Rubric:** A5

**Acceptance scenarios:**

- Given valid name, quantity, and optional expiry date, when I submit, then the item is stored and appears on the inventory page.
- Given invalid data, when I submit, then no item is created and I see a useful error.
- Given I am not authenticated, when I attempt to invoke the add-item function, then access is denied.

## 4. Assessor Evidence Story

### US-011 - Verify the Complete System

**Story:** As an assessor, I want a concise demonstration and traceable documentation so that I can verify every rubric item efficiently.

**Related requirements:** All Must requirements  
**Rubric:** Sections A, B, and C

**Acceptance scenarios:**

- Given the submitted report, when I inspect it, then I find the architecture explanation, sitemap, request-response diagram, ERD, technology justification, references, and video link.
- Given the video, when I watch it, then all required public and administrator functions are demonstrated within five minutes.
- Given the submission package, when I inspect it, then the report PDF, source ZIP, and SQL schema are complete and organised.

## 5. Recommended Demonstration Order

1. US-001: show inventory states.
2. US-003: show client-side validation.
3. US-005: show server rejection of an excessive quantity.
4. US-004: submit a valid request and show stock reduction.
5. US-006: demonstrate failed and successful login plus protected access.
6. US-008 and US-009: show requests and low stock.
7. US-010: add a new item.
8. US-007: log out and confirm protection.
