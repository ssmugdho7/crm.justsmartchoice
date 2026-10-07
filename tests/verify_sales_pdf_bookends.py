"""Render synthetic sales documents and verify real PDF page order with pypdf.
Usage: python tests/verify_sales_pdf_bookends.py /absolute/temporary/output-directory
Optional SC_SALES_PDF_QA_ASSETS supplies local artwork fixtures outside the repository.
"""
import os
from pathlib import Path
import subprocess
import sys
from pypdf import PdfReader

root = Path(__file__).resolve().parents[1]
output = Path(sys.argv[1]).resolve()
output.mkdir(parents=True, exist_ok=True)
cases = ["empty", "long", "configured", "missing", "image", "cover-only", "closing-only", "bookend-long", "foreign", "legacy", "missing-cover", "inactive"]
count = 0
for engine in ["native", "custom", "styleflow"]:
    for kind in ["estimate", "invoice"]:
        for case in cases:
            if engine == "custom" and case == "inactive":
                continue  # An inactive module cannot select its own renderer.
            path = output / f"{engine}-{kind}-{case}.pdf"
            run = subprocess.run(["php", str(root / "tests/sales_pdf_bookends_regression.php"), engine, kind, case, str(path)], cwd=root, capture_output=True, text=True)
            if run.returncode:
                raise RuntimeError(run.stdout + run.stderr)
            reader = PdfReader(path)
            pages = [page.extract_text() or "" for page in reader.pages]
            assert kind.capitalize() in pages[0] or len(reader.pages[0].images), (path, "Cover missing")
            assert "Thank you." in pages[-1] or "Additional customer guidance." in pages[-1] or len(reader.pages[-1].images), (path, "Closing missing or not last")
            assert "Signature record preserved." not in pages[0] + pages[-1], (path, "Signature in bookend")
            assert "Hook appendix" not in pages[0] + pages[-1], (path, "Appendix in bookend")
            text = "\n".join(pages)
            assert text.count("Signature record preserved.") == 1, (path, "Signature duplicated")
            assert text.count("Hook appendix") == 1, (path, "Close hook duplicated")
            assert "$9,650.00" in text and "$630.00" in text and "$1,000.00" in text, (path, "Financial amounts missing")
            assert "{document_number}" not in text, (path, "Unresolved merge field")
            for number, page in enumerate(reader.pages):
                assert pages[number].strip() or len(page.images), (path, number + 1, "Blank page")
            if engine == "native":
                assert "Attachment appendix" in text and "Attachment appendix" not in pages[-1], (path, "Attachment lost or after closing")
            count += 1
print(f"PASS: {count} real PDF renders and page-order, amount, signature, attachment and merge-field checks")
