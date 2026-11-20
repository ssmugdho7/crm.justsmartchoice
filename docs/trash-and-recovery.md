# Trash and recovery

The CRM preserves deleted business rows with MySQL BEFORE DELETE triggers. The
archive uses InnoDB, participates in the original transaction, and includes
foreign-key CASCADE and SET NULL descendants. Rows contain base64 encoded column
bytes, UTC timestamps, actor, request batch, and reason. Expired session/token
rows and temporary caches are excluded. No automatic purge exists.

Media is copied before unlink, replacement, rename, and stream writes into a
private content-addressed store outside public_html. SHA-256 verification, private
permissions, exclusive restore creation, and per-path locks protect copies. A
failed pre-deletion archive stops that file operation. Native call substitutions
are token-based, preserve methods/strings/comments, and fall back to native PHP
outside CRM bootstrap. Third-party authenticated uploads also receive an incoming
copy before controller handling. Existing uploads receive a baseline snapshot.

Administrator UI: **Setup → Trash & Recovery**, `/admin/recovery`. Files can be
downloaded or restored to an unoccupied original path. Deleted database operations
can be exported for reviewed recovery; financial/linked records are not blindly
reinserted by a browser button. There is no purge action.

Deployment: take a private consistent full database dump and media baseline before
installing triggers or enabling file protection. CLI commands:

```
php scripts/recovery/maintenance.php --initial
php scripts/recovery/manage.php /absolute/crm/path install
php scripts/recovery/manage.php /absolute/crm/path enable
php scripts/recovery/manage.php /absolute/crm/path health
```

Schedule `maintenance.php --daily` hourly; it runs once per day in the 03:00 hour
Asia/Dhaka, checks schema/trigger coverage, makes a verified full compressed database
snapshot, and reconciles media. Logs and status remain private; failures appear in
the admin UI. Copies are retained indefinitely, so monitor disk space and arrange
an independent off-host backup. No local trash design survives loss of the hosting
account, disk, or privileged external deletion of its recovery store.

Application TRUNCATE is converted to trigger-backed DELETE, preserving clear-all
workflows without reusing record IDs. Destructive schema DROP is blocked while
protection is enabled. Planned module uninstall/schema migrations require a fresh
verified backup and reviewed CLI maintenance. New/altered tables receive refreshed
coverage automatically; nightly verification detects external schema changes.

The filesystem migration intentionally excludes vendor libraries, obsolete
PHPExcel source, duplicate inactive framework trees, symlink targets, and remote
object-storage APIs. Baseline/nightly snapshots cover local media, including files
created outside normal wrappers. Direct SSH, remote cloud deletion, and arbitrary
external code are outside synchronous protection. CRM database triggers still
protect ordinary raw SQL DELETE/REPLACE and related cascades.

Checks: `php tests/recovery_regression.php`,
`php tests/recovery_database_regression.php` (isolated local MySQL database),
`php tests/recovery_authorization_regression.php`, plus signature and bulk-sales
regressions. Server probes use isolated temporary tables only, never customer data.
