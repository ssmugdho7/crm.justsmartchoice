# Smart Choice social profiles

Update the company profile destinations in reusable site templates and active CRM email content.

| Profile | Destination |
| --- | --- |
| Facebook | https://www.facebook.com/profile.php?id=61594271759671 |
| Instagram | https://www.instagram.com/smartchoicecontra/ |
| Pinterest | https://www.pinterest.com/haroldcabrerausa/ |
| YouTube | https://www.youtube.com/@SmartChoiceContractorUSA |

The source changes cover the footer references, template JSON, and template seeder in `modules/zillapage_off`. The module stays disabled. Existing CRM content lives in the database, so pulling source alone does not update it.

From the CRM installation directory, inspect the affected row counts:

```sh
php tools/maintenance/update_company_social_links.php
```

Apply once using a new private directory outside the web root:

```sh
php tools/maintenance/update_company_social_links.php --apply --backup-dir=/private/path/social-links-backup
```

The CLI updates only exact former company URLs in active options, email templates (including English and Spanish), and saved Smart Choice links. It preserves old backup options, all other content, and other social networks. It uses the existing private database configuration and prints counts only. No account passwords are needed or stored.

Before writing, it verifies a private JSON recovery copy of the affected content and requires transactional tables. Updates compare the prior content to prevent overwriting concurrent edits. Re-running the inspection should report zero changed rows. Recovery data contains the table, row ID, and original content; restore only those rows after checking for subsequent edits.

No schema migration, activation, permission changes, translation keys, or cache/config changes are required. The script returns HTTP 403 if requested through a browser. Existing email messages already delivered are unaffected; no test email is sent by this upgrade.
