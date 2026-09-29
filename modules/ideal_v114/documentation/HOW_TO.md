# Smart Choice Stripe Payments

1. Configure the Stripe publishable key and secret key under Setup → Settings → Payment Gateways.
2. Activate the gateway after both keys are saved.
3. Open Setup → Stripe Payments and run Test Connection.
4. Create or verify the webhook endpoint. The endpoint must receive and verify Stripe signatures.
5. Select supported payment methods. iDEAL is available only for EUR transactions.
6. Use a recurring Stripe Price ID (`price_...`) to create subscription checkout links.
7. Processing fees are disabled by default. Confirm legal and card-network requirements before enabling any customer-facing fee.
