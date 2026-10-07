# Sales PDF cover and closing pages

Proposals, estimates and invoices now share `Sales_document_pdf`. Native, Custom PDF and StyleFlow classes all use that lifecycle. Each document starts with a separate cover, renders its existing body and attachments, processes signatures and the `pdf_close` hook once, then ends with a separate closing page. Repeated prepare/close calls do not add duplicate pages.

Existing document-specific artwork and merge-field text in `proposals_pdf_settings`, `estimate_pdf_settings` and `invoice_pdf_settings` remain in use. Missing or unreadable artwork falls back to a designed page labeled for the correct document type. Local legacy cover/end image options remain supported. Bookend assets resolve only to readable raster images in the relevant upload directory, or public uploads/assets for legacy options; remote URL fetching and paths outside those directories are rejected. Presentation pages suppress body headers/footers, including continuations of long configured text.

The production inspection found estimate/invoice settings pointing to `cover_page.png` while those files were absent; their `closing_page.png` files were present. This code does not modify those settings or upload files. A missing cover no longer removes the first presentation page.

Document body views, prices, taxes, discounts, payment modes, signatures, language behavior and authorization/download handlers remain unchanged. No database migration is required. Credit notes, contracts, payments and sample previews keep their existing lifecycle.

## Verification

Use PHP with the installed TCPDF dependency and Python with `pypdf`:

- `python tests/verify_sales_pdf_bookends.py /absolute/temporary/qa-directory` renders and checks 70 estimate/invoice scenarios using real native, Custom PDF and StyleFlow views. Set `SC_SALES_PDF_QA_ASSETS` to an external fixture root containing `uploads/custom_pdf/{estimate,invoice}/cover_page.png` and `closing_page.png` to exercise valid artwork too.
- Run `php tests/proposal_pdf_regression.php PROVIDER SCENARIO /absolute/temporary/output.pdf` for providers `native`, `custom`, `styleflow` and scenarios `short`, `long`, `configured`, `image`.
- Render representative first, body and final pages with Poppler and inspect them before release.

Only source and synthetic test fixtures belong in Git. Customer PDFs and runtime artwork remain outside commits. A production pull is a separate deployment action; roll back application files to the prior revision if needed. There is no schema or customer-data rollback.
