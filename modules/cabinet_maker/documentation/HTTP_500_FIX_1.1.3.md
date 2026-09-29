# Cabinet Maker 1.1.3 HTTP 500 Repair

- Corrected every migration class to extend `App_module_migration`.
- Synchronized `config/cabinet_maker.php` with module version 1.1.3.
- Corrected minimum Perfex compatibility declaration to 3.4.0.
- Kept schema operations non-destructive.
- Activation now installs the complete current schema and catalog.
- Existing options are preserved; missing defaults are added.
