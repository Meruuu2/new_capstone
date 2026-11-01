# User Onboarding and Training Guide

## Purpose

Use this guide to train University of Antique Facility Management System users. It covers the current web application workflows for guests, College Staff, Organization Unit Staff, and GSU administrators.

Conduct hands-on practice in a training or staging environment with test accounts and disposable records. Do not practice approvals, cancellations, account deactivation, or payment verification on live requests.

## Before training

The trainer should:

1. Confirm the application is available and identify the correct environment and homepage URL.
2. Prepare one active test account for each staff role. Give credentials to each trainee privately; never put passwords in slides, screenshots, or shared documents.
3. Confirm the test environment has sample active facilities and that email delivery is either configured or clearly identified as simulated.
4. Explain that guests do not sign in; staff use the portal appropriate to their role.
5. Explain that staff accounts can be locked for 30 minutes after five failed sign-in attempts from the same IP address. Users sharing a public IP may share this cooldown.

## Common orientation

### Signing in and signing out

- Staff open the portal assigned to their role and sign in with their assigned email and password.
- Passwords must be 8–94 characters and include uppercase and lowercase letters and a special character.
- Use the **Logout** control when finished, especially on shared computers.
- If an account is deactivated, contact the GSU administrator. Do not repeatedly retry a password after the login cooldown begins.

### Moving around

- Use the sidebar to move between the portal dashboard, requests, bookings/calendar, facilities, users, and other role-authorized sections.
- The notification area shows updates relevant to the signed-in user.
- Available navigation and actions depend on role. A missing menu item may mean the feature is not available to that role.

### Facility requests and reservations

- Check the calendar and facility availability before submitting.
- The request form displays venue information such as estimated seating capacity; guest venue choices also show published rates.
- Select the facility or facilities, event date(s), time, purpose, and required equipment, then check the details before submitting.
- Requests must be made at least 30 days before the event. Reservation times must be between 6:00 AM and 7:00 PM.
- Submitting a request does not itself mean a reservation is approved. GSU reviews the request; an overlapping or unavailable booking can prevent reservation creation.
- Keep the generated `FM-…` request code. A created booking has a separate `BKG-…` code.

### Public request tracking

The public tracker is read-only. Look up a request with its tracking code and the exact last name entered or recorded for that submission. It does not derive a tracking last name from a full name. Keep the code private and do not post it in public.

## Guest workflow

Guests do not create an account or sign in.

1. Open **Request a Facility** from the public homepage.
2. Review venue choices, seating information, and any displayed rate.
3. Enter applicant details, including the full name and the separate last name used for tracking.
4. Complete the event, venue, schedule, purpose, and equipment fields; attach requested files only when needed.
5. Review the form and submit it.
6. Save the `FM-…` tracking code from the confirmation and the tracking last name. Check email for the tracking message if delivery is enabled.
7. Use the public tracker to check progress. If GSU requests payment after approval, follow the instructions shown for the request and upload the receipt through its authorized link.
8. Use any PDF or receipt link only for the corresponding request; signed links can be temporary.

**Practice exercise:** Submit a clearly identified test request for a date more than 30 days away, then look it up using the exact tracking last name. Do not use a real person's personal details for training.

## College Staff workflow

### Submit and follow a facility request

1. Sign in through the College Staff portal.
2. Open the facility request form and review venue availability and seating information.
3. Complete the event details, schedule, facility selection, and equipment.
4. Submit, save the `FM-…` code, and follow the status in **My Requests** or the public tracker.
5. Wait for GSU review; do not treat a pending request as a confirmed reservation.

### Report and discuss a repair

1. Open the maintenance/repair requests section and create a ticket.
2. Select the affected active facility or choose **Others** and identify the location.
3. Enter a clear subject and description of the issue. Avoid putting passwords or unrelated sensitive information in the description.
4. Submit and save the `RM-…` ticket code.
5. Open the ticket to follow status and communicate with GSU in its conversation. Messages may appear in real time; refresh or allow the page to update if needed.
6. Tickets are visible to eligible members of the same College team and GSU administrators.

