# API Notes

## Google

Solar Pro 1.0.0 supports Google Geocoding API and Google Solar API Building Insights. Enter keys in Setup → Settings → Solar Pro → Google. The Solar API request uses `buildingInsights:findClosest` and supports BASE, MEDIUM, or HIGH minimum imagery quality. If Google returns no supported building or the key is absent, the calculator continues with the fallback production model.

## Enphase

The Enphase settings retain API key, client ID, client secret, base URL, organization, and model fields. No activation or upgrade process overwrites existing values. Enphase API v4 monitoring synchronization is not enabled in 1.0.0 because OAuth authorization and system-owner consent must be completed before site production data can be read.
