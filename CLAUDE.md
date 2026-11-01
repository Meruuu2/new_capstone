# UA Facility Management System – Project Rules

Laravel (PHP) backend, Blade frontend, MySQL database, deployed on a LEMP stack.

## Scope

- No directory is off-limits, including `legacy/` and `laravel_app/`.
- Make changes across the frontend and backend as needed to fulfill the user's request.

## UI Conventions

- Primary focus: modernizing and improving UI/UX within Blade templates.
- Maintain a clean aesthetic. Visual upgrades must enhance readability without disrupting existing layouts or breaking functional code.
- When creating or fixing any UI, always also fix the margins, padding, font size, and font styles of the affected elements.
- Navigation must look and behave the same across all user portals (Admin, College Staff, Organization Unit, Guest Access): same colors, sidebar/header treatment, icons, and unique styling. When the navigation changes for one portal, apply the same change to the others.

## Database

- You may run `php artisan migrate` and `php artisan db:seed` on the local MySQL database to verify layout changes and test component rendering with populated data.

## Testing

- Always verify UI changes with Playwright.
- Provide a report or screenshot preview of the modified UI elements before marking a task as complete.

## Git

- Do not commit any changes unless explicitly instructed to do so.
- When asked to commit, use concise Conventional Commits format, e.g. `feat(ui): update navigation bar layout` or `style: polish dashboard cards`.