### College team accounts and facilities

- Only authorized unit heads can create, edit, or deactivate team accounts. Staff can edit their own permitted profile details.
- If your account is authorized to access College facility management, facilities submitted for addition are inactive/unavailable until verified by GSU.
- Do not represent a newly submitted facility as bookable until GSU has made it active and available.

**Practice exercise:** Create a test maintenance ticket, add a message, and verify that the ticket remains accessible from the College account. Do not report a real fault as a test.

## Organization Unit Staff workflow

1. Sign in through the Organization Unit portal.
2. Use the facility request form to submit an event request and save its `FM-…` tracking code.
3. Review status in **My Requests** and check the calendar/bookings available to the account.
4. Unit heads can manage eligible Organization Unit team accounts through **Users**.

Organization Unit accounts do **not** have maintenance/repair reporting or repair-chat access. Contact the appropriate College or GSU office through established channels to report a facility issue.

**Practice exercise:** Submit and track a test facility request, then locate its status and event details in the Organization Unit portal.

## GSU Administrator workflow

GSU administrative actions affect shared operational records. Practice only in a test environment.

### Review facility requests

1. Open **All Requests** and find a submission by its status, venue, month, or control number.
2. Open the submission and verify the requester, requested facilities, dates, times, purpose, and supporting details.
3. If payment is uploaded and awaiting verification, inspect and verify it before approving or disapproving.
4. Approve only when the request is valid, payment requirements are satisfied, facilities are available, and no booking conflict exists.
5. For disapproval, enter a clear reason. The reason is saved and included in the status communication.
6. Confirm the resulting request status and booking code. If a reservation cannot be created because of a conflict or unavailable facility, resolve the issue before taking further action.

### Manage facilities, users, and bookings

- Open **All facilities** to create, edit, activate, or deactivate facilities. Deactivation is preferred over deletion so historical records remain understandable.
- Open **Users** to review accounts and make authorized changes. Deactivation is not deletion and can affect upcoming requests or bookings; review the confirmation carefully.
- Use the booking list and calendar to review reservations. Direct bookings and schedule changes should be made only under approved GSU procedures.

### Handle repair tickets

1. Open the maintenance/repair queue and select a ticket.
2. Review the reported location, issue, and status history.
3. Update status or remarks according to the actual work and communicate with the College requester in the ticket conversation.
4. Avoid placing sensitive personal or security information in remarks.

### Audit trail

Use **Audit Trail** to search and review recorded account, facility, request, reservation, and maintenance changes. The trail is read-only in the application. Treat it as sensitive operational data.

**Practice exercise:** In the test environment, review a test request, complete the approval flow only if the exercise explicitly calls for it, verify the resulting booking, then locate the recorded change in the audit trail.

## Safety and data handling

- Use only your own assigned account. Never share credentials or sign in as another employee.
- Do not use real guest contact information, real payment receipts, or confidential incident details in training.
- Verify the recipient, request code, venue, date, and time before approval, payment verification, cancellation, or schedule changes.
- Keep tracking codes, signed links, and private attachments confidential.
- Report unexpected access, incorrect records, suspected account compromise, or a lost device to the GSU administrator promptly.
- Use the production system only for real work. Complete exercises using a designated training environment and test data.

## Trainer completion checklist

The trainee can demonstrate that they can:

- [ ] Sign in and log out safely (staff roles).
- [ ] Find portal navigation and notifications.
- [ ] Explain the difference between a submitted request, an approved request, and a reserved booking.
- [ ] Locate venue capacity and any displayed guest rates.
- [ ] Explain the request lead time, operating hours, and tracking-code/last-name requirement.
- [ ] Complete the workflow relevant to their role and retrieve its tracking code.
- [ ] Identify actions that are restricted to another role or require GSU approval.
- [ ] Protect personal information, credentials, codes, receipts, and signed links.

Record the trainee's name, role, training date, trainer, environment used, and any follow-up training needed in the institution's approved training record. Do not store passwords or sensitive request details in that record.
