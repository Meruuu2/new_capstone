# Current Documentation and Verification Notes

This file tracks only current follow-up items. Historical audit findings are preserved in `NEED_TO_BE_FIX.md`, `NEED_TO_FIX_AND_UPDATE.md`, and dated critique snapshots; those records are not an authoritative live backlog.

## Routine verification after workflow changes

- [ ] Run `php artisan test` and record the current result in release notes when it changes.
- [ ] For UI changes, use Playwright at desktop and mobile widths and record what was checked.
- [ ] Confirm database migrations are applied in the target environment before using new fields.
- [ ] Re-check route and workflow documentation against `routes/web.php` and the controllers when endpoints change.

## Current tracking-name behavior

- New staff accounts store an explicit `users.last_name`; new guest requests store `requester_last_name` in the submission payload.
- Status lookup uses the explicit value and does not infer a surname from a full name.
- Existing records without an explicit tracking value retain legacy matching; no surname backfill should split historical names.
- Guest and college request/approval/booking/tracker flows have feature-test coverage.
