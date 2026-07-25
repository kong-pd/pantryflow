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

### NFR-SEC-004 - Safe Error Disclosure

**Priority:** Must

- Users shall receive helpful generic errors.
- SQL statements, database credentials, stack traces, and raw exception messages shall not be shown in the browser.
- Technical errors may be logged locally during development.

## 3. Data Integrity and Reliability

### NFR-DAT-001 - Referential Integrity

**Priority:** Must

- Every request shall reference an existing food item through a foreign key.
- Primary keys, foreign keys, and required indexes shall be defined in the schema.

### NFR-DAT-002 - Non-Negative Inventory

**Priority:** Must

- Inventory quantity shall never become negative.
- Both application validation and the update condition shall protect this invariant.

### NFR-REL-001 - Atomic Request Processing

**Priority:** Must

- Request creation and inventory decrement shall succeed or fail as one transaction.
- A failed insertion or update shall leave both tables unchanged.

### NFR-REL-002 - Duplicate Submission Prevention

**Priority:** Must

- Data-changing POST requests shall redirect after processing.
- Refreshing the resulting GET page shall not repeat the database change.

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
| Responsive UI | Test approximately 360 px and desktop widths. |
| Error handling | Trigger validation and database-safe failures without exposing internals. |
