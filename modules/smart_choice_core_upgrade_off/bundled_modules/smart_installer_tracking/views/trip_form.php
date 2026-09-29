<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-installer-tracking"><div class="row"><div class="col-md-9"><div class="panel_s"><div class="panel-body">
<h3 class="sit-page-title"><?php echo html_escape($title); ?></h3>
<p class="text-muted">Create a live tracking trip using existing appointments, projects, clients, and installer staff. Do not type raw IDs manually.</p>
<?php echo form_open(admin_url('smart_installer_tracking/create'), ['id' => 'sit-trip-form']); ?>
<div class="row">
  <div class="col-md-6">
    <label for="staff_id">Installer</label>
    <select name="staff_id" id="staff_id" class="form-control selectpicker" data-live-search="true" required>
      <?php foreach ($staff as $member) { $name = trim(($member['firstname'] ?? '') . ' ' . ($member['lastname'] ?? '')); ?>
        <option value="<?php echo (int) $member['staffid']; ?>" <?php echo (int)$member['staffid'] === get_staff_user_id() ? 'selected' : ''; ?>><?php echo html_escape($name !== '' ? $name : ($member['email'] ?? 'Staff Member')); ?></option>
      <?php } ?>
    </select>
  </div>
  <div class="col-md-6">
    <label for="appointment_id">Appointment</label>
    <select name="appointment_id" id="appointment_id" class="form-control selectpicker" data-live-search="true">
      <option value="">Select Appointment</option>
      <?php foreach ($appointments as $appointment) { ?>
        <option value="<?php echo (int) $appointment['id']; ?>" data-client-id="<?php echo (int)($appointment['client_id'] ?? 0); ?>" data-project-id="<?php echo (int)($appointment['project_id'] ?? 0); ?>"><?php echo html_escape(trim(($appointment['subject'] ?? 'Appointment') . ' - ' . ($appointment['start_date'] ?? ''))); ?></option>
      <?php } ?>
    </select>
    <p class="text-muted small">This list is pulled from the CRM appointments table when that module is installed.</p>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <label for="project_id">Project</label>
    <select name="project_id" id="project_id" class="form-control selectpicker" data-live-search="true">
      <option value="">Select Project</option>
      <?php foreach ($projects as $project) { ?>
        <option value="<?php echo (int) $project['id']; ?>" data-client-id="<?php echo (int)($project['clientid'] ?? 0); ?>"><?php echo html_escape($project['name'] ?? 'Project'); ?></option>
      <?php } ?>
    </select>
  </div>
  <div class="col-md-6">
    <label for="client_id">Customer</label>
    <select name="client_id" id="client_id" class="form-control selectpicker" data-live-search="true">
      <option value="">Select Customer</option>
      <?php foreach ($clients as $client) { $street = $client['billing_street'] ?: $client['address']; $city = $client['billing_city'] ?: $client['city']; $state = $client['billing_state'] ?: $client['state']; $zip = $client['billing_zip'] ?: $client['zip']; $address = trim($street . ', ' . $city . ', ' . $state . ' ' . $zip, ' ,'); ?>
        <option value="<?php echo (int) $client['userid']; ?>" data-name="<?php echo html_escape($client['company'] ?? ''); ?>" data-phone="<?php echo html_escape($client['phonenumber'] ?? ''); ?>" data-address="<?php echo html_escape($address); ?>"><?php echo html_escape($client['company'] ?? 'Customer'); ?></option>
      <?php } ?>
    </select>
  </div>
</div>
<div class="row">
  <div class="col-md-4"><?php echo render_input('client_name','Customer Name'); ?></div>
  <div class="col-md-4"><?php echo render_input('client_phone','Customer Phone'); ?></div>
  <div class="col-md-4"><?php echo render_input('client_email','Customer Email','', 'email'); ?></div>
</div>
<?php echo render_textarea('destination_address','Destination Address'); ?>
<div class="row">
  <div class="col-md-5"><?php echo render_input('destination_lat','Destination Latitude'); ?></div>
  <div class="col-md-5"><?php echo render_input('destination_lng','Destination Longitude'); ?></div>
  <div class="col-md-2"><label>&nbsp;</label><button type="button" class="btn btn-info btn-block" id="sit-geocode-address">Get Coordinates</button></div>
</div>
<div class="sit-map-toolbar">
  <a class="btn btn-default btn-sm" id="sit-open-google-map" target="_blank" rel="noopener">Open Google Map</a>
  <span id="sit-geocode-status" class="text-muted"></span>
</div>
<hr>
<button type="submit" class="btn btn-primary">Save Trip</button>
<a href="<?php echo admin_url('smart_installer_tracking'); ?>" class="btn btn-default">Cancel</a>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
