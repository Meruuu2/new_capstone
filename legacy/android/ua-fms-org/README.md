# Archived Organization Android Client

> **Archived:** This WebView wrapper targets the former token-gated `/fms-portal-entry` route. That route is not registered in the current Laravel web application, so this client cannot be used as documented and is not a supported deployment target. Do not build or distribute it based on old credentials or configuration.

Use the supported browser-based application and follow the repository's [Quick Start](../../../QUICKSTART.md) and [Deployment Guide](../../../DEPLOYMENT_GUIDE.md).

The legacy `FMS_LOGIN_URL`, `FMS_ACCESS_TOKEN`, and `FMS_ORG_SECRET` settings do not activate authentication or route protection in the current web application. Staff access is provided by Laravel session login at `/login` and server-side role middleware.
