# Solar Pro 1.0.4 Upgrade

1. Back up the CRM database and the complete `modules/solar_pro/` directory.
2. Replace the existing Solar Pro module directory with this package.
3. Open Setup → Modules and run the Solar Pro module upgrade if Perfex offers it.
4. Open Solar Pro from the main menu. It must load the Solar Pro dashboard and must not show the CRM core database-upgrade screen.
5. Open Setup → Settings → Solar Pro Settings and verify the settings form renders in the right-side Settings panel.
6. Verify Dashboard, Analyses, Utilities, Equipment, Contracts, and Settings.

Migration 104 is module-only and non-destructive. It does not modify the CRM core migration table or core migration configuration.
