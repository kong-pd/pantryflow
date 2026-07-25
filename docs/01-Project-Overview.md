# PantryFlow - Project Overview

## 1. Document Purpose

This document defines the agreed project baseline for the Web Application Programming final assessment. It is the primary reference for scope, implementation decisions, quality gates, and submission evidence. If another project document conflicts with this overview, the conflict must be resolved before implementation continues.

## 2. Project Objective

Build a small client-server web application that enables a community food pantry to:

1. Display its food inventory to visitors.
2. Accept a visitor's request for one food item and a requested quantity.
3. Validate the request and reduce inventory safely.
4. Allow an authenticated administrator to view requests, identify low-stock items, and add new food items.

The solution must demonstrate clear separation between browser-side presentation and validation, PHP server-side processing, and MySQL data storage.

## 3. Stakeholders and Actors

| Actor | Responsibility or Interest |
|---|---|
| Visitor / Client | Views inventory and submits a food request. |
| Pantry Administrator | Logs in, monitors requests and low stock, and adds inventory items. |
| Assessor | Evaluates functionality, database design, security, documentation, and demonstration quality. |

## 4. Technology Baseline

| Layer | Selected Technology |
|---|---|
| Client | Semantic HTML5, external CSS, vanilla JavaScript |
| Server | Apache and PHP provided by XAMPP |
| Database | MySQL / MariaDB accessed through PHP PDO |
| Authentication state | PHP session |
| Development deployment | `http://localhost/pantryflow/` |

No front-end framework, PHP framework, build system, or external authentication service is required.

## 5. Scope

### 5.1 In Scope

- Inventory listing loaded from the database.
- Item name, quantity, expiry date, and visible availability status.
- Clear expired-item highlighting using both text and colour.
- A request form with client name, contact number, pickup date, item selection, and requested quantity.
- Client-side and server-side validation.
- Successful request storage and inventory decrement.
- SQL injection and XSS prevention.
- Hardcoded administrator credentials as required by the assessment.
- Session-based login, protected administrator pages, and logout.
- Administrator request list, low-stock list, and add-item function.
- SQL schema and sample data.
- Technical report, diagrams, references, rubric, and video link.

### 5.2 Out of Scope

- Visitor registration or visitor login.
- Database-managed administrator accounts or password-reset workflows.
- Multiple food items in a single request.
- Editing or deleting inventory items.
- Approving, rejecting, or changing request status.
- Email, SMS, payment, delivery, barcode, or notification integrations.
- Production hosting, cloud deployment, or public API development.

## 6. Frozen Design Decisions

These decisions prevent later rework unless the assessment instructions change.

| ID | Decision | Rationale |
|---|---|---|
| DEC-01 | One request contains one food item selected from a database-populated dropdown. | Matches the rubric's `client_requests.food_item_id` and `requested_qty` structure and keeps the prototype achievable. |
| DEC-02 | MySQL / MariaDB and PDO are mandatory for the implementation. | This is the most consistent requirement across the project description, rubric, and course scope. |
| DEC-03 | The public inventory page displays all sample items, including expired and zero-stock items, with explicit status labels. | Makes expiry highlighting and inventory state visible to the assessor. |
| DEC-04 | The request dropdown includes only items with quantity greater than zero and no past expiry date. | Prevents invalid choices without hiding rubric evidence from the public listing. |
| DEC-05 | A pickup date must be later than the current local date. | Applies the stricter interpretation of "future date only." |
| DEC-06 | Low stock means `quantity < 5`, including zero stock. | Matches the assessment threshold exactly. |
| DEC-07 | All data-changing forms use HTTP POST followed by a redirect to a GET page. | Prevents accidental repeat submissions and follows the course's POST-Redirect-GET pattern. |
| DEC-08 | Request insertion and inventory decrement occur in one database transaction. | Preserves data consistency if either operation fails. |
| DEC-09 | Administrator credentials are exactly `pantry_admin` and `help2026`. | Matches the assessment specification. |
| DEC-10 | User-facing database or stack-trace details are never displayed. | Keeps error handling clear and avoids information leakage. |

## 7. Success Criteria

The implementation is considered assessment-ready only when:

- Every Must requirement in `02-Functional-Requirements.md` passes its acceptance criteria.
- All relevant non-functional checks in `03-Non-Functional-Requirements.md` pass.
- All five Section A rubric functions can be demonstrated without manual database correction.
- The schema contains valid PKs, an FK, indexes, and at least five sample items.
- Every query containing external input uses a PDO prepared statement.
- Direct navigation to protected pages while logged out is denied.
- A valid request creates one request record and reduces stock exactly once.
- Invalid, expired, zero-stock, or excessive-quantity requests do not change the database.
- The final submission contains the report PDF, source ZIP, and SQL schema.

## 8. Planned Evidence for the Report and Video

- Public inventory page showing normal, low-stock, expired, and out-of-stock states.
- Client-side validation messages.
- Server-side rejection of an invalid or excessive request.
- Successful request followed by visible stock reduction.
- Login failure, login success, protected dashboard, and logout.
- Administrator request list, low-stock list, and successful item creation.
- Database schema / ERD, system architecture, sitemap, and request-response flow.

## 9. Traceability Sources

| Project Area | Primary Reference |
|---|---|
| Functional behaviour | `02-Functional-Requirements.md` |
| Quality and security | `03-Non-Functional-Requirements.md` |
| Actor goals and demonstration scenarios | `04-User-Stories.md` |
| Data design | `05-Database-Schema.md` |
| Component responsibilities and request flows | `06-System-Architecture.md` |
