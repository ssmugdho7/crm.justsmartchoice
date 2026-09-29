# Emergency Recovery 1.2.2

This release removes the direct update of the Perfex module registry from migration 121. Perfex now remains solely responsible for updating `installed_version` after each successful migration.

## Required deployment

Replace the complete module folder contents. Confirm that migrations 121 and 122 match this package. Then run the standard module database upgrade.

No operational CRM tables are altered by this recovery release.
