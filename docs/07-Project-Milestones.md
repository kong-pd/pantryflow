# PantryFlow - Project Milestones

**Progress baseline:** 26 July 2026  
**Current position:** Code and technical QA are complete. Submission evidence and report materials are prepared; the student video and final PDF assembly remain.  
**Repository policy:** The current progress snapshot is approved for GitHub publication under `kong-pd`.

## 1. Milestone Summary

| ID | Milestone | Release Marker | Status | Completion Gate |
|---|---|---|---|---|
| M0 | Assessment Scope and Top-Level Design | `v0.1-design` | Complete | Requirements, user stories, schema and architecture agree with the assessment. |
| M1 | Project and Data Foundation | `v0.2-foundation` | Complete | XAMPP project, Git repository, schema, seed data, PDO and shared bootstrap work locally. |
| M2 | Public Inventory and Request Workflow | `v0.3-public-mvp` | Complete | Inventory listing, dynamic request form, dual-layer validation and safe stock decrement work end to end. |
| M3 | Administrator Workflow and Security | `v0.4-admin-mvp` | Complete | Login, session guard, dashboard, low-stock view, add-item action and logout work correctly. |
| M4 | Integrated MVP and Quality Gate | `v0.5-release-candidate` | Complete | Syntax checks, security checks, transaction tests, responsive review and clean database reset pass. |
| M5 | Submission Evidence and Packaging | `v0.9-submission-candidate` | In progress | References, screenshots and report drafts are ready; video, final PDF and source ZIP remain. |
| M6 | GitHub Assessment Release | `v1.0.0-assessment` | Approved snapshot | The current implementation and documentation may be committed and pushed; the final release tag waits for completed submission files. |

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
- Smoke-test data was removed; database state returned to six food items and zero client requests.
- Local port 8080 and assessor port 80 require no PHP code change.

Acceptance result: **Passed.** The current codebase is the functional release-candidate baseline.

## 3. Current Milestone - M5 Submission Evidence and Packaging

Completed in this checkpoint:

- Six labelled screenshots covering public, protected and responsive behaviour.
- Assessment criterion-to-evidence matrix.
- Technical report draft with architecture, technology choices, diagrams and testing evidence.
- Six referenced technical sources.
- A timed demonstration script below five minutes.
- A final PDF, video, source ZIP and portal checklist.

Student actions still required:

1. Fill the student information and official cover page.
2. Record the required demonstration video and paste its shareable link.
3. Merge the report, cover page, video link and official rubric into one PDF.
4. Reset the database and create the final source archive without `.git`.
5. Open the final PDF and install the extracted ZIP once before portal upload.

M5 is complete only when every assessment criterion has a named evidence item and the clean project can be installed from the README instructions.

## 4. Final Milestone - M6 GitHub Assessment Release

The user approved publishing the current code and documentation snapshot to GitHub. This push is a progress snapshot, not the final assessment tag.

Planned release steps:

1. Review `git status` and confirm the exact file scope.
2. Commit the implemented application, documentation, diagrams and evidence with a plain descriptive message.
3. Push branch `main` to `origin` as `kong-pd`.
4. Confirm the GitHub repository contains the expected files.
5. Create a final release tag only after the student video and submission package are complete.

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
