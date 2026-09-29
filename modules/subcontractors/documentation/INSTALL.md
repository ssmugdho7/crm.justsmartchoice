1. Back up the database and `modules/`, `uploads/`, and `application/` folders.
2. Confirm both deleted duplicate module folders are absent.
3. Upload the included `subcontractors` folder directly into `modules/`.
4. Activate **Subcontractors** in Setup > Modules.
5. Open Setup > Roles and assign: View Own, View (Global), Create, Edit, Delete, View All Templates.
6. Clear Perfex cache and PHP OPcache.
7. Verify subcontractor list, templates, contract creation, public link, initials, signature, PDF, attachments, and project links.

Rollback: deactivate the module and restore the backed-up files/database. Do not uninstall with deletion enabled.
