# HTTP 500 Recovery

1. Using File Manager or FTP, rename `modules/cabinet_maker` to `cabinet_maker_disabled` to restore CRM access.
2. Remove the disabled folder after confirming the CRM loads.
3. Upload this corrected package and extract it so the final path is `modules/cabinet_maker/cabinet_maker.php`.
4. In Setup > Modules, activate Cabinet Maker.
5. If activation reports a database error, check the newest file in `application/logs/` and verify the database user has CREATE and ALTER privileges.

Version 1.0.3 removes the duplicate root bootstrap file that could reload the module and cause function-redeclaration fatal errors.
