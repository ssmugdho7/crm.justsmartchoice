# Smart Choice Stripe Payments 1.1.6

- Protects publishable, secret, and webhook credentials from CRM money formatting.
- Preserves existing encrypted credentials when Settings are saved with password fields blank.
- Uses Stripe-hosted Checkout Session redirect instead of the embedded client-side checkout page.
- Preserves iDEAL/card/payment-method configuration, fees, webhooks, subscriptions, and payment history.
- Activation no longer overwrites existing module settings.
- Adds migration 116.
