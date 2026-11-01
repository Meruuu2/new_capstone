---
target: critique the website functions
total_score: 22
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 3
target_identity: "file:C:\\capstone\\ua-fms\\resources\\views"
timestamp: 2026-10-18T18-25-12Z
slug: resources-views
---
Method: dual-agent (A: design review sub-agent · B: detector + browser sub-agent)

## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|---|---|---|
| 1 | Visibility of System Status | 2 | Dashboards count ticket statuses that don't exist (in_progress/completed/rejected vs ongoing/success/failed): "Open maintenance 1" vs 2 actually open |
| 2 | Match System / Real World | 3 | Authentic GSU form codes; but one repair state has 3 names (Ongoing / In progress / Queued vs Notified) |
| 3 | User Control and Freedom | 2 | Approve / Disapprove are one-click POSTs, no confirm, no reason, no undo |
| 4 | Consistency and Standards | 1 | Venue names, venue lists, nav order, and the "create reservation" action all differ per surface |
| 5 | Error Prevention | 2 | Direct reservation silently bumps conflicting bookings to Pending behind a banner |
| 6 | Recognition Rather Than Recall | 3 | Labelled sidebar, live summary ticket, calendar legends |
| 7 | Flexibility and Efficiency | 2 | No bulk approve, no "mark all read", duplicate status filters (tabs + dropdown) |
| 8 | Aesthetic and Minimalist Design | 2 | 150-190px photo banner on every portal page; admins listed twice on Users; Quick Access repeats sidebar |
| 9 | Error Recovery | 3 | Tracker errors good; wrong-portal login says only "Access denied. Invalid credentials." |
| 10 | Help and Documentation | 2 | Guest "have these ready" list good; nothing for staff/admin |
| **Total** | | **22/40** | **Acceptable** |

## Design Specificity Verdict
Public site: specific (UA campus photos, "Kruhay Bambuhay!", peso rates, GSU-FM-004 paper-form header, FM/RM ticket codes). Portals: category-interchangeable SaaS template (navy sidebar, grey photo banner, 4 KPI tiles, dark table). Three visual languages (Bricolage black/white landing; Merriweather/navy guest pages; Inter/Merriweather portals); none uses the UA seal's green/gold/red, every photo desaturated.

Deterministic scan: `impeccable detect resources/views` returned [] (exit 0) because .blade.php files are skipped by extension. HTML-copy scan: 28 findings (undersized-ui-text 19, low-contrast 7, icon-tile-stack 1, flat-type-hierarchy 1), 25 of them in print-only forms/facilities + forms/repair (false positives). Browser overlay on 5 pages: real repeat offenders are sidebar group titles (app.css:1472-1477, 10.5px #94A3B8, 2.6:1) on every logged-in page, ±10 quantity buttons 10.5px (14 on college form), placeholder #94a3b8 2.6:1 (app.css:2812), skipped heading h1→h3 on admin dashboard, nested cards on request forms (11 college, 4 public).

## Priority Issues
1. [P1] Dashboards and Monthly Records show wrong numbers (status mismatch; KPI excludes cancelled silently; monthly trend chart empty, equipment columns "–", blank total fees, duplicate "PAGHIUSA HALL"/"Paghiusa hall"). Fix: map real statuses; label KPI; read venue list from facilities table. Backend sign-off needed. Commands: /impeccable harden, /impeccable clarify.
2. [P1] Approve / Disapprove: no confirmation, no reason (admin/forms/facilities_show.blade.php). Fix: confirm dialog (usr-modal pattern); required disapproval reason shown to requester + tracker. Command: /impeccable harden.
3. [P1] Reserved requests vanish from College/Org "My requests" while the page shows a Reserved tab and "No facility requests yet". Fix: drop tab + point to Reservations, or keep reserved rows linked to booking. Command: /impeccable clarify.
4. [P2] Venue catalogue differs per surface (4 / 8 / 11 / 12 venues, stylised names like "bUsAlian Hall", per-use vs /day pricing, requestable vs priced mismatch, equipment names differ). Fix: one venue source, one name, one unit. Command: /impeccable distill.
5. [P2] Portal tables overflow on phones (390px → 620-780px wide; status and actions off-screen). Fix: stacked row cards under 640px or scroll container with sticky first column. Command: /impeccable adapt.

## Persona Red Flags
- Jordan: guest pages drop site header (only "Back"); "Submit & pay" but payment is by email; reserved request disappears from My requests.
- Sam: no skip link; closed mobile drawer links stay in tab order; tracker scrolls invalid field under sticky header; 54-62 text nodes <12px on forms; sidebar labels 2.6:1.
- Casey: admin tables overflow; ~3,300px single guest form; calendar "Loading booked dates…" ~1.5s (scrapes full landing page).
- Alex: wrong counts; 21 vs 20 unread, no mark-all-read; no bulk approve; one action has 4 names; "Update for sender" overlaps the conversation.

## Minor Observations
Nav differs College vs Org (order; Org lacks Repair link though the route works); two Back controls on request detail; stepper "Done" while Head of Office "Not set"; Control No. column shows word "Pending"; search placeholder "BKG-123…" vs FM- codes; donut "0% 0%" overlap; top-bar portal name only on dashboard; 4 web font families; "Filter" vs "Apply Filters"; "Available" badge reads as "free".

## Questions to Consider
- Why do guests email the receipt when the system already has a payment attachment and "Payment being verified" state?
- Which visual language is the University of Antique, and where is the seal's green and gold?
- Does a GSU admin need a 12-link sidebar and KPI cards, or one "Needs your decision" queue?
