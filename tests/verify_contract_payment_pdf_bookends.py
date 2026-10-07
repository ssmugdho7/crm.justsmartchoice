"""Verify Contract/Payment bookends with the actual native and Custom PDF body views.
Usage: python tests/verify_contract_payment_pdf_bookends.py /absolute/temporary/output-directory
Fixtures use synthetic records, signature artwork and isolated upload folders, never a database.
"""
from pathlib import Path
import os
import shutil
import subprocess
import sys
import tempfile
from pypdf import PdfReader

root = Path(__file__).resolve().parents[1]
output = Path(sys.argv[1]).resolve()
output.mkdir(parents=True, exist_ok=True)
count = 0
with tempfile.TemporaryDirectory(prefix="sc-contract-payment-assets-") as temporary:
    assets = Path(temporary)
    bookends = assets / "modules/custom_pdf/assets/bookends"
    shutil.copytree(root / "modules/custom_pdf/assets/bookends", bookends)
    for kind in ["contract", "payment"]:
        uploads = assets / f"uploads/custom_pdf/{kind}"
        uploads.mkdir(parents=True)
        shutil.copyfile(bookends / f"{kind}-cover.png", uploads / "cover_page.png")
        shutil.copyfile(bookends / "closing-page.png", uploads / "closing_page.png")
    missing_assets = assets / "no-artwork"
    missing_assets.mkdir()
    for engine in ["native", "custom"]:
        for kind in ["contract", "payment"]:
            cases = ["empty", "long", "configured", "missing", "image", "cover-only", "closing-only", "bookend-long", "foreign", "legacy", "missing-cover", "fallback"]
            cases += ["signed"] if kind == "contract" else ["paid", "cancelled"]
            if engine == "native":
                cases += ["inactive"]
            for case in cases:
                path = output / f"{engine}-{kind}-{case}.pdf"
                env = dict(os.environ, SC_SALES_PDF_QA_ASSETS=str(missing_assets if case == "fallback" else assets))
                run = subprocess.run(["php", str(root / "tests/sales_pdf_bookends_regression.php"), engine, kind, case, str(path)], cwd=root, env=env, capture_output=True, text=True)
                if run.returncode or "TCPDF ERROR" in run.stdout + run.stderr or "PASS " not in run.stdout:
                    raise RuntimeError(run.stdout + run.stderr)
                reader = PdfReader(path)
                pages = [page.extract_text() or "" for page in reader.pages]
                text = "\n".join(pages)
                body = "\n".join(pages[1:-1])
                assert kind.capitalize() in pages[0] or len(reader.pages[0].images), (path, "Cover missing")
                assert "Thank you." in pages[-1] or "Additional customer guidance." in pages[-1] or len(reader.pages[-1].images), (path, "Closing not last")
                assert text.count("Signature record preserved.") == 1 and "Signature record preserved." in body, (path, "Signature lifecycle changed")
                assert text.count("Hook appendix") == 1 and "Hook appendix" in body, (path, "Close hook order changed")
                assert "{document_number}" not in text and "{customer_name}" not in text, (path, "Unresolved merge fields")
                assert "$9,650.00" in body, (path, "Amount lost")
                for number, page in enumerate(reader.pages):
                    assert pages[number].strip() or len(page.images), (path, number + 1, "Blank page")
                if kind == "contract":
                    assert "Contract scope" in body and "payment schedule remains unchanged" in body, (path, "Contract terms lost")
                    if case == "long":
                        assert len(reader.pages) > 4, (path, "Long contract truncated")
                    if case == "signed":
                        assert "Sample Customer" in body and "192.0.2.1" in body, (path, "Acceptance details lost")
                        assert sum(len(page.images) for page in reader.pages[1:-1]) >= 1, (path, "Signature image missing")
                else:
                    assert "$12,000.00" in body and "INVOICE-TEST-001" in body and "TXN-TEST-42" in body and "ACH" in body, (path, "Receipt details lost")
                    assert ("$2,350.00" in body) == (case not in ["paid", "cancelled"]), (path, "Amount-due rules changed")
                    if engine == "native":
                        assert "Related customer guide" in body, (path, "Customer links lost")
                if case == "fallback":
                    assert "Sample Customer" in pages[0] and "2026-10-07" in pages[0], (path, "Fallback metadata wrong")
                    assert ("Contract #12" if kind == "contract" else "Payment #42") in pages[0], (path, "Wrong record number")
                if case in ["configured", "inactive"]:
                    assert ("INVOICE-TEST-001" if kind == "payment" else "CONTRACT-TEST-001") in pages[0], (path, "Wrong configured merge number")
                count += 1
print(f"PASS: {count} Contract/Payment PDF renders; bookends, content, signatures, receipt amounts, merge IDs and close hooks preserved")
