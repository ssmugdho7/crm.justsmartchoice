Stripe Hub v1.5.0
Smart Choice Contractors USA / Harold Cabrera

Compatibility target:
- User-supplied Smart Choice CRM core reviewed: 4.1.7 custom build
- Module APIs kept compatible with Perfex 3.x style module system used by the supplied Appointly 2.2.1 module

Install:
1. Upload the stripe_hub folder into /modules/ or install the ZIP in Setup > Modules.
2. Activate Stripe Hub.
3. Assign Stripe Hub permissions to Roles/Staff: View Own, View (Global), Create, Edit, Delete.
4. Open Stripe Hub > Settings.
5. Recommended: keep "Use the CRM built-in Stripe gateway credentials" enabled.
6. Optional webhook logging endpoint is shown in Settings. If you create a Stripe webhook for it, paste its signing secret into Stripe Hub Settings.

Important:
- Stripe Hub does not replace the CRM's existing Stripe payment gateway.
- Invoice Checkout sessions use the same Stripe account and include CRM invoice metadata so the existing CRM Stripe webhook can record invoice payments when that webhook is configured.
- View Own is intentionally limited to local Stripe Hub actions created by the logged-in staff member. Full remote Stripe account lists require View (Global).
- Delete permission controls refunds and log deletion because refunds are destructive financial actions.
- All module UI text is stored in language/english or language/spanish.
