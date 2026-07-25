# PantryFlow - System Architecture

## 1. Architecture Style

The application uses a small three-tier client-server architecture:

1. **Presentation tier:** HTML, CSS, and vanilla JavaScript in the browser.
2. **Application tier:** Apache and PHP in XAMPP.
3. **Data tier:** MySQL / MariaDB accessed through PDO.

The browser improves usability but is not trusted to enforce business rules. PHP is the authority for authentication, validation, security, and inventory updates. MySQL is the persistent source of truth.

## 2. System Architecture Diagram

```mermaid
flowchart LR
    Visitor["Visitor / Client"] --> Browser
    Admin["Pantry Administrator"] --> Browser

    subgraph Client["Client Tier - Web Browser"]
        Browser["HTML5 UI\nExternal CSS\nVanilla JavaScript validation"]
    end

    subgraph Server["Application Tier - XAMPP"]
        Apache["Apache Web Server"]
        PHP["PHP Pages and Handlers\nRouting / Validation / Auth\nBusiness Logic / Flash Messages"]
        Session["PHP Session\nAdmin authentication state"]
        Apache --> PHP
        PHP <--> Session
    end

    subgraph Data["Data Tier"]
        PDO["PDO\nPrepared Statements\nTransactions"]
        MySQL[("MySQL / MariaDB\nfood_items\nclient_requests")]
        PDO <--> MySQL
    end

    Browser -- "HTTP GET / POST" --> Apache
    PHP -- "HTML response or HTTP redirect" --> Browser
    PHP <--> PDO
```

## 3. Responsibilities by Layer

| Layer | Responsibilities | Must Not Be Trusted For |
|---|---|---|
| Browser | Render pages, responsive styling, form controls, immediate JS validation. | Final validation, authorisation, or stock decisions. |
| Apache/PHP | Route requests, validate all input, authenticate admin, apply business rules, escape output, manage PRG and flash messages. | Persistent storage without the database. |
| PDO | Manage database connection, prepared statements, transactions, and exception behaviour. | Presentation or user-facing error text. |
| MySQL / MariaDB | Persist inventory and requests; enforce PK/FK relationships and indexed access. | Browser-side interaction or session state. |

## 4. Planned Route and Component Map

| Route / File | Method | Access | Responsibility |
|---|---|---|---|
| `index.php` | GET | Public | Display complete inventory and status labels. |
| `request.php` | GET | Public | Display database-populated request form and flash messages. |
| `request.php` | POST | Public | Validate, store request, decrement stock, redirect. |
| `login.php` | GET | Public | Display login form and authentication feedback. |
| `login.php` | POST | Public | Validate hardcoded admin credentials and redirect. |
| `dashboard.php` | GET | Admin | Display requests, low stock, and add-item UI. |
| `add-item.php` | POST | Admin | Validate and insert a food item, then redirect. |
| `logout.php` | POST preferred | Admin | Clear authentication state and redirect to login. |
| `config/pdo.php` | Internal | Internal | Create configured PDO connection. |
| `includes/auth.php` | Internal | Internal | Start session and enforce admin access. |
| `includes/functions.php` | Internal | Internal | Shared escaping, redirect, flash, and validation helpers. |

The final implementation may combine a simple POST handler with its corresponding page, but route responsibilities and security boundaries must remain unchanged.

## 5. Public Inventory Request Flow

```mermaid
sequenceDiagram
    actor V as Visitor
    participant B as Browser
    participant P as Apache/PHP
    participant D as MySQL via PDO

    V->>B: Open inventory page
    B->>P: GET /pantryflow/index.php
    P->>D: SELECT inventory
    D-->>P: Item rows
    P-->>B: HTML with status labels
    B-->>V: Render inventory
```

## 6. Client Request Submission Flow

```mermaid
sequenceDiagram
    actor V as Visitor
    participant B as Browser + JavaScript
    participant P as Apache/PHP
    participant D as MySQL via PDO

    V->>B: Complete request form
    B->>B: Client-side validation
    B->>P: POST /pantryflow/request.php
    P->>P: Server-side validation
    P->>D: BEGIN + lock selected item
    D-->>P: Current item and stock
    P->>P: Re-check expiry and quantity
    P->>D: INSERT request + UPDATE stock
    D-->>P: Success
    P->>D: COMMIT
    P-->>B: 302 redirect to GET request page
    B->>P: GET /pantryflow/request.php
    P-->>B: HTML with one-time flash message
    B-->>V: Show confirmation
```

If validation or a database step fails, PHP rolls back the transaction, stores a safe error message, and redirects without changing data.

## 7. Administrator Authentication Flow

```mermaid
sequenceDiagram
    actor A as Administrator
    participant B as Browser
    participant P as Apache/PHP
    participant S as PHP Session

    A->>B: Submit credentials
    B->>P: POST /pantryflow/login.php
    P->>P: Compare with required hardcoded credentials
    alt Valid credentials
        P->>S: Regenerate ID and store admin marker
        P-->>B: 302 redirect to dashboard
    else Invalid credentials
        P->>S: Store generic error flash
        P-->>B: 302 redirect to login
    end
```

Every protected route checks session authentication before output or database modification. Logout removes the authentication state and redirects.

## 8. Security Boundaries

- Browser validation is a usability feature; PHP repeats every validation.
- External values are bound to prepared statements rather than concatenated into SQL.
- Values rendered into HTML are escaped at output time.
- Administrator authorisation is checked on every protected request, including POST handlers.
- Database exceptions are handled internally; only safe messages are shown to users.
- Data-changing requests use POST and POST-Redirect-GET.

## 9. Deployment Context

```text
E:\XAMPP\htdocs\pantryflow
        |
        +-- Apache document root mapping
        |
        +-- http://localhost/pantryflow/

XAMPP Apache/PHP  --->  XAMPP MySQL / MariaDB
```

Expected local configuration:

- Apache and MySQL are started through XAMPP.
- The `pantryflow` database is imported from `database/schema.sql`.
- PDO connection settings are centralised in `config/pdo.php`.
- The application has no runtime internet dependency.

## 10. Architecture Quality Gates

- No database query is executed directly from client-side JavaScript.
- No protected content is rendered before authentication checks.
- No business rule depends only on JavaScript.
- No data-changing operation is performed through GET.
- No external input is concatenated into SQL.
- Request storage and stock decrement are atomic.
- Every redirect occurs before HTML output.

## 11. Related Diagram Deliverables

This document contains the required system architecture and request-response flow. The final report should also include:

1. A sitemap showing all public and administrator pages.
2. The ERD from `05-Database-Schema.md`.
