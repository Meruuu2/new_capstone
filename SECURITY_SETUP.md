# Security Notes — UA Facility Management System

This document describes the security boundaries relevant to the current Laravel web application. It does not claim that every security-related dependency in the package manifests is actively used.

## Authentication and authorization

- Staff use Laravel session-based authentication.
- Role middleware restricts admin, college-staff, and organization-staff routes.
- Active-account checks prevent deactivated users from continuing to access protected pages.
- Public routes are intentionally limited to public information and guest workflows. The homepage status tracker does not allow record editing.
- College maintenance-ticket access is scoped to members of the requester's college. Organization users do not have maintenance-ticket routes or access.

## Passwords, forms, and sessions

Use Laravel's password hashing and session mechanisms. Do not add client-side password hashing as a substitute for HTTPS or server-side password verification. Keep CSRF protection enabled for state-changing requests, use HTTPS in production, and configure cookies and trusted proxies consistently with the production web server.

## Public tracking and guest uploads

- The tracker requires a tracking code and the submitter's last name and is rate-limited. New submissions store a separate explicit tracking last name; it must not be inferred from the full name.
- A mismatch does not disclose whether the record exists. Lookup results omit requester identity and do not permit editing.
- Guest PDF and receipt-upload actions use temporary signed links where applicable. Receipt downloads use session authorization. Upload routes validate file type and size, and receipt/attachment downloads are authorization-checked.
- New receipt files are stored outside the public web root. Do not expose the private receipt directory using a public alias or storage symlink.

## Production configuration

1. Set `APP_DEBUG=false` and `APP_ENV=production`.
2. Generate and preserve a unique `APP_KEY`; do not commit `.env`.
3. Use least-privilege database credentials and strong mail credentials.
4. Serve only the `public/` directory over HTTPS.
5. Keep `storage/` and `bootstrap/cache/` writable only by the required service account.
6. Back up the database and private upload directory using protected storage.
7. Keep dependencies updated and review audit findings. Do not run automatic audit-fix commands without reviewing the proposed changes.

## Dependency note

The project manifest contains cryptography, JWT, Hashids, and JavaScript utility packages. Their presence does not establish that the application uses them for authentication, encryption of form data, or protection of records. Rely on the implemented Laravel session, role, validation, signed-route, and private-storage controls described above; review actual call sites before adopting any additional library.

For deployment procedures, see [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md).
