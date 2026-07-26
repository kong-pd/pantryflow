# PantryFlow Assessment Evidence Matrix

This matrix keeps the assessment proof concrete. The main answer sheet is `docs/PantryFlow-Technical-Report.docx`; source paths and screenshots below let the assessor verify each claim quickly.

## Section A - Web Application Functions

| Criterion | Implementation evidence | Report / visual evidence | Status |
|---|---|---|---|
| A1: Food listing and expired state | `index.php`, `config/pdo.php`, `database/schema.sql`, `assets/css/style.css` | Report sections 5 and 6; Figures 5 and 6; `evidence/08-current-public-experience.png` | Ready |
| A2: Request form and JavaScript validation | `request.php`, `assets/js/request-validation.js` | Report sections 4 and 6; Figure 7; `evidence/09-current-request-workflow.png` | Ready |
| A3: PHP processing, secure SQL and stock decrement | `request.php`, `includes/functions.php` | Report sections 4, 6 and 7; Figure 3; transaction and rollback discussion | Ready |
| A4: Hardcoded login, PHP session and logout | `login.php`, `includes/auth.php`, `logout.php` | Report sections 3, 4 and 6; `evidence/04-admin-access-guard.png` | Ready |
| A5: Dashboard, low stock and add item | `dashboard.php`, `add-item.php` | Report sections 4 and 6; Figures 4 and 8; `evidence/10-current-operations-workspace.png` | Ready |

The implemented administrator workspace goes slightly beyond the minimum: a pending request can be rejected with stock restored once, an item can be archived without erasing request history, and only an archived item with no request references may be deleted permanently. These rules are documented in the lifecycle diagram rather than presented as unrelated extra scope.

## Section B - Database and Programming

| Criterion | Evidence | Why it matters | Status |
|---|---|---|---|
| B1: Schema, keys, indexes, sample data and PDO | `database/schema.sql`, `database/migrations/20260726_admin_lifecycle.sql`, `config/pdo.php`, `docs/05-Database-Schema.md` | Two required tables, PK/FK relationship, query-supporting indexes, constraints, seed rows and central PDO setup | Ready |
| B2: Validation and safe processing | `request.php`, `add-item.php`, `request-action.php`, `item-action.php`, `includes/functions.php` | Browser feedback plus authoritative PHP validation, prepared statements, escaped output, row locks and transactions | Ready |

## Section C - Documentation

| Criterion | Delivered evidence | Student-owned final action | Status |
|---|---|---|---|
| C1: Architecture and technology choices | Word report sections 2 and 3; `docs/06-System-Architecture.md`; Figure 1 | Add official cover details | Ready |
| C2: Sitemap and request/response flow | Report Figures 2 and 3; diagrams in `docs/diagrams/` | None | Ready |
| C3: References and video | Twelve report references; `docs/11-References.md`; timed `docs/10-Video-Demo-Script.md` | Record the video and replace the URL placeholder | References ready; video pending |
| C4: Professional submission | Complete Word report with official rubric appendix, README, checklist, evidence set and clean source ZIP | Put cover first and export the final combined PDF | Package ready for final merge |

## Current Evidence Set

1. `01-inventory-listing.png` - original database listing and stock states.
2. `02-request-form.png` - original database-populated request form.
3. `03-request-success.png` - successful request and stock change.
4. `04-admin-access-guard.png` - unauthenticated dashboard access is blocked.
5. `05-admin-dashboard.png` - earlier authenticated dashboard state.
6. `06-responsive-mobile.png` - original narrow-viewport check.
7. `07-add-item-form.png` - protected add-item form.
8. `08-current-public-experience.png` - final public landing and availability presentation.
9. `09-current-request-workflow.png` - final request workflow and live summary.
10. `10-current-operations-workspace.png` - final high-density operations workspace.
11. `11-current-mobile-inventory.png` - final mobile hierarchy and inventory treatment.

## Honest Completion Note

The application, database, diagrams, screenshots, references, report and source archive are ready. The report intentionally has no cover page because the student will use the official institution cover. The only unresolved placeholder is the student's shareable demonstration-video URL.
