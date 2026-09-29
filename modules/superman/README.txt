Superman PSP 8.5 - Migration Chain Repair

Install path:
public_html/crm.justsmartchoice.com/modules/superman/

This repair adds safe migration bridge files from 100 through 860 so Perfex will not fail on Migration_Version_120 or any nearby sequence gap.

Important upload instruction:
Delete the old modules/superman/migrations folder before uploading this package, or overwrite all files completely. Old broken migration files left on the server can keep causing the same error.

No data is dropped by down() methods. They return true safely.
