# PantryFlow - Project Milestones

**Progress baseline:** 26 July 2026  
**Current position:** Code, technical QA, evidence capture, the complete Word report and the source archive are ready. Only student-owned submission details remain: the official cover page, video URL and final PDF merge.
**Repository policy:** The implementation and assessment materials are published to GitHub `main` under `kong-pd`.

## 1. Milestone Summary

| ID | Milestone | Release Marker | Status | Completion Gate |
|---|---|---|---|---|
| M0 | Assessment Scope and Top-Level Design | `v0.1-design` | Complete | Requirements, user stories, schema and architecture agree with the assessment. |
| M1 | Project and Data Foundation | `v0.2-foundation` | Complete | XAMPP project, Git repository, schema, seed data, PDO and shared bootstrap work locally. |
| M2 | Public Inventory and Request Workflow | `v0.3-public-mvp` | Complete | Inventory listing, dynamic request form, dual-layer validation and safe stock decrement work end to end. |
| M3 | Administrator Workflow and Security | `v0.4-admin-mvp` | Complete | Login, session guard, dashboard, low-stock view, add-item action and logout work correctly. |
| M4 | Integrated MVP and Quality Gate | `v0.5-release-candidate` | Complete | Syntax checks, security checks, transaction tests, responsive review and clean database reset pass. |
| M5 | Submission Evidence and Packaging | `v0.9-submission-candidate` | Complete for handoff | Complete Word report, current screenshots, references, video script, evidence matrix and source ZIP are ready. |
| M6 | GitHub Assessment Release | `v1.0.0-assessment` | Published to `main` | Code and documentation are pushed; the optional final tag waits for the student's cover and video URL. |

## 2. Completed Milestones

### M0 - Assessment Scope and Top-Level Design

Delivered:

- Project overview and scope boundaries.
- Functional and non-functional requirements.
- User stories with acceptance criteria.
- Two-table relational schema aligned with the single-item request requirement.
- System architecture, ERD, sitemap and request-response sequence diagrams.
- Classic draw.io-style SVG and PNG diagram exports.

Acceptance result: **Passed.** The design matches the implemented application and does not introduce unsupported entities or functions.

### M1 - Project and Data Foundation

Delivered:

- Project location: `E:\XAMPP\htdocs\pantryflow`.
- Local Git branch: `main`.
- GitHub remote: `https://github.com/kong-pd/pantryflow.git`.
- `pantryflow` MySQL/MariaDB database.
- `food_items` and `client_requests` tables with PK, FK, indexes and constraints.
- Six clean inventory seed records.
- Central configuration, PDO connection, session bootstrap and shared helpers.

Acceptance result: **Passed.** Database import, PDO access and project paths were verified on XAMPP.

### M2 - Public Inventory and Request Workflow

Delivered:

- Responsive inventory listing populated from MySQL.
- Available, low-stock, out-of-stock and expired status labels.
- Red visual treatment and text label for expired items.
- Request dropdown populated from current requestable database items.
- JavaScript validation for required fields, contact, date and quantity.
- Repeated PHP server validation.
- Prepared statements, `SELECT ... FOR UPDATE`, transaction, guarded stock update and rollback path.
- Post/Redirect/Get success and error feedback.

Acceptance result: **Passed.** Valid requests insert one record and decrement stock; invalid requests leave stock unchanged.

### M3 - Administrator Workflow and Security

Delivered:

- Hardcoded assessment login: `pantry_admin` / `help2026`.
- PHP Session authentication and session ID regeneration.
- Direct-access guards for protected actions.
- Administrator dashboard with all client requests and low-stock items.
- Validated add-food-item action.
- POST logout and protected-route redirect after logout.
- PDO prepared statements and escaped output for SQL injection and XSS protection.

Acceptance result: **Passed.** Authentication, authorisation, stored-output escaping and logout behaviour were verified.

### M4 - Integrated MVP and Quality Gate

Completed checks:

- All PHP files pass `php -l`.
- Front-end JavaScript passes the Node syntax check.
- Ten HTTP/database smoke tests pass.
- SQL-injection-style login input is rejected.
- Stored XSS test input is rendered as text, not executable HTML.
- Excessive quantity is rejected without an inventory change.
- Desktop pages and a true 390-pixel responsive viewport were visually reviewed.
- Administrator dashboard renders correctly with no browser console errors.
- Temporary automated test rows were removed without resetting or overwriting the user's current demonstration data.
- Local port 8080 and assessor port 80 require no PHP code change.

Acceptance result: **Passed.** The current codebase is the functional release-candidate baseline.

## 3. M5 - Submission Evidence and Packaging

Completed for handoff:

- Eleven labelled screenshots, including the final public, request, administrator and mobile interfaces.
- Assessment criterion-to-evidence matrix.
- Complete report at `docs/PantryFlow-Technical-Report.docx`, excluding only the official cover page as requested.
- Architecture, ERD, sitemap, request-response and administrator lifecycle diagrams embedded in the report.
- Twelve references covering official platform guidance and the relevant course lecture decks.
- A timed demonstration script below five minutes.
- A clean source ZIP generated from the committed repository and a final portal checklist.

Student actions still required:

1. Fill the student information and official cover page.
2. Record the required demonstration video and paste its shareable link.
3. Replace the video placeholder in the Word report, put the official cover page first and export one final PDF.
4. Open the final PDF and install the extracted ZIP once before portal upload.

Every criterion now has a named evidence item. The remaining actions require the student's identity details or recorded video and therefore sit outside the code handoff.

## 4. Final Milestone - M6 GitHub Assessment Release

The user approved publishing the code and documentation directly to GitHub `main`. The repository now contains the application, schema, diagrams, evidence and final Word report. No pull request was created because this is the user's own assessment repository.

Completed release steps:

1. Reviewed the exact file scope and preserved user-created database records.
2. Committed the implementation, documentation, diagrams and evidence with plain descriptive messages.
3. Pushed branch `main` to `origin` as `kong-pd`.
4. Prepared the report and source archive for submission.
5. Left the optional `v1.0.0-assessment` tag until the student inserts the final cover and video URL.

## 5. Scope Freeze

The following changes are excluded unless the assessment requirement changes:

- User self-registration or a database-backed administrator table.
- Multi-item requests or a separate request-line table.
- Online payment, email delivery or third-party APIs.
- Framework migration away from the required HTML/CSS/JavaScript, PHP, PDO and MySQL stack.

This freeze protects the rubric-aligned implementation from unnecessary rework.

## 6. Overall Definition of Done

PantryFlow reaches the final assessment release when:

- All functional and non-functional requirements are satisfied.
- The database can be recreated from `database/schema.sql`.
- Public and protected workflows pass on a clean XAMPP installation.
- Security controls can be demonstrated and explained.
- Documentation and diagrams match the submitted code.
- Required references, evidence and video are complete.
- Final source, SQL and documentation are packaged correctly.
- GitHub publication occurs only after explicit approval.
