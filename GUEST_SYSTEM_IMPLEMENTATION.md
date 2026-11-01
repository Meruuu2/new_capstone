# Public Guest Request Workflow

## Current behavior

Guests and community partners do not sign in or create accounts. They submit facility requests through the public form at `/request-facility`. The system files each submission under a non-interactive walk-in account and stores the visitor's actual details—including full name and explicitly entered tracking last name—in that submission's payload.

The tracking last name is a separate value. It is not inferred by splitting the visitor's full name. The confirmation page and tracking email provide the facility request code (`FM-…`); the visitor uses that code and the exact tracking last name on the homepage.

## Review, booking, and payment

GSU staff review and approve or disapprove the request. Approval attempts to reserve the requested facility and time; the booking service checks availability and conflicts before committing the reservation. Where payment is applicable, the public tracker can provide a temporary signed receipt-upload action after approval. Approved guest requests may also receive a temporary signed form download link.

## Privacy and access

- Guest submissions use `requester_type=guest` and are linked to the walk-in system account.
- Visitor identity, contact details, and the explicit tracking surname are saved on the request, not on the shared walk-in account.
- Status lookup does not require sign-in and does not expose requester identity.
- Guest accounts and the former sign-in guest portal are not the current guest workflow. Legacy `/guest/...` URLs redirect to the public request flow.

## Related implementation

- `app/Http/Controllers/PublicFacilityRequestController.php`
- `app/Http/Controllers/TicketLookupController.php`
- `app/Http/Controllers/Traits/SubmitsFacilitiesForm.php`
- `app/Services/GuestRequestEmailService.php`
- `tests/Feature/TicketLookupTest.php`

See [fdd.md](fdd.md), [model-documentation.md](model-documentation.md), and [TESTING_GUIDE.md](TESTING_GUIDE.md) for the broader system behavior and verification steps.
