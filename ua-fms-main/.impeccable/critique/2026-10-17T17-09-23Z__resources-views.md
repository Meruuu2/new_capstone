---
target: my website (all portals, resources/views)
total_score: 23
max_score: 40
na_heuristics: 
p0_count: 1
p1_count: 3
target_identity: "file:C:\\capstone\\ua-fms\\resources\\views"
timestamp: 2026-10-17T17-09-23Z
slug: resources-views
---
Method: dual-agent (A: design review · B: detector + browser)

## Design Health Score: 23/40 (Acceptable)
| # | Heuristic | Score | Key issue |
|---|---|---|---|
| 1 | System status | 3 | Stepper/pills good; Control No. "Pending" reads as a status |
| 2 | Real world | 3 | GSU vocabulary; Utilization/Reservation/Booking/Direct booking overlap |
| 3 | User control | 2 | Disapprove posts in one click, no confirm or reason |
| 4 | Consistency | 2 | Public vs portal visual split; Reservations filed differently per portal; 3 labels for one action |
| 5 | Error prevention | 2 | No same-venue/date conflicts at decision; ±10 steppers |
| 6 | Recognition | 3 | Labeled sidebar; no page title in top bar |
| 7 | Flexibility | 2 | No bulk/inline approve; duplicate status tabs + select |
| 8 | Minimalist | 2 | Photo header on every page eats the fold |
| 9 | Error recovery | 2 | Native browser validation only |
| 10 | Help | 2 | Good microcopy, no help/FAQ |

## Specificity
Partly specific (UA seal, campus photos, GSU form header, 4-step tracker, ₱ rates); data pages share one generic template recipe; public site vs portals are two visual languages.
Detector: Blade CLI 0 findings (reduced Blade coverage); .html copies 26 (19 undersized text, 6 low contrast, 1 flat hierarchy - mostly print forms, flat hierarchy false positive); live overlay 8-12/page, shared sidebar: 10.5px #94a3b8 labels 2.6:1, #cbd5e1 1.5:1, placeholders 2.6:1.

## Priority issues
- [P0] pk-table overflows at 390px (723/762/717px) on approvals, bookings, maintenance; View unreachable on phone. -> adapt
- [P1] Disapprove one-click, no confirm, equal weight to Approve, no conflict context. -> harden
- [P1] Shared sidebar/placeholder contrast fails AA; outline:none app.css:933,936,4187; no skip link. -> audit, polish
- [P1] Native-only form validation on users/create and request-facility. -> harden, clarify
- [P2] Photo header on every list page; app.css:391-404 back/refresh catch-all + 218 !important. -> quieter, polish

## Persona red flags
Alex: no bulk/inline approve, Filter button required, 6-link Requests group, overlapping analytics pages, 35 stepper buttons.
Sam: no skip link, outline:none on inputs, native validation, donut chart has no text alternative.
Casey: guest form ~5,900px with summary below Submit, table overflow, donut labels overlap, /track/ticket raw JSON.

## Minor
Leading space ' Approve Requests'; developer copy on Create Direct Reservation; facility cards repeat names; orphan pagination; messy venue casing; trailing em dashes; duplicate back links on request detail; Head of Office "Not set" on reserved; dimmed Disapproved card.

## Questions
Pending-queue with inline decisions? Public identity as the system? Which single monthly page would GSU print?
