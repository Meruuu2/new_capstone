# Official GSU Forms

The application includes administrator-facing facilities and repair/maintenance form pages and document-generation routes. They are part of the Laravel application; this folder is not a separate installable form-filler project.

## Current application routes

- `GET /forms/facilities` — facilities utilization form.
- `POST /forms/facilities/download` — generate and download the completed facilities form.
- `GET /forms/repair` — repair and maintenance form.
- `POST /forms/repair/download` — generate and download the completed repair form.

These routes are protected by administrator authentication and role middleware. `GsuFormController` looks for templates under `app/templates/` first and falls back to `storage/app/templates/`. Install LibreOffice/`soffice` on the server for DOCX-to-PDF conversion and test the configured conversion path during deployment.

Templates and reference copies in `FORMS/` are source documents. Keep the deployed template files synchronized with the approved official form versions.

See [../controller-documentation.md](../controller-documentation.md) for controller behavior and [../DEPLOYMENT_GUIDE.md](../DEPLOYMENT_GUIDE.md) for server deployment.
