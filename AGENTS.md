# PQRSF Platform

Laravel 13 app in `./src`, served only through Docker from the repo root. Stack: Filament v5 admin, MySQL 8.4, Tailwind CSS v4, Alpine.js, DomPDF, pnpm/Vite.

## Runtime And Commands

- Start/refresh services from repo root: `docker compose up -d --build`.
- Laravel runs in service `app` at `/var/www`; run Artisan/Composer there, e.g. `docker compose exec app php artisan ...` and `docker compose exec app composer ...`.
- Vite/pnpm runs in service `vite`: `docker compose exec -w /var/www vite pnpm run build`.
- Format PHP: `docker compose exec app ./vendor/bin/pint <paths>`.
- Full test suite: `docker compose exec app php artisan test`.
- Focused test: `docker compose exec app php artisan test tests/Feature/AdminPqrsfSubmissionsTest.php`.
- Reset dev DB: `docker compose exec app php artisan migrate:fresh --seed`.
- Root `.env` only supplies Docker `UID`/`GID`; Laravel config is `src/.env`. After changing `src/.env`, run `docker compose exec app php artisan config:clear`.
- Inside containers, MySQL is `db:3306`; host access is port `3307`. App is `http://localhost:8080`, phpMyAdmin `http://localhost:8081`, Vite HMR `5173`.

## Architecture Hotspots

- Public form `/pqrsf`: `app/Http/Controllers/PqrsfController.php`, `app/Http/Requests/StorePqrsfRequest.php`, `resources/views/pqrsf/*`, layout `resources/views/layouts/public.blade.php`.
- Admin `/admin`: Filament panel in `app/Providers/Filament/AdminPanelProvider.php`; admin access is `role === 'admin'` via `User::canAccessPanel()` and the `access-admin` gate.
- Filament v5 resources are split by resource folder, table and schema, e.g. `app/Filament/Resources/PqrsfSubmissions/Tables/PqrsfSubmissionsTable.php` and `Schemas/*`.
- Dynamic form definitions come from `app/Services/FormFieldService.php` and `form_fields`. Supported types: `text`, `email`, `tel`, `textarea`, `select`, `rating`, `boolean`, `checkbox_list`.
- Submission payloads live in `pqrsf_submissions.field_values` JSON. Access values as `$record->field_values['nombre_completo']`, not as columns.

## PQRSF Workflow

- Status flow is `pending` -> `validated` -> `sent`.
- Public submissions always start `pending`.
- In the admin table, `Cambiar opción` is only available while `pending`; it updates `field_values['opcion_a_calificar']` and writes `submission_logs.action = option_changed`.
- `Enviar a destinatarios` is only visible for `validated`; it emails active `sede_recipients` for the submission's `sede` and sets status to `sent` only if all sends succeed.
- Imported sedes may have no recipients. If email appears broken, first check active recipients for that `sede`.

## Email And PDF

- Mailable: `app/Mail/PqrsfSubmissionMail.php`.
- Email view: `resources/views/emails/pqrsf-submission.blade.php`.
- Shared email/PDF body: `resources/views/emails/partials/pqrsf-summary.blade.php`.
- Signed public PDF route: `pqrsf.submissions.pdf` at `/pqrsf-submissions/{submission}/pdf`, implemented by `app/Http/Controllers/PqrsfSubmissionPdfController.php`.
- The email must not link to `/admin` and must not show `autorizacion_datos`; it links to the signed PDF instead.
- SMTP is read from `src/.env`; never commit or repeat mail credentials. Use `php artisan config:clear` after edits.

## CSV Import

- Import command: `docker compose exec app php artisan pqrsf:import-csv /tmp/PQRSF_2026.csv --dry-run` first, then without `--dry-run`.
- The CSV path is inside the `app` container; use `docker compose cp "C:\path\file.csv" app:/tmp/file.csv` for host files.
- The importer handles Microsoft Forms CSV quirks, split physical rows, semicolons/commas in free text, creates missing sedes, and deduplicates by `field_values['csv_id']`.

## Tests And Gotchas

- PHPUnit uses in-memory SQLite and `MAIL_MAILER=array` from `phpunit.xml`; it does not exercise real SMTP.
- Report PDF tests that need MySQL JSON functions are skipped when the test DB is SQLite.
- DomPDF views should use local/inline styles and `isRemoteEnabled=false` unless deliberately changed.
- Do not trust `src/README.md` for app behavior; it is the stock Laravel README. Prefer config, routes, services, resources and tests.

## Seed Data

- Default admin: `admin@pqrsf.com` / `admin123`.
- Seeders create default form fields, 3 demo sedes, 25 sample submissions, and 2 recipients per demo sede.
