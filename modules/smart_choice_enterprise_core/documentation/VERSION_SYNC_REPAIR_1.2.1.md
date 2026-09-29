# Version Synchronization Repair 1.2.1

This maintenance release repairs installations where the Phase 3 files and database tables were present but the Perfex Modules page continued to display an earlier installed version.

## Upgrade process

1. Merge this module folder over the existing `smart_choice_enterprise_core` folder.
2. Open **Setup → Modules**.
3. Run the standard database upgrade for **Smart Choice Enterprise Core**.
4. Reload the Modules page.
5. Confirm that both the file version and installed version display `1.2.1`.

The migration does not modify customer, lead, project, invoice, staff, or other operational records.
