# HTTP 500 Migration Repair

Migrations 101 through 201 are compatibility bridge migrations and intentionally perform no database work.
Migration 202 executes the idempotent installer once. This prevents shared-hosting timeouts caused by repeatedly running the installer in every migration.
