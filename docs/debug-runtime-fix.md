# Debug mode runtime correction

The CRM Utilities notice, status cards and checkbox previously used the saved
`debug_mode_enabled` preference. The live preference was `1` while the entry point
defaulted to production. The switch only matched an older literal ENVIRONMENT
declaration; it could not change the current `$_SERVER['CI_ENV'] ?? 'production'`
declaration. Its controller saved the preference and reported success even on failure.

Status and notices now follow the running request's ENVIRONMENT. A real development
runtime still displays the warning when the banner preference is enabled. No runtime
or database setting is changed merely by displaying a page.

Explicit admin switches support both entry point formats, validate the declaration,
and replace the entry point atomically with its existing permissions. Unknown or
ambiguous declarations and write failures are rejected. Only a successful switch
updates the saved preference. Native Settings uses the same validation and submits
`0` when the debug checkbox is unchecked. Settings edit permission alone does not
grant environment-switching access; the existing administrator requirement remains.

Validation: `php tests/debug_mode_runtime_regression.php production` (also run with
`development` and `testing`). Each run checks 122 assertions against temporary files,
including executable rewritten declarations, stale flags, controller feedback,
idempotency, failure preservation, admin restrictions and native settings behavior.
Validated locally and against Bluehost PHP 8.3.35; all changed PHP files pass lint.
The fixture never boots the CRM or modifies its real entry point or database.

Rollout is a separate commit based on origin/main, excluding pending dev changes.
The production entry point, protected configuration, and previous module files are
backed up under `/home2/scusawco/codex-backups/main-debug-runtime-20261003` before a
fast-forward pull. Rollback can restore the module files from `previous-files.tar.gz`
and revert this single commit in Git. The deployment itself changes no settings,
permissions, database schema, or entry point. Authenticated visual verification
requires a signed-in staff session; the available browser session was signed out.
