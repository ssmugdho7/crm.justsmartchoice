# Smart Choice Notes Compliance

- Module folder: `modules/notes/`
- Main file: `notes.php`
- Languages: `language/english/notes_lang.php`, `language/spanish/notes_lang.php`
- Migrations: continuous through `112_version_112.php`
- Permissions: View (Own), View (Global), Create, Edit, Delete
- Data source: native Perfex `tblnotes` table; legacy records are preserved
- Email rule: if email notifications are enabled in a future release, they must use native Perfex CRM Email Templates, recipient language, merge fields, sender, signature, header, and footer. No hard-coded mail bodies are permitted.

- v1.1.3 adds table/folder health diagnostics, imports legacy project notes into native tblnotes without deleting originals, and adds View/Delete actions.
