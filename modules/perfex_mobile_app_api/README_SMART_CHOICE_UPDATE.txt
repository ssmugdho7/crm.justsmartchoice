Perfex Mobile App API - Smart Choice Contractors Update
Version: 1.5.1
Author: Smart Choice Contractors
Target: Perfex CRM 3.4.1+, PHP 8.5 forward-compatible

Main changes:
- Removed hardcoded staff ID override that forced API requests to staff ID 1.
- Added active staff validation before login/session use.
- Removed password from session validation response.
- Added health endpoint: /perfex_mobile_app_api/perfex_mobile_app_api/health
- Added optional Auth-Key enforcement setting.
- Added optional API request/response logging setting.
- Updated settings tab registration to support newer Perfex app settings API.
- Improved CSRF exception installer/remover with clear Smart Choice markers.
- Improved api_logs table to LONGTEXT fields and indexed created_at.
- Deactivation now keeps module data; uninstall removes it.
- PHP syntax checked.

Install:
1. Upload the perfex_mobile_app_api folder or ZIP through Perfex modules.
2. Activate the module.
3. Go to Setup > Settings > Mobile App API.
4. Keep Require Auth-Key disabled unless your mobile app sends that header.
5. Use the health endpoint to confirm the module is reachable.


Version 1.5.1 migration fix:
- Added full migration shim path through 1.5.1 so Perfex does not stop with "No migration could be found" at 1.5.0 / 150.
- Re-applies safe options and api_logs table repair during upgrade.
- Upload the ZIP, then click Upgrade Database.
