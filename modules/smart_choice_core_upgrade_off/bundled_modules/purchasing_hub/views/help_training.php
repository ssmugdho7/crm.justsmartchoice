<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body purchasing-help">
  <h3>Purchasing Hub Help Guide</h3>
  <p>Use Purchasing Hub for vendors, materials, purchase orders, vendor quotes, accounts payable, contracts, and purchasing reports.</p>
  <h4>Important Design Rule</h4><p>This module uses its own Purchasing Hub item and vendor tables, so old automobile item groups from other modules will not appear here.</p>
  <h4>Recommended Workflow</h4>
  <ol><li>Create vendors.</li><li>Create items/materials.</li><li>Create vendor quotes.</li><li>Create purchase orders.</li><li>Create accounts payable bills when vendor invoices arrive.</li><li>Use reports to review material costs and payments.</li></ol>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
