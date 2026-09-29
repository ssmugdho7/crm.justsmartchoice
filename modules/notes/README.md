# Smart Choice Notes 1.1.7

Compatible with Perfex CRM 3.4.x+, PHP 8.5+, and MySQL 5.7+.

## Upgrade
1. Back up the database and `modules/notes/`.
2. Replace the existing `modules/notes/` folder with the folder in this ZIP.
3. Open Setup > Modules and run the Notes upgrade to 1.1.7.
4. Open Setup > Settings > Notes to manage sources and note types.
5. Clear CRM cache and PHP OPcache.

## Data preservation
Migration 116 adds configuration tables and the `note_type` field. Existing notes, relations, attachments, colors, priorities, assignments, and uploads are preserved.

## Rollback
Restore the previous module folder and database backup. Migration down intentionally does not delete data.
