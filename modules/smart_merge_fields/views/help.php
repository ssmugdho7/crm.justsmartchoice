<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="panel_s smf-panel">
            <div class="panel-body">
                <div class="smf-page-head">
                    <div>
                        <h4 class="smf-title">Merge Fields Help Guide</h4>
                        <p class="text-muted smf-muted">Detailed operating guide for Smart Choice Merge Fields Automation.</p>
                    </div>
                    <a href="<?php echo admin_url('smart_merge_fields/scan'); ?>" class="btn btn-info smf-btn"><i class="fa fa-refresh"></i> Refresh</a>
                </div>
                <?php $this->load->view('smart_merge_fields/_nav'); ?>

                <div class="alert alert-info smf-help-alert">
                    <strong>Main purpose:</strong> this module helps staff connect related CRM fields so information can move from one area of Perfex CRM to another without typing the same data repeatedly. A common workflow is Lead → Customer → Project → Contract → Invoice.
                </div>

                <div class="smf-guide-hero">
                    <img src="<?php echo module_dir_url('smart_merge_fields', 'assets/img/workflow.svg'); ?>" alt="Merge fields automation workflow" class="img-responsive smf-guide-img">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="smf-help-card smf-help-card-tall">
                            <h4><i class="fa fa-bullseye"></i> What The Module Does</h4>
                            <p>The module scans the CRM database, finds useful fields, and lets you build field relationships. After a relationship is saved, staff can synchronize information between matching records.</p>
                            <p><strong>Example:</strong> when a lead has an email address, that same value can be mapped into the customer profile, contract record, invoice contact field, or another compatible field.</p>
                            <p><strong>Business result:</strong> less duplicate typing, fewer spelling mistakes, cleaner customer records, and faster lead-to-project workflows.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="smf-help-card smf-help-card-tall">
                            <h4><i class="fa fa-shield"></i> Safe Operating Rule</h4>
                            <p>Always run <strong>Refresh</strong> and <strong>Health Check</strong> before creating or syncing mappings. This verifies that tables, columns, permissions, and saved mappings are available.</p>
                            <p>The module is designed to avoid destructive changes. It does not drop CRM tables. It creates its own mapping tables and uses controlled synchronization rules.</p>
                            <p>Staff access is controlled by Perfex CRM role permissions: View, Create, Edit, and Delete.</p>
                        </div>
                    </div>
                </div>

                <hr>

                <h4 class="smf-section-title">How The Visual Mapping Screen Works</h4>
                <div class="smf-guide-hero">
                    <img src="<?php echo module_dir_url('smart_merge_fields', 'assets/img/mapping_workspace.svg'); ?>" alt="Visual mapping workspace" class="img-responsive smf-guide-img">
                </div>

                <div class="smf-step-list">
                    <div class="smf-step"><span>1</span><div><strong>Refresh The Database Fields</strong><p>Click Refresh. The system scans CRM tables and records available fields from Leads, Customers, Projects, Contracts, Invoices, Estimates, Proposals, Payments, Credit Notes, and other installed modules.</p></div></div>
                    <div class="smf-step"><span>2</span><div><strong>Choose The Source Field</strong><p>The source is the field that already has the correct information. Example: Lead Email, Lead Phone, Customer Address, Project Name, or Custom Field Value.</p></div></div>
                    <div class="smf-step"><span>3</span><div><strong>Choose The Target Field</strong><p>The target is where the information should be copied. Example: Customer Email, Contract Client Email, Invoice Billing Email, or Project Contact Phone.</p></div></div>
                    <div class="smf-step"><span>4</span><div><strong>Set The Relation Field</strong><p>The relation field tells the system which records belong together. Examples: Lead ID, Client ID, Project ID, Invoice ID, Proposal ID, Estimate ID, or Contract ID.</p></div></div>
                    <div class="smf-step"><span>5</span><div><strong>Save The Mapping</strong><p>After the mapping is saved, the module stores the rule. You can edit, delete, export, or run synchronization from the dashboard.</p></div></div>
                    <div class="smf-step"><span>6</span><div><strong>Sync And Review</strong><p>Run Sync only after reviewing the mapping. The module will report success and show how many records were affected.</p></div></div>
                </div>

                <hr>

                <h4 class="smf-section-title">Practical Examples</h4>
                <div class="table-responsive">
                    <table class="table table-bordered smf-table smf-example-table">
                        <thead>
                            <tr>
                                <th>Scenario</th>
                                <th>Source Field</th>
                                <th>Target Field</th>
                                <th>Relation</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Lead converted to customer</td>
                                <td>Lead Email</td>
                                <td>Customer Email</td>
                                <td>Lead ID or Client ID after conversion</td>
                                <td>The customer profile receives the same email from the original lead.</td>
                            </tr>
                            <tr>
                                <td>Customer contract preparation</td>
                                <td>Customer Address</td>
                                <td>Contract Billing Address</td>
                                <td>Client ID</td>
                                <td>The contract can reuse the customer address without retyping.</td>
                            </tr>
                            <tr>
                                <td>Project document consistency</td>
                                <td>Project Name</td>
                                <td>Contract Subject</td>
                                <td>Project ID</td>
                                <td>The contract and project use matching job names.</td>
                            </tr>
                            <tr>
                                <td>Invoice contact accuracy</td>
                                <td>Customer Phone</td>
                                <td>Invoice Contact Phone</td>
                                <td>Client ID</td>
                                <td>Invoice records keep the same phone number as the customer file.</td>
                            </tr>
                            <tr>
                                <td>Custom lead intake field</td>
                                <td>Lead Custom Field: Service Type</td>
                                <td>Project Description or Contract Notes</td>
                                <td>Lead ID / Client ID / Project ID</td>
                                <td>Information collected during intake can follow the job workflow.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>

                <h4 class="smf-section-title">Health Check Diagram</h4>
                <div class="smf-guide-hero">
                    <img src="<?php echo module_dir_url('smart_merge_fields', 'assets/img/health_check.svg'); ?>" alt="Merge fields health check" class="img-responsive smf-guide-img">
                </div>

                <div class="row">
                    <div class="col-md-4"><div class="smf-help-card"><h4>Refresh</h4><p>Scans CRM tables and updates the available field list. Use it after installing new modules, adding fields, or changing database structure.</p></div></div>
                    <div class="col-md-4"><div class="smf-help-card"><h4>Health Check</h4><p>Checks module tables, mappings, and recent logs. Use it before synchronization and after upgrades.</p></div></div>
                    <div class="col-md-4"><div class="smf-help-card"><h4>Settings</h4><p>Controls allowed tables, auto sync behavior, and staff configuration. Settings must be managed from the Setup menu.</p></div></div>
                </div>

                <hr>

                <h4 class="smf-section-title">Lead To Customer Automation Example</h4>
                <div class="smf-process-box">
                    <ol>
                        <li>A new lead enters the CRM with name, email, phone, address, and service requested.</li>
                        <li>Staff clicks <strong>Convert To Customer</strong> using the normal Perfex workflow.</li>
                        <li>The module mapping keeps the lead email, phone, and address aligned with the customer record.</li>
                        <li>When staff creates a project, contract, estimate, or invoice, mapped fields can be reused consistently.</li>
                        <li>If a custom field is missing from Leads, use Settings → Create Lead Custom Field.</li>
                    </ol>
                </div>

                <h4 class="smf-section-title">Recommended Mapping Strategy</h4>
                <div class="smf-help-grid">
                    <div class="smf-help-card"><h4>Customer Identity</h4><p>Map name, company, email, phone, and address first. These fields affect most CRM documents.</p></div>
                    <div class="smf-help-card"><h4>Project Information</h4><p>Map project name, project address, service type, assigned staff, and project status.</p></div>
                    <div class="smf-help-card"><h4>Sales Documents</h4><p>Map customer, project, billing email, invoice reference, estimate reference, proposal reference, and contract subject.</p></div>
                    <div class="smf-help-card"><h4>Custom Fields</h4><p>Use custom fields for construction-specific intake data such as roof type, trade, permit status, inspection date, or job address.</p></div>
                </div>

                <div class="alert alert-warning smf-help-alert">
                    <strong>Important:</strong> do not sync a mapping until the source, target, and relation field are correct. A wrong relation can copy values into the wrong matching records. Run Health Check first when unsure.
                </div>

                <h4 class="smf-section-title">Button Reference</h4>
                <div class="table-responsive">
                    <table class="table table-striped smf-table">
                        <thead><tr><th>Button</th><th>What It Does</th><th>When To Use It</th></tr></thead>
                        <tbody>
                            <tr><td>Refresh</td><td>Re-scans CRM tables and fields.</td><td>After installing modules, adding custom fields, or changing database structure.</td></tr>
                            <tr><td>Create Mapping</td><td>Creates a new source-to-target field relationship.</td><td>When you want one value to follow another CRM workflow.</td></tr>
                            <tr><td>Sync</td><td>Applies a saved mapping to matching records.</td><td>After verifying the rule is correct.</td></tr>
                            <tr><td>Export</td><td>Downloads saved mappings as CSV.</td><td>For backup, review, or documentation.</td></tr>
                            <tr><td>Health Check</td><td>Reviews module tables, mappings, and logs.</td><td>Before sync and after upgrades.</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
