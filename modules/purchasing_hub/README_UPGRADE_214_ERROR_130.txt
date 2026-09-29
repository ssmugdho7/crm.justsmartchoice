Purchasing Hub v2.1.4 - Error 130 Upgrade Rescue

What was fixed:
- Perfex/CodeIgniter migration gaps were causing upgrade error 130.
- The module now includes continuous migration files from 101 through 214.
- Missing versions are safe no-op migrations. They do not delete or alter data.
- Migration 214 reruns the safe installer and updates the module version marker.

Upload path:
public_html/crm.justsmartchoice/modules/purchasing_hub/

Install/upgrade:
1. Backup database and the old purchasing_hub folder.
2. Upload this purchasing_hub folder over the existing folder.
3. Go to Setup > Modules and run Upgrade.
4. If Perfex still blocks because the old database record is corrupted, run SQL_REPAIR_214_ERROR_130.sql, then reload Setup > Modules.

Do not rename the folder. The folder must stay: purchasing_hub
