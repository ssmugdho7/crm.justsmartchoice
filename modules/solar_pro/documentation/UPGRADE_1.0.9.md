# Solar Pro 1.0.9 Upgrade

## Scope
- Premium proposal redesign and high-resolution proposal imagery.
- Detailed 25-year savings schedule with annual utility escalation assumption.
- Enphase IQ8 and IQ Gateway customer education and warranty summaries.
- Rebuilt installation-flow visual.
- Proposal acceptance form labels, U.S. phone normalization, email validation, and spacing.
- Email modal viewport repair.
- Solar Pro Settings tab navigation repair and direct public-portal link.
- Solar Pro module Settings route now keeps the standard CRM admin header/quick links while the native Settings registration remains available.

## Migration
Run module migration `109_version_109.php`. It is non-destructive and only seeds missing Solar Pro assumptions and refreshes packaged proposal assets.

## Backup
Back up the database, `modules/solar_pro/`, and `uploads/solar_pro/` before upgrading.
