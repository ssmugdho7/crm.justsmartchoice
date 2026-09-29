# Solar Pro 1.0.3 Upgrade

1. Back up the CRM database and `modules/solar_pro/`.
2. Replace the existing Solar Pro module files with this package.
3. Open Solar Pro from the main CRM menu. The module applies only its own pending migration and then loads the dashboard.
4. Open Setup → Settings → Solar Pro Settings and verify production, panel, API, pricing, financial, and portal values.
5. Confirm existing Google/Enphase credentials remain unchanged when secret fields are left blank.
6. Verify Staff Roles contains Solar Pro permissions: View Own, View Global, Create, Edit, Delete, plus the preserved specialized permissions.

Rollback: restore the backed-up module folder and database. Migration 103 is non-destructive and does not delete existing module data.
