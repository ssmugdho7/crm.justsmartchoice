<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_210 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'smartsource_subcontractor_templates';
        if (!$CI->db->table_exists($table)) {
            return;
        }

        $common = '<hr><h2>Initials And Signatures</h2><table width="100%" cellpadding="8" cellspacing="0" border="1"><tr><td><strong>Company Initials:</strong> {company_initials}</td><td><strong>Subcontractor Initials:</strong> {subcontractor_initials}</td></tr><tr><td>{company_signature}</td><td>{subcontractor_signature}</td></tr><tr><td colspan="2">{contract_signed_stamp}</td></tr></table>';
        $replacement = '<h1 style="text-align:center;">SMART CHOICE CONTRACTORS USA</h1><h2 style="text-align:center;">MASTER SUBCONTRACTOR AGREEMENT</h2><p>This Master Subcontractor Agreement is entered into between Smart Choice Contractors USA and {subcontractor_name}. This agreement governs subcontractor services, project assignments, scope of work, payment requirements, license requirements, insurance, document control, safety rules, change orders, inspections, lien releases, and compliance with Florida construction requirements.</p><h2>1. Subcontractor Information</h2><p><strong>Subcontractor:</strong> {subcontractor_name}<br><strong>Email:</strong> {subcontractor_email}<br><strong>Phone:</strong> {subcontractor_phone}<br><strong>Project:</strong> {project_name}<br><strong>Contract Subject:</strong> {contract_subject}<br><strong>Contract Value:</strong> ${contract_value}</p><h2>2. Scope Of Work</h2><p>The subcontractor shall provide labor, supervision, tools, equipment, material coordination when assigned, cleanup, project documentation, photos, and all work required by the assigned trade scope, work order, drawings, change orders, project notes, and written instructions issued by Smart Choice Contractors USA.</p><h2>3. License, Insurance, And Compliance</h2><p>The subcontractor must maintain active license status when licensing is required, general liability insurance, workers compensation coverage or exemption documentation when applicable, W-9 records, and certifications required for the assigned trade. The subcontractor must comply with Florida Building Code, local permitting requirements, OSHA safety practices, project inspections, and Smart Choice Contractors USA jobsite standards.</p><h2>4. Payment Terms</h2><p>Payment is subject to completed work, project manager approval, inspection approval when applicable, delivery of required documents, approved invoices, lien releases, photos, and confirmation that the work is complete and acceptable. Smart Choice Contractors USA may withhold payment for incomplete work, failed inspections, missing documentation, damages, cleanup issues, unauthorized changes, or unresolved punch list items.</p><h2>5. Change Orders</h2><p>No change order is payable unless approved in writing by Smart Choice Contractors USA before the additional work is performed. Text message, email, signed change order, or CRM approval may be used as written approval.</p><h2>6. Independent Contractor Status</h2><p>The subcontractor is an independent contractor and is responsible for its own employees, taxes, insurance, tools, licenses, vehicles, payroll, supervision, and legal obligations.</p><h2>7. Signature And Record Stamp</h2><p>By signing or initialing electronically, both parties acknowledge that the electronic signature, initials, date, time, and IP address may be stored as part of the CRM record.</p>' . $common;

        $CI->db->like('content', 'Enter template content here')->update($table, ['content' => $replacement]);
    }
}
