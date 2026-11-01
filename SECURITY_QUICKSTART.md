# Security Quick Reference

This is a summary of security-relevant behavior in the current Laravel web application. Installed libraries do not automatically provide security controls; use the application code and deployment configuration as the source of truth.

## Current application controls

- Laravel session authentication and role middleware protect staff portal routes.
- Active-user middleware prevents deactivated accounts from continuing to use the system.
- Laravel's CSRF protection applies to state-changing web forms.
- Passwords are stored using Laravel's password hashing facilities, not client-side hashing.
- Public status lookup is rate-limited and returns status/details without requester identity. New requests use a separately saved last name instead of a surname guessed from the full name.
- Guest PDF and receipt-upload actions use temporary signed URLs where applicable. Receipt downloads use session authorization; upload and download routes validate access, and the public endpoints are rate-limited.
- New receipts and supporting files use private local storage, not a directly public URL.

## Production checklist

- Set `APP_ENV=production`, `APP_DEBUG=false`, a unique `APP_KEY`, and strong database/mail credentials in an untracked `.env`.
- Use HTTPS and secure web-server/TLS configuration.
- Keep private upload storage outside the public web root and back it up securely.
- Do not expose `.env`, logs, database files, or uploaded receipts through the web server.
- Do not treat JavaScript obfuscation, ID encoding, or client-side encryption as authentication or access control.
- Keep dependencies patched and review Composer/npm audit results before deployment.
- Use a separate test database; never run destructive tests against production data.

See [SECURITY_SETUP.md](SECURITY_SETUP.md) for an expanded explanation and [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) for deployment requirements.
