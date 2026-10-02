# Deployment file audit — October 3, 2026

## Files that should remain outside Git

- `application/config/app-config.php` and `database.php`: installation-specific settings and credentials. Preserve and back up privately on each server.
- Customer `uploads/`, logs, cache, `temp/`, SQL exports, ZIP backups, `error_log`, and `.user.ini`: private data or runtime/server files. A Git pull must preserve them; they must not be force-added.
- Module vendor dependencies and `node_modules`: use module packages or locked dependency installation. Do not indiscriminately commit dependency directories.

## Assets needing a later deployment workflow

The unanchored `uploads/` rule also ignores `modules/products/uploads/`, including public service-gallery images. Development currently has 687 product assets (513 in the service gallery), three training-manual upload files, and nine company assets outside Git. Existing production assets are preserved during this rollout.

Public marketing images should later move to a tracked static-assets directory, or receive a narrowly scoped whitelist and explicit asset-sync process. Customer attachments, generated PDFs, profile photos, and configuration must remain private. A fresh clone alone does not reproduce these uploaded assets.

## Git packaging issue separate from ignored files

Three paths are Git links without a `.gitmodules` mapping: `modules/trade_job_management`, `modules/video_library ` (trailing space), and the Telegram API dependency inside `modules/telegram_chat_off/vendor/telegram-bot/api`. Existing server directories are preserved. A fresh clone needs a deliberate packaging/submodule repair before it can reproduce these modules.

## Production rollout

Production initially had an unborn Git branch with all application files untracked. The release preserves 56 differing source files in commit `d9d4ccd4`, then merges the tested portal release. The existing meeting-date helper is included because production code already requires it. Portal asset loading, layout, dashboard, and proposal templates use the tested implementation.

Before establishing the checkout, create and verify a private full-site archive and a transaction-consistent database dump. Bootstrap the Git index against the preservation commit without overwriting files, restore only `.gitignore`, and inspect the tracked diff. Move any existing files that collide with newly tracked paths into the private backup; stop if their content differs from the release. Run `git pull --ff-only origin main`, then verify asset delivery, syntax, customer permissions/form payloads, and login redirects.

Rollback: restore the previous tracked source from the preservation commit with the saved pre-pull commit and collision-file backup. Restore the private site archive if a complete checkout restoration is required. The database is unchanged by this presentation rollout; retain its backup for recovery, not routine rollback.

No ignored files are added or ignore rules changed in this release.
