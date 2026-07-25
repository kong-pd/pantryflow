# PantryFlow Assessment Evidence Matrix

This checklist links each rubric item to something concrete in the submitted project. It is mainly for the final audit, so no need to hunt around during the demo.

## Application Criteria

| Criterion | Evidence in the project | Demo / screenshot evidence | Status |
|---|---|---|---|
| A1: Display food items | `index.php`, `config/pdo.php`, `database/schema.sql` | `evidence/01-inventory-listing.png` shows six database items and clear stock states. | Ready |
| A2: Expired item display | Expiry comparison and status output in `index.php`; visual state in `assets/css/style.css` | `evidence/01-inventory-listing.png` shows expired Milk Powder in red with an Expired label. | Ready |
| A3: Client request form | `request.php`, `assets/js/request-validation.js` | `evidence/02-request-form.png` shows the dynamic form; `evidence/03-request-success.png` shows a saved request and reduced Rice stock. | Ready |
| A4: Administrator access and dashboard | `login.php`, `includes/auth.php`, `dashboard.php`, `logout.php` | `evidence/04-admin-access-guard.png` proves direct access is blocked; `evidence/05-admin-dashboard.png` shows requests and low stock. | Ready |
| A5: Add food item | `dashboard.php`, `add-item.php` | `evidence/07-add-item-form.png` shows the protected add-item task; demonstrate the success message in the video. | Ready |

## Database and Programming Criteria

| Criterion | Evidence in the project | Explanation to give | Status |
|---|---|---|---|
| B1: Database design and PDO | `database/schema.sql`, `config/pdo.php`, `docs/05-Database-Schema.md`, `diagrams/02-database-erd.png` | Two rubric-required tables, PK/FK, indexes, check constraint and prepared PDO access. | Ready |
| B2: Validation and secure processing | `request.php`, `assets/js/request-validation.js`, `add-item.php`, `includes/functions.php` | Client and server validation, output escaping, prepared statements, transaction and row lock before stock decrement. | Ready |

## Documentation Criteria

| Criterion | Evidence in the project | Remaining student action | Status |
|---|---|---|---|
| C1: Architecture and technology choices | `docs/09-Technical-Report-Draft.md`, `docs/06-System-Architecture.md`, `diagrams/01-system-architecture.png` | Put student details on the cover page. | Draft ready |
| C2: Diagrams | Architecture, ERD, sitemap and request-response diagrams in `docs/diagrams/` | Insert the PNG exports into the final report PDF. | Ready |
| C3: References and video | `docs/11-References.md`, `docs/10-Video-Demo-Script.md` | Record the video and replace the video-link placeholder. | Partly pending |
| C4: Professional submission | `README.md`, `docs/12-Submission-Checklist.md`, clean SQL installer | Merge cover, report, video link and marking rubric into one PDF; create the final source ZIP. | Partly pending |

## Evidence Files

1. `01-inventory-listing.png` — inventory comes from the database and uses visible states.
2. `02-request-form.png` — client request fields and current item quantity.
3. `03-request-success.png` — successful request feedback and inventory decrement.
4. `04-admin-access-guard.png` — unauthenticated dashboard access redirects to login.
5. `05-admin-dashboard.png` — authenticated request table, low-stock section and add-item form.
6. `06-responsive-mobile.png` — inventory at a real 390-pixel viewport without horizontal overflow.
7. `07-add-item-form.png` — completed administrator form for adding a valid inventory item.

## Honest Completion Note

The code, diagrams, references, screenshots and report content are prepared. The final video, its shareable URL, student details, official cover page and attached marking rubric still need to be supplied before portal submission.
