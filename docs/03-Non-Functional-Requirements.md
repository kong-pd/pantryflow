# PantryFlow - Non-Functional Requirements

## 1. Purpose and Scope

These requirements define testable quality expectations for a small localhost assessment prototype. They intentionally avoid enterprise-scale requirements that are not relevant to the rubric.

## 2. Security

### NFR-SEC-001 - SQL Injection Resistance

**Priority:** Must

- Every SQL statement containing external input shall use PDO prepared statements and bound parameters.
- User input shall never be concatenated into SQL.
- Verification: review every query and test common SQL injection strings in login and request fields.

### NFR-SEC-002 - XSS Resistance

**Priority:** Must

- Every untrusted value rendered into HTML shall be escaped using a shared HTML-escaping helper.
- Escaping shall use `ENT_QUOTES` and UTF-8.
- Verification: submit text containing HTML tags and confirm it is displayed as text rather than executed.

### NFR-SEC-003 - Session Protection

**Priority:** Must

- `session_start()` shall run before output on pages that use session state.
- The session identifier shall be regenerated after login.
- Protected pages shall verify an authenticated administrator session.
- Logout shall invalidate administrator session state.
- Session-owned public pickup references are not authentication state and may remain after administrator logout.

### NFR-SEC-005 - Recent Pickup Privacy

**Priority:** Must

- The public interface shall not provide a searchable directory of client requests.
- A confirmation identifier shall be checked against request identifiers stored in the current server-side session before its details are returned.
- The recent-pickup view shall omit client name and contact number.

### NFR-SEC-004 - Safe Error Disclosure

**Priority:** Must

- Users shall receive helpful generic errors.
- SQL statements, database credentials, stack traces, and raw exception messages shall not be shown in the browser.
- Technical errors may be logged locally during development.

### NFR-SEC-006 - Protected Lifecycle Actions

**Priority:** Must

- Request rejection and inventory archive, restore, or delete actions shall require an authenticated administrator session.
- Lifecycle changes shall accept POST only; a direct GET shall not modify data.
- Irreversible deletion and stock-restoring rejection shall require an explicit confirmation in the administrator interface.

## 3. Data Integrity and Reliability

### NFR-DAT-001 - Referential Integrity

**Priority:** Must

- Every request shall reference an existing food item through a foreign key.
- Primary keys, foreign keys, and required indexes shall be defined in the schema.

### NFR-DAT-002 - Non-Negative Inventory

**Priority:** Must

- Inventory quantity shall never become negative.
- Both application validation and the update condition shall protect this invariant.

### NFR-DAT-003 - Historical Record Retention

**Priority:** Must

- Rejecting a request shall retain its row and foreign-key relationship.
- Archiving a food item shall retain its row and every historical request reference.
- Permanent deletion shall be refused unless the item is archived and has zero request references.

### NFR-REL-001 - Atomic Request Processing

**Priority:** Must

- Request creation and inventory decrement shall succeed or fail as one transaction.
- A failed insertion or update shall leave both tables unchanged.

### NFR-REL-002 - Duplicate Submission Prevention

**Priority:** Must

- Data-changing POST requests shall redirect after processing.
- Refreshing the resulting GET page shall not repeat the database change.

### NFR-REL-003 - Atomic and Idempotent Rejection

**Priority:** Must

- A pending-to-rejected status update and its inventory restoration shall commit or roll back together.
- Row locking and a guarded status transition shall ensure that the same request cannot restore stock more than once.
- A repeated rejection attempt shall leave both request status and inventory quantity unchanged.

## 4. Usability and Accessibility

### NFR-USA-001 - Clear Feedback

**Priority:** Must

- Validation messages shall identify the affected field or problem.
- Success and failure messages shall be visually distinct and understandable without technical knowledge.
- Form data should be preserved after a validation failure where practical.

### NFR-USA-002 - Responsive Layout

**Priority:** Must

- Public and administrator pages shall remain usable at approximately 360 px viewport width and on a standard desktop viewport.
- Tables may scroll horizontally on small screens rather than clipping content.

### NFR-USA-003 - Accessible Status Communication

**Priority:** Must

- Expired, low-stock, and error states shall use visible text or icons in addition to colour.
- Form controls shall have associated labels.
- Keyboard users shall be able to reach and submit all controls.

### NFR-USA-004 - Journey Continuity

**Priority:** Should

- A successful request shall end in a dedicated confirmation state rather than showing a blank copy of the form again.
- Recent confirmations shall remain reachable while the current browser session remains active.
- `Team access` shall remain a secondary public destination because it is intended for authorised pantry staff.

### NFR-USA-005 - Deliberate Administrative Actions

**Priority:** Should

- Request and inventory statuses shall be visible as text in the operations table.
- Archive, restore, reject, and delete controls shall use direct action labels rather than ambiguous icons.
- Rejected and archived rows shall remain legible while appearing secondary to active work.
- Permanent deletion shall be visually and verbally distinguished from reversible archiving.

## 5. Performance

### NFR-PER-001 - Appropriate Local Response Time

**Priority:** Should

- Under normal localhost conditions and the supplied sample dataset, pages should complete typical requests within one second, excluding environment startup time.
- Queries shall select only the columns required by the page.
- Indexed columns shall support foreign-key joins, pickup-date display, quantity filtering, and expiry filtering.

## 6. Maintainability

### NFR-MNT-001 - Separation of Responsibilities

**Priority:** Must

- Database connection code shall be centralised.
- Authentication checks and HTML escaping shall be reusable helpers.
- CSS and JavaScript shall be stored in external asset files.
- Business rules shall be implemented on the server rather than duplicated only in page markup.

### NFR-MNT-002 - Readable Implementation

**Priority:** Should

- Names shall describe their purpose consistently across HTML, PHP, and SQL.
- Complex or security-sensitive logic shall have concise comments explaining why it exists.
- Dead code, debug output, and unused files shall be removed before submission.

## 7. Compatibility

### NFR-COM-001 - Assessment Environment

**Priority:** Must

- The application shall run under the installed Windows XAMPP Apache, PHP, and MySQL / MariaDB environment.
- It shall not require Node.js, Composer, a framework, or an internet connection at runtime.
- It shall work in a current Chromium-based browser.

## 8. Verification Checklist

| Area | Minimum Verification |
|---|---|
| SQL injection | Test suspicious input and inspect all input-bearing queries for prepared statements. |
| XSS | Store or submit HTML-like text and verify that no script or markup executes. |
| Authentication | Open protected URLs before login, after login, and after logout. |
| Data integrity | Submit valid, excessive, zero, negative, expired, and missing-item requests. |
| Duplicate POST | Submit successfully, refresh, and confirm quantity changes only once. |
| Request rejection | Reject once and confirm status changes, the request remains, and stock returns exactly once; repeat and confirm no second restoration. |
| Inventory lifecycle | Archive and restore an item; verify public visibility, admin retention, and hard-delete restrictions. |
| Responsive UI | Test approximately 360 px and desktop widths. |
| Error handling | Trigger validation and database-safe failures without exposing internals. |
