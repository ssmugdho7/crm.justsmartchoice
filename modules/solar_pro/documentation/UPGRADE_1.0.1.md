# Superseded by v1.0.2

This historical note is retained for upgrade history. Use the v1.0.2 instructions in README.md.

# Solar Pro 1.0.1 Upgrade

1. Back up the CRM database and files.
2. Upload `solar_pro_1.0.1.zip` through Setup > Modules > Upload Module.
3. Allow the module files to replace the existing `modules/solar_pro` files.
4. Perfex will detect the version change from 1.0.0 to 1.0.1. Click **Upgrade Database** when prompted.
5. Confirm **Solar Pro** appears in the admin sidebar.
6. Open Solar Pro > Dashboard.
7. Test the public calculator at `/solar_pro/estimate`.
8. Test the direct fallback path at `/solar_pro/solar_portal/estimate`.
9. Open Setup > Settings > Solar Pro and verify existing API keys/settings are still present.

## Rollback

Restore the database/files backup taken before the upgrade. Do not run uninstall to roll back because Solar Pro uninstall is intentionally data-preserving and is not a version downgrade mechanism.
