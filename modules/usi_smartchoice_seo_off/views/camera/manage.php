<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="sc-toolbar"><h4><i class="fa fa-camera"></i> <?php echo html_escape($title); ?></h4><div class="sc-toolbar-actions">
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_estimates'); ?>">Estimate Drafts</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/sample_header/ai_estimates'); ?>">Sample Header</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/export/ai_estimates'); ?>">Export</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/camera_intake'); ?>">Reload</a>
</div></div>
<div class="row">
  <div class="col-md-7">
    <?php echo form_open_multipart(admin_url('usi_smartchoice_seo/save_camera_intake')); ?>
    <div class="sc-card">
      <h4>Camera / Photo Estimate Intake</h4>
      <p class="text-muted">Upload existing photos or take new photos from mobile. The system checks existing CRM leads/customers by phone or email before creating new records.</p>
      <div class="form-group"><label>Estimate Title</label><input type="text" name="title" class="form-control" value="AI Jobsite Estimate"></div>
      <div class="row">
        <div class="col-md-4"><div class="form-group"><label>Customer Name</label><input type="text" name="customer_name" class="form-control" placeholder="Customer full name"></div></div>
        <div class="col-md-4"><div class="form-group"><label>Phone</label><input type="text" name="customer_phone" class="form-control" placeholder="Phone number"></div></div>
        <div class="col-md-4"><div class="form-group"><label>Email</label><input type="email" name="customer_email" class="form-control" placeholder="Email address"></div></div>
      </div>
      <div class="row">
        <div class="col-md-4"><div class="form-group"><label>Existing Customer</label><select name="customer_id" class="form-control"><option value="0">Auto Check / Create Customer</option><?php foreach (($recent_customers ?? []) as $customer) { ?><option value="<?php echo (int)$customer['userid']; ?>">#<?php echo (int)$customer['userid']; ?> - <?php echo html_escape($customer['company'] ?? 'Customer'); ?></option><?php } ?></select></div></div>
        <div class="col-md-4"><div class="form-group"><label>Existing Lead</label><select name="lead_id" class="form-control"><option value="0">Auto Check / Create Lead</option><?php foreach (($recent_leads ?? []) as $lead) { ?><option value="<?php echo (int)$lead['id']; ?>">#<?php echo (int)$lead['id']; ?> - <?php echo html_escape($lead['name'] ?? 'Lead'); ?></option><?php } ?></select></div></div>
        <div class="col-md-4"><div class="form-group"><label>Project ID</label><input type="number" name="project_id" class="form-control" value="0"></div></div>
      </div>
      <div class="row">
        <div class="col-md-8"><div class="form-group"><label>Location / Job Address</label><input type="text" name="location" id="scJobLocation" class="form-control sc-google-address" autocomplete="street-address" placeholder="Start with street number for Google address suggestions"></div></div>
        <div class="col-md-4"><div class="form-group"><label>Serial / Permit / Key ID</label><input type="text" name="serial_reference" class="form-control" placeholder="Serial, permit, key, or internal ID"></div></div>
      </div>
      <div class="form-group"><label>Duplicate Handling</label><select name="duplicate_action" class="form-control"><option value="create_new">Create a new estimate for existing customer/lead</option><option value="update_existing">Use existing CRM record and flag for update review</option></select></div>
      <div class="form-group sc-photo-upload-group"><label>Upload Existing Photos</label><input type="file" name="photos[]" id="scCameraFile" class="form-control" accept="image/*" multiple><small class="text-muted">Desktop: select two or three photos at once. Mobile: choose Photo Library and select multiple photos when your phone browser supports it.</small></div>
      <div class="form-group sc-photo-upload-group sc-mobile-camera-field"><label>Take New Photos From Phone</label><input type="file" name="photos[]" id="scCameraCaptureFile" class="form-control" accept="image/*" capture="environment" multiple><small class="text-muted">Mobile: use this option to open the phone camera. Save the picture, then add more photos if needed.</small></div>
      <div class="form-group"><label>Photo Type</label><select name="photo_type" class="form-control"><option value="jobsite">Jobsite</option><option value="damage">Damage</option><option value="measurement">Measurement</option><option value="material">Material</option><option value="room">Room</option><option value="roof">Roof</option><option value="window">Window</option><option value="door">Door</option><option value="electrical">Electrical</option><option value="plumbing">Plumbing</option><option value="hvac">HVAC</option><option value="drywall">Drywall</option><option value="flooring">Flooring</option><option value="cabinet">Cabinet</option></select></div>
      <div class="form-group"><label>Scope Summary</label><textarea name="scope_summary" class="form-control" rows="4" placeholder="Describe what needs to be estimated."></textarea></div>
      <div class="form-group"><label>Measurement Notes</label><textarea name="measurement_notes" class="form-control" rows="3" placeholder="Add sizes, quantities, room names, or measurements."></textarea></div>
      <div class="form-group"><label>AI Photo Caption</label><textarea name="ai_caption" class="form-control" rows="2" placeholder="What the photo shows."></textarea></div>
      <input type="hidden" name="status" value="draft">
      <div class="sc-mobile-button-grid sc-form-actions">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save Camera Intake</button>
        <button type="submit" name="run_estimate" value="1" class="btn btn-warning btn-sm"><i class="fa fa-calculator"></i> Get an Estimate</button>
      </div>
    </div>
    <?php echo form_close(); ?>
  </div>
  <div class="col-md-5">
    <div class="sc-card">
      <h4>Live Camera Test</h4>
      <p class="text-muted">This activates the browser camera for preview. Use the photo upload field to save photos into the CRM.</p>
      <video id="scCameraPreview" class="sc-camera-preview" autoplay playsinline muted></video>
      <div class="sc-mobile-button-grid mtop10"><button type="button" class="btn btn-default btn-sm" id="scCameraStart"><i class="fa fa-video-camera"></i> Activate Camera</button><button type="button" class="btn btn-default btn-sm" id="scCameraStop"><i class="fa fa-stop"></i> Stop Camera</button></div>
      <div id="scCameraStatus" class="sc-result-box mtop10">Camera Off</div>
    </div>
    <div class="alert alert-info mtop15">CRM duplicate rule: phone/email are checked before creating new leads/customers. Existing records are linked to the AI estimate instead of duplicating the contact.</div>
  </div>
</div>
</div></div></div></div><?php init_tail(); ?>
