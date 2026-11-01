---
target: my website User interface design
total_score: 22
max_score: 40
na_heuristics: 
p0_count: 1
p1_count: 2
target_identity: "file:C:\\capstone\\ua-fms\\resources\\views"
timestamp: 2026-10-17T08-55-52Z
slug: resources-views
---
Method: dual-agent (A: design review · B: detector + browser)
Target: resources/views (all portals). Viewed live without a login: /, /login, /request-facility, /guest-access, /venue-rates. Admin, College, Org and Guest portal pages were reviewed from Blade source only.

## Design Health Score: 22/40 (Acceptable)

| # | Heuristic | Score | Key Issue |
|---|---|---|---|
| 1 | Visibility of System Status | 3 | Status pills, toasts, ticket popup and loading state are good. Dashboards don't show a requester's latest request or its progress. |
| 2 | Match System / Real World | 3 | Mirrors the GSU-FM-004 paper form. Rates are "per use" on /venue-rates but "/ day" on the request form. |
| 3 | User Control and Freedom | 2 | Cancel goes to url()->previous(). Every cancel or delete uses the browser's confirm(). No undo. |
| 4 | Consistency and Standards | 1 | Reservation, Booking, Direct Reservation and Direct Booking are used interchangeably. The approval page is still legacy Tailwind. 3 font stacks, about 25 font sizes. |
| 5 | Error Prevention | 2 | Clash warning and minimum date are good. Approve and Disapprove render as identical black buttons with no confirmation. |
| 6 | Recognition Rather Than Recall | 3 | Nav icons have labels and the summary ticket updates live. |
| 7 | Flexibility and Efficiency | 1 | No bulk approve, no shortcuts, no saved filters, no pending queue. |
| 8 | Aesthetic and Minimalist Design | 2 | A 200px decorative grayscale banner on every page. Quick-access grids repeat the sidebar. |
| 9 | Error Recovery | 2 | The big forms have 0 inline @error messages; errors only appear as a list at the top. |
| 10 | Help and Documentation | 3 | The guest-access "how it works" page is good. Login has no forgot-password link or help contact. |

## Priority Issues
- [P0] Approval page undermines the core admin decision (admin/forms/facilities_show.blade.php:170-192, app.css:691-698). The legacy monochrome override turns green Approve and red Disapprove into identical black buttons. There is no confirmation and no reason field, the payment warning is flattened, and the flash message shows twice.
- [P1] Low-contrast and tiny text. #94a3b8 on white (2.6:1) is used for nav group titles, help text, hints, placeholders and meta text. Text runs 9.5–10.5px in many places. On the home calendar and table: #808080 at 3.9:1, #ccc at 1.6:1, and 10px/10.5px headers.
- [P1] Inconsistent terminology and nav. Reservation/Booking naming drifts. The admin sidebar has 12 links, with Add and Create actions as top-level links. College "My facilities" is missing from the sidebar.
- [P2] Dashboards report totals instead of tasks. No "Needs your action" queue. Plotly (about 3MB) and Chart.js are both loaded. Chart.js loads unversioned on every page.
- [P2] Mobile and keyboard gaps on public pages. Public nav disappears below 760px with no menu button. No skip link. The focus ring is invisible on the dark header. On mobile the ₱ total sits below Submit. Two pages skip from h2 to h4.

## Specificity
Public side is partly specific to this product: the Kruhay welcome, the mascot, the GSU form, the venue availability board. Portals are a generic SaaS template: grayscale photo banner, KPI tiles, table, navy and Merriweather, with no UA color.

## Detector
.blade.php is not supported, so the views scan returned 0 findings: not scanned, not clean. CSS scan: 15 findings (Inter monoculture x13, bounce easing x2). Rendered pages: /login 2, / 41, /guest-access 14, /venue-rates 2. These are contrast, undersized text, kicker-above-heading, skipped headings and icon-tile stacks.
