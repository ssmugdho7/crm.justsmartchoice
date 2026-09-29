<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="sc-toolbar"><h4><i class="fa fa-camera"></i> <?php echo html_escape($title); ?></h4><div class="sc-toolbar-actions">
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_estimates'); ?>">Estimate Drafts</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/sample_header/ai_estimates'); ?>">Sample Header</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/export/ai_estimates'); ?>">Export</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/camera_intake'); ?>">Reload</a>
</div></div>
<div class="row">
  <div class="col-md-6">
    <?php echo form_open_multipart(admin_url('usi_smartchoice_seo/save_camera_intake')); ?>
    <div class="sc-card">
      <h4>Camera / Photo Estimate Intake</h4>
      <p class="text-muted">Use Save Camera Intake to store the photo and notes. Use Get an Estimate to calculate and save an estimate draft immediately.</p>
      <div class="form-group"><label>Estimate Title</label><input type="text" name="title" class="form-control" value="AI Jobsite Estimate"></div>
      <div class="row"><div class="col-md-4"><div class="form-group"><label>Customer ID</label><input type="number" name="customer_id" class="form-control" value="0"></div></div><div class="col-md-4"><div class="form-group"><label>Lead ID</label><input type="number" name="lead_id" class="form-control" value="0"></div></div><div class="col-md-4"><div class="form-group"><label>Project ID</label><input type="number" name="project_id" class="form-control" value="0"></div></div></div>
      <div class="form-group"><label>Location</label><input type="text" name="location" class="form-control" placeholder="Job address, city, or room"></div>
      <div class="form-group"><label>Take or Upload Photo</label><input type="file" name="photo" id="scCameraFile" class="form-control" accept="image/*" capture="environment"></div>
      <div class="form-group"><label>Photo Type</label><select name="photo_type" class="form-control"><option value="jobsite">Jobsite</option><option value="damage">Damage</option><option value="measurement">Measurement</option><option value="material">Material</option><option value="room">Room</option><option value="roof">Roof</option><option value="window">Window</option><option value="door">Door</option><option value="electrical">Electrical</option><option value="plumbing">Plumbing</option><option value="hvac">HVAC</option><option value="drywall">Drywall</option><option value="flooring">Flooring</option><option value="cabinet">Cabinet</option></select></div>
      <div class="form-group"><label>Scope Summary</label><textarea name="scope_summary" class="form-control" rows="4" placeholder="Describe what needs to be estimated. Example: bathroom tile, 80 square feet, demo old tile and install new tile."></textarea></div>
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
  <div class="col-md-6">
    <div class="sc-card">
      <h4>Live Camera Test</h4>
      <p class="text-muted">This test lets the browser activate the camera. Use the file field on the left to save the photo into the CRM.</p>
      <video id="scCameraPreview" class="sc-camera-preview" autoplay playsinline muted></video>
      <div class="sc-mobile-button-grid mtop10"><button type="button" class="btn btn-default btn-sm" id="scCameraStart"><i class="fa fa-video-camera"></i> Activate Camera</button><button type="button" class="btn btn-default btn-sm" id="scCameraStop"><i class="fa fa-stop"></i> Stop Camera</button></div>
      <div id="scCameraStatus" class="sc-result-box mtop10">Camera Off</div>
    </div>
    <div class="alert alert-info mtop15">Phone workflow: open this page on the phone, press the photo field, take the picture, add notes, then press Get an Estimate.</div>
  </div>
</div>
</div></div></div></div><?php init_tail(); ?>
