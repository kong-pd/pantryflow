# Hospitality UX Benchmark

## Purpose

This note records the UI/UX references used for the second PantryFlow interface redesign. The goal was not to make a community pantry look expensive. The goal was to borrow the calm structure, clear service journey and sense of care found in established hospitality websites.

No hotel logo, photograph, branded copy or proprietary visual asset is used in PantryFlow.

## Competitors reviewed

### Four Seasons

Official references:

- [Four Seasons homepage](https://www.fourseasons.com/)
- [Four Seasons Malaysia](https://www.fourseasons.com/malaysia/)

Observed patterns:

- split-screen hero composition with one strong visual field and one editorial content field;
- a persistent, high-contrast booking action;
- large serif headlines paired with small spaced uppercase labels;
- generous whitespace and very few competing calls to action;
- availability and booking information grouped around a single next step.

PantryFlow translation:

- the home hero uses a dark service emblem panel and a light editorial panel;
- `Request a pickup` remains visible in the global header;
- the main action is `Arrange a pickup`, while inventory browsing is secondary;
- the visual panel is CSS-generated, so the project does not depend on copied hotel photography.

### Aman

Official reference:

- [Aman homepage](https://www.aman.com/)

Observed patterns:

- restrained navigation with a highly visible wordmark;
- warm stone, off-white and dark natural colours;
- quiet editorial typography rather than colourful application cards;
- a persistent `Reserve` action;
- deliberate use of thin rules, spacing and low visual noise.

PantryFlow translation:

- warm porcelain canvas, deep pine, charcoal and restrained champagne accents;
- square controls, thin borders and minimal shadow;
- fewer rounded containers, badges and decorative UI elements;
- serif display type for service moments and sans-serif type for usable form controls.

### Mandarin Oriental

Official references:

- [Mandarin Oriental homepage](https://www.mandarinoriental.com/en)
- [Mandarin Oriental reservation journey](https://www.mandarinoriental.com/en/reservation)

Observed booking model:

1. Property, guests and dates
2. Rooms and packages
3. Enhancements
4. Check out

PantryFlow translation:

1. Select item and review availability
2. Arrange quantity, pickup date and contact details
3. Receive confirmation after the live stock check

The request form now starts with the service choice instead of asking for personal details first. A persistent itinerary-style summary shows the selected item, quantity, pickup date and current availability.

### Six Senses

Official references:

- [Six Senses app and guest journey](https://www.sixsenses.com/en/six-senses-app)
- [Six Senses offers](https://www.sixsenses.com/en/offers/)

Observed patterns:

- the experience continues from browsing to pre-arrival planning and in-stay service;
- useful details are surfaced as `Good to know` information;
- guests can manage requests rather than treating the website as a static brochure;
- human service language is used around operational actions.

PantryFlow translation:

- the public journey explains what happens before and after submission;
- the request sidebar acts like a small pantry concierge;
- availability and confirmation expectations are visible before submission;
- the admin dashboard is presented as an operations desk with pickup, unit and low-stock priorities.

## Resulting design principles

### 1. One service, one clear next action

Every page has one dominant action. Secondary links remain visually quieter.

### 2. Selection before personal data

The request flow follows the user's actual decision order: item, quantity, date, then contact details.

### 3. A visible itinerary

The request summary updates immediately when item, quantity or date changes. This reduces memory load and makes the final submission easier to review.

### 4. Dignity rather than charity imagery

The interface avoids cliched donation photographs, emotional pressure and copied stock imagery. Abstract typography and geometry communicate care without representing pantry users as a visual subject.

### 5. Editorial calm with operational clarity

Display typography and whitespace provide a hospitality tone. Form labels, error messages, stock quantities and dashboard tables remain direct and readable.

### 6. Responsive, not merely compressed

At small sizes the layout changes order and structure: navigation becomes a compact service bar, the split hero stacks, inventory rows collapse logically, form fields become single-column, and dashboard metrics become a ledger.

## Implementation scope

The benchmark influenced:

- `includes/header.php`
- `index.php`
- `request.php`
- `login.php`
- `dashboard.php`
- `assets/css/style.css`
- `assets/js/request-validation.js`

The database schema, PHP transaction rules and authentication boundary were not changed by this visual redesign.

## Review refinements

The first hospitality-inspired draft was refined after visual review:

- the repeated `Available / Low stock / Unavailable` legend was removed because every inventory row already communicates its own state;
- the inventory heading and rows were tightened to reduce unused space while preserving calm reading rhythm;
- the unsupported `EST. 2026` statement was removed;
- the abstract `PF` hero mark was replaced with the complete `PantryFlow` name;
- implementation details and assessment wording were removed from the public footer;
- the login-to-logout session path was tested, and the public navigation was confirmed to return to `Team access` after logout.
- the desktop hero now uses a bounded golden-ratio rhythm (`61.8svh`, with practical minimum and maximum heights) so the pickup journey is visible in the first screen;
- interior page headings, the request progress bar and Team Access composition were reduced together so restraint remains a system-wide rule rather than a homepage-only treatment;
- desktop layouts were checked at 1920 x 920 and 1366 x 768, while the stacked mobile layout was checked separately at 390 x 844.
- the hero uses a content-safe minimum height rather than a fixed height, preventing the visual panel from overlapping the pickup journey at browser zoom levels;
- the redundant journey top border was removed so the hero ledger and section transition do not create competing horizontal rules;
- inventory display type, quantities and row height were reduced together to restore a quieter hierarchy;
- request groups use labelled sections instead of visually styled `fieldset/legend` elements, avoiding browser-specific spacing between a group title and its supporting copy.
- a successful request now redirects to a dedicated confirmation state instead of placing a success alert above a fresh copy of the form;
- the confirmation state contains only the reserved item, quantity, pickup date and two restrained next actions, so the journey ends clearly without repeating instructions or progress controls.
- recent confirmation references are retained only in the current PHP browser session and can be revisited from `My pickups`; the public interface does not expose a searchable client-request directory;
- `Team access` is a secondary footer destination for unauthenticated visitors, while authenticated administrators continue to receive direct `Operations` and `Log out` controls in the header.
- the administrator masthead was reduced to a one-line overview with an integrated metrics ledger, allowing the pickup queue to enter the first desktop viewport;
- the desktop dashboard now works as a dense two-column operations desk: the pickup queue flows directly into the complete inventory ledger on the left, while low-stock review and inventory entry share a compact right rail;
- the redundant login-success banner and public journey actions were removed from the authenticated header;
- the dashboard table scrolls inside its own container at 390 px without introducing page-level horizontal overflow.
- the complete queue and inventory tool rail fit within a 1920 x 920 working viewport, while the 1366 px layout preserves the same hierarchy without page-level overflow.
- a complete inventory ledger now follows the workbench and is linked from its command area, ensuring high-quantity items remain discoverable rather than appearing only when stock is low;
- successful item entry redirects to the resulting inventory evidence without a persistent banner, and the redundant signed-in username was removed.
