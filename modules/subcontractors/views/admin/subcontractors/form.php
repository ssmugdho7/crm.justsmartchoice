<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo smartsource_admin_submenu('subcontractors'); ?>

<?php echo form_open_multipart($subcontractor ? admin_url('subcontractors/subcontractor/' . $subcontractor->id) : admin_url('subcontractors/subcontractor')); ?>
<div class="panel_s smartsource-panel"><div class="panel-body">
<h4><?php echo html_escape($title); ?></h4><hr>
<div class="row">
<div class="col-md-6"><?php echo render_input('company', 'subcontractor_company', $subcontractor->company ?? '', 'text', ['required'=>true]); ?></div>
<div class="col-md-3"><?php echo render_input('contact_name', 'subcontractor_contact_name', $subcontractor->contact_name ?? ''); ?></div>
<div class="col-md-3"><div class="checkbox checkbox-primary smartsource-portal-enabled-box"><input type="checkbox" name="portal_enabled" id="portal_enabled" value="1" <?php echo (!$subcontractor || !empty($subcontractor->portal_enabled)) ? 'checked' : ''; ?>><label for="portal_enabled">Subcontractor Portal Enabled</label></div></div>
<div class="col-md-6"><?php echo render_input('email', 'email', $subcontractor->email ?? '', 'email'); ?></div>
<div class="col-md-6"><?php echo render_input('phone', 'phone', $subcontractor->phone ?? ''); ?></div>
<div class="col-md-4"><?php echo render_input('trade', 'subcontractor_trade', $subcontractor->trade ?? ''); ?></div>
<div class="col-md-4"><?php echo render_select('category', $categories, ['name','name'], 'smartsource_category', $subcontractor->category ?? ''); ?></div>
<div class="col-md-4"><?php echo render_select('status', $statuses, ['slug','name'], 'status', $subcontractor->status ?? 'active'); ?></div>
<div class="col-md-4"><?php echo render_input('license_number', 'subcontractor_license_number', $subcontractor->license_number ?? ''); ?></div>
<div class="col-md-4"><?php echo render_date_input('insurance_expiration', 'subcontractor_insurance_expiration', $subcontractor->insurance_expiration ?? ''); ?></div>
<div class="col-md-4"><?php echo render_input('assigned', 'Assigned Staff ID', $subcontractor->assigned ?? get_staff_user_id(), 'number'); ?></div>
<div class="col-md-4"><?php echo render_select('staff_id', $staff_members, ['staffid', ['firstname','lastname']], 'Linked Staff Account', $subcontractor->staff_id ?? ''); ?></div>
<div class="col-md-6"><?php echo render_input('dbpr_link', 'smartsource_dbpr_link', $subcontractor->dbpr_link ?? '', 'text'); ?></div>
<div class="col-md-6"><?php echo render_input('county_license_link', 'smartsource_county_license_link', $subcontractor->county_license_link ?? '', 'text'); ?></div>
<div class="col-md-6"><?php echo render_input('profile_image', 'Profile Picture URL', $subcontractor->profile_image ?? '', 'text'); ?></div>
<div class="col-md-6"><div class="form-group"><label for="profile_image_file">Upload Profile Picture</label><input type="file" name="profile_image_file" id="profile_image_file" class="form-control" accept="image/*"></div></div>
<div class="col-md-12"><?php echo render_textarea('address', 'address', $subcontractor->address ?? '', ['rows'=>2]); ?></div>
<div class="col-md-4"><?php echo render_input('city', 'city', $subcontractor->city ?? ''); ?></div>
<div class="col-md-4"><?php echo render_input('state', 'state', $subcontractor->state ?? 'FL'); ?></div>
<div class="col-md-4"><?php echo render_input('zip', 'zip_code', $subcontractor->zip ?? ''); ?></div>
<div class="col-md-12"><?php echo render_textarea('notes', 'notes', $subcontractor->notes ?? '', ['rows'=>5]); ?></div>
<?php if (function_exists('render_custom_fields')) { ?><div class="col-md-12"><hr><?php echo render_custom_fields('smartsource_subcontractors', $subcontractor->id ?? false); ?></div><?php } ?>
</div>
<?php if ($subcontractor && !empty($subcontractor->portal_token)) { ?>
<hr>
<div class="alert alert-info">
<strong>Subcontractor Portal Link:</strong><br>
<input type="text" class="form-control" readonly value="<?php echo smartsource_portal_profile_url($subcontractor->portal_token); ?>">
</div>
<?php } ?>
<button type="submit" class="btn btn-primary pull-right"><?php echo _l('submit'); ?></button>
</div></div>
<?php echo form_close(); ?>
</div></div>
<?php init_tail(); ?>
