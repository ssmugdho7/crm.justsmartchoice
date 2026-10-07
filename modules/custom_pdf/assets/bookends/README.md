# Smart Choice sales-document artwork

Branded defaults used when the corresponding uploaded cover or closing artwork is absent. Existing uploaded artwork takes precedence. These files are static company presentation assets, with no customer financial records or credentials.

- `proposal-cover.png` and `closing-page.png`: existing Smart Choice company artwork, retained unchanged.
- `estimate-cover.png`, `invoice-cover.png`, `contract-cover.png` and `payment-cover.png`: variants made with the built-in image generation tool from the existing proposal cover. The exact edit request was to replace the large PROPOSAL title and the word on the angled paper with ESTIMATE, INVOICE, CONTRACT or PAYMENT, while preserving the logo, photographs, stripes, contact details, prepared-by line, quality text and stars.

Customer upload directories and installation configuration stay outside Git. The source proposal upload is never overwritten.
