# Local changes from production

This branch keeps `main` at the production snapshot and adds the local-only changes needed to run the CRM at `http://127.0.0.1:8000/`.

## `.local-router.php`

- Added a PHP built-in server router.
- Static files that exist on disk are returned directly by PHP's development server.
- All other requests are passed to `index.php`.
- This fixes local CSS, JavaScript, fonts, and images being routed through CodeIgniter and redirected to login.

Run locally with:

```sh
CI_ENV=development php -S 127.0.0.1:8000 .local-router.php
```

## `application/helpers/misc_helper.php`

- Added `is_local_recaptcha_bypass_enabled()`.
- Updated `show_recaptcha()` to return `false` when the app is accessed from `localhost`, `127.0.0.1`, or `::1`.
- This hides the reCAPTCHA widget and skips reCAPTCHA validation for local development.
- Production domains still use the normal reCAPTCHA settings.

## `application/config/app-config.php`

- This file is ignored by git and was not committed.
- For local development, it was adjusted locally to use:
  - `APP_BASE_URL`: `http://127.0.0.1:8000/`
  - local database: `crm_justsmartchoice`
  - local MySQL user: `root`
- Keep production credentials out of commits.
