<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content smartchoice-commission">
    <div class="row">
      <div class="col-md-12">
        <div class="sc-commission-hero">
          <div>
            <h2>Quick Assign Commission</h2>
            <p>Select an invoice, choose the salesperson / installer / subcontractor, and assign a simple commission.</p>
          </div>
          <a href="<?php echo admin_url('commission/smartchoice_report'); ?>" class="btn btn-default">View Report</a>
        </div>
      </div>
    </div>

    <?php echo form_open(admin_url('commission/quick_assign')); ?>
    <div class="row">
      <div class="col-md-8">
        <div class="panel_s sc-card">
          <div class="panel-body">
            <h4 class="no-margin">Commission Details</h4>
            <hr>
            <?php echo render_select('invoice_id', $invoices, ['id', 'name'], 'Invoice / Job', '', [], [], '', '', false); ?>

            <div class="row">
              <div class="col-md-6">
                <?php echo render_select('assignee_type', [
                  ['id' => 'staff', 'name' => 'Employee / Sales Rep / Installer'],
                  ['id' => 'client', 'name' => 'Subcontractor / Non-Employee'],
                ], ['id', 'name'], 'Person Type', 'staff', [], [], '', '', false); ?>
              </div>
              <div class="col-md-6">
                <?php echo render_input('role_label', 'Role on Job', '', 'text', ['placeholder' => 'Sales Rep, Installer, Subcontractor, Helper']); ?>
              </div>
            </div>

            <div id="staff-assignee-box">
              <?php echo render_select('assignee_id_staff', $staffs, ['staffid', ['firstname', 'lastname']], 'Employee / Staff', '', [], [], '', '', false); ?>
            </div>
            <div id="client-assignee-box" class="hide">
              <?php echo render_select('assignee_id_client', $clients, ['userid', 'company'], 'Subcontractor / Non-Employee', '', [], [], '', '', false); ?>
            </div>
            <input type="hidden" name="assignee_id" id="smartchoice_assignee_id" value="">

            <div class="row">
              <div class="col-md-6">
                <?php echo render_select('commission_mode', [
                  ['id' => 'percentage', 'name' => 'Percentage of Invoice'],
                  ['id' => 'fixed', 'name' => 'Fixed Amount'],
                ], ['id', 'name'], 'Commission Type', 'percentage', [], [], '', '', false); ?>
              </div>
              <div class="col-md-6">
                <?php echo render_input('commission_rate', 'Commission Percent or Fixed Amount', '', 'number', ['step' => '0.01', 'min' => '0', 'placeholder' => 'Example: 10 or 250']); ?>
              </div>
            </div>

            <?php echo render_textarea('notes', 'Notes / Comments', '', ['rows' => 4, 'placeholder' => 'Example: Commission for sold kitchen remodel, installer payout, helper bonus, etc.']); ?>

            <button type="submit" class="btn btn-primary sc-yellow-btn">Save Commission</button>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="panel_s sc-card sc-help-card">
          <div class="panel-body">
            <h4>How this works</h4>
            <p><strong>Step 1:</strong> Choose the invoice/job.</p>
            <p><strong>Step 2:</strong> Choose whether the person is staff or subcontractor/non-employee.</p>
            <p><strong>Step 3:</strong> Enter percentage or fixed amount.</p>
            <p><strong>Step 4:</strong> Save. The commission appears in reports as owed until paid.</p>
            <hr>
            <a href="<?php echo admin_url('commission/help'); ?>" class="btn btn-default btn-block">Open Help Page</a>
          </div>
        </div>
      </div>
    </div>
    <?php echo form_close(); ?>
  </div>
</div>
<?php init_tail(); ?>
<script>
(function(){
  function syncAssignee(){
    var type = $('select[name="assignee_type"]').val();
    if(type === 'client'){
      $('#staff-assignee-box').addClass('hide');
      $('#client-assignee-box').removeClass('hide');
      $('#smartchoice_assignee_id').val($('select[name="assignee_id_client"]').val());
    } else {
      $('#client-assignee-box').addClass('hide');
      $('#staff-assignee-box').removeClass('hide');
      $('#smartchoice_assignee_id').val($('select[name="assignee_id_staff"]').val());
    }
  }
  $(document).on('change','select[name="assignee_type"],select[name="assignee_id_staff"],select[name="assignee_id_client"]',syncAssignee);
  $(function(){ syncAssignee(); });
})();
</script>
</body>
</html>
