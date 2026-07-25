# PantryFlow UI/UX Refactor

> July 2026 follow-up: the visual language and request journey were refined again after a hospitality competitor benchmark. See [14-Hospitality-UX-Benchmark.md](14-Hospitality-UX-Benchmark.md).

Reference: [Vercel Web Interface Guidelines](https://vercel.com/design/guidelines)

This review used the supplied guidelines as an interface quality checklist. The visual style remains PantryFlow's own green community-pantry identity; the reference was used for interaction principles, not copied branding.

## Main Flow Changes

### Inventory to Request

Before: the user viewed a card, then opened a separate generic request page and selected the item again.

After: every requestable card has a specific `Request [item]` link. The link uses `request.php?item=[id]`, and the form verifies that the ID still belongs to a currently requestable database item before preselecting it.

### Request Form

Before: all fields appeared in one flat grid with a general instruction panel.

After:

- The page explains the three request outcomes: enter details, stock check and confirmation.
- Fields are grouped into `Your details` and `Item & pickup` sections.
- The selected item and current available quantity appear in a live summary.
- Errors are linked to their inputs and announced politely.
- Validation waits for field interaction instead of showing errors immediately on page load.
- Invalid submit moves focus to the first field that needs attention.
- Valid submit disables the button and announces the stock-checking state.

### Administrator Work

Before: the dashboard gave equal weight to an unhelpful `Admin access` statistic and operational data.

After:

- A compact operations ledger shows request count, requested units and low-stock count without separate card styling.
- The pickup-date-ordered request queue and complete inventory ledger form the left work surface, while stock watch and inventory entry stay together in a compact right rail.
- Contacts are usable telephone links.
- Each request receives a consistent `PF-####` reference, while pickup dates and low quantities retain compact text labels.
- The heading links directly to the add-item task.
- Invalid add-item submissions preserve the entered values after redirect.
- Authenticated navigation contains only Availability, Operations and Log out; public journey links remain available in the footer.
- The dashboard contains an `All food items` ledger so normal, low, expired and out-of-stock records remain visible to administrators.
- Successful inventory entry redirects directly to that ledger; the visible row replaces a redundant persistent success banner.
- Authentication status is communicated by the protected workspace itself, so the repeated signed-in username label is omitted.

## Guideline Audit

| Guideline area | PantryFlow implementation | Verification |
|---|---|---|
| Keyboard and focus | Native links, buttons, inputs and selects; skip link; one visible global `:focus-visible` ring. | Semantic DOM reviewed; all controls remain native. |
| Hit targets | Navigation is at least 44px on mobile; primary buttons are 48px. | Measured at 390px viewport. |
| Mobile input size | Inputs and selects use 16px text. | Measured at 390px viewport. |
| Clear actions | `Confirm request`, `Open dashboard`, `Add to inventory`, and item-specific request links. | DOM and end-to-end flows reviewed. |
| Helpful errors | Messages explain the correction, errors are linked with `aria-describedby`, and first invalid input receives focus. | Empty request submit tested. |
| Async updates | Flash messages and live form summaries use polite/assertive live regions as appropriate. | Success and error states tested. |
| No dead ends | Empty states offer recovery; unavailable items explain the reason; back links return to inventory. | Public routes reviewed. |
| Redundant status | Available, low stock, expired and out of stock always include text labels. | Six seed states reviewed. |
| Responsive layout | Mobile two-column navigation, single-column content and no page-level horizontal overflow. | 390px: client width 375px, scroll width 375px. |
| Useful scrollbars | The dashboard table scrolls only when its data cannot fit; the main page does not overflow. | Desktop and mobile CSS reviewed. |
| Motion safety | Only specific properties animate and reduced-motion users receive near-zero transitions. | CSS audit found no `transition: all`. |
| Visual craft | Warm neutral surfaces, thin rules, restrained type scale and dense alignment without decorative cards. | Desktop layouts reviewed at 1920px and 1366px widths. |

## Tested Outcomes

- 6 inventory records load from the clean database.
- 4 seed items are requestable; expired and out-of-stock records have no request action.
- Item deep-link preselects Rice and shows 20 available units.
- Empty submit reports name, contact and date errors and focuses `client_name`.
- Valid request stores 2 units and updates Rice from 20 to 18.
- Direct dashboard access redirects to login.
- Administrator login displays 1 request, 2 requested units and 2 low-stock items.
- Valid Lentils insert succeeds and clears the add-item form.
- 390px inventory and request layouts have no page-level horizontal overflow.
- Browser console review returned no warnings or errors.

The database was reset after these checks to 6 seed items, 0 requests and Rice quantity 20.
