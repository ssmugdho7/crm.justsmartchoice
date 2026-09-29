# Solar Pro 1.0.2 Installation and Verification

Always back up the database and full CRM file system before deployment. Copy `solar_pro` into the CRM `modules` directory, activate it from Setup → Modules, then configure Setup → Settings → Solar Pro Settings. Add current utility tariffs before relying on customer savings calculations. Do not remove the migration file after installation.

The public calculator route is `/solar_pro/estimate`. A saved analysis receives a cryptographically random public token. CRM lead creation is enabled by default when an email is supplied and can be disabled in Solar Pro settings.

For rollback, deactivate the module and restore the backed-up files/database if required. Solar Pro's uninstall routine intentionally does not delete its database tables or stored settings.

Solar Pro v1.0.4 performs its own module migration when a Solar Pro admin page is opened. It must never require a CRM core database upgrade merely to open the module.
