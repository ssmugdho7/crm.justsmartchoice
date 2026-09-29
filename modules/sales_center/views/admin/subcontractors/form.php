<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo sales_center_admin_submenu('salespersons'); ?>

<?php echo form_open_multipart($salesperson ? admin_url('sales_center/salesperson/' . $salesperson->id) : admin_url('sales_center/salesperson')); ?>
<div class="panel_s smartsource-panel"><div class="panel-body">
<h4><?php echo html_escape($title); ?></h4><hr>
<div class="row">
<div class="col-md-6"><?php echo render_input('company', 'salesperson_company', $salesperson->company ?? '', 'text', ['required'=>true]); ?></div>
<div class="col-md-3"><?php echo render_input('contact_name', 'salesperson_contact_name', $salesperson->contact_name ?? ''); ?></div>
<div class="col-md-3"><div class="checkbox checkbox-primary smartsource-portal-enabled-box"><input type="checkbox" name="portal_enabled" id="portal_enabled" value="1" <?php echo (!$salesperson || !empty($salesperson->portal_enabled)) ? 'checked' : ''; ?>><label for="portal_enabled">Sales Representative Portal Enabled</label></div></div>
<div class="col-md-6"><?php echo render_input('email', 'email', $salesperson->email ?? '', 'email'); ?></div>
<div class="col-md-6"><?php echo render_input('phone', 'phone', $salesperson->phone ?? ''); ?></div>
<div class="col-md-4"><?php echo render_input('trade', 'salesperson_trade', $salesperson->trade ?? ''); ?></div>
<div class="col-md-4"><?php echo render_select('position_type', [ ['id'=>'Sales Representative','name'=>'Sales Representative'], ['id'=>'Sales Manager','name'=>'Sales Manager'], ['id'=>'Sales Director','name'=>'Sales Director'] ], ['id','name'], 'Position Type', $salesperson->position_type ?? 'Sales Representative'); ?></div>
<div class="col-md-4"><?php echo render_select('employment_type', [ ['id'=>'W-2 Employee','name'=>'W-2 Employee'], ['id'=>'1099 Contractor','name'=>'1099 Contractor'], ['id'=>'Commission Only','name'=>'Commission Only'] ], ['id','name'], 'Employment Type', $salesperson->employment_type ?? '1099 Contractor'); ?></div>
<div class="col-md-4"><?php echo render_input('department_id', 'Department ID', $salesperson->department_id ?? '', 'number'); ?></div>
<div class="col-md-4"><?php echo render_input('commission_rate', 'Commission Rate %', $salesperson->commission_rate ?? '0.00', 'number', ['step'=>'0.01']); ?></div>
<div class="col-md-4"><?php echo render_input('sales_goal', 'Sales Goal', $salesperson->sales_goal ?? '0.00', 'number', ['step'=>'0.01']); ?></div>

<div class="col-md-4"><?php echo render_select('category', $categories, ['name','name'], 'sales_center_category', $salesperson->category ?? ''); ?></div>
<div class="col-md-4"><?php echo render_select('status', $statuses, ['slug','name'], 'status', $salesperson->status ?? 'active'); ?></div>
<div class="col-md-4"><?php echo render_input('license_number', 'salesperson_license_number', $salesperson->license_number ?? ''); ?></div>
<div class="col-md-4"><?php echo render_date_input('insurance_expiration', 'salesperson_insurance_expiration', $salesperson->insurance_expiration ?? ''); ?></div>
<div class="col-md-4"><?php echo render_input('assigned', 'Assigned Staff ID', $salesperson->assigned ?? get_staff_user_id(), 'number'); ?></div>
<div class="col-md-4"><?php echo render_select('staff_id', $staff_members, ['staffid', ['firstname','lastname']], 'Linked Staff Account', $salesperson->staff_id ?? ''); ?></div>
<div class="col-md-6"><?php echo render_input('dbpr_link', 'sales_center_dbpr_link', $salesperson->dbpr_link ?? '', 'text'); ?></div>
<div class="col-md-6"><?php echo render_input('county_license_link', 'sales_center_county_license_link', $salesperson->county_license_link ?? '', 'text'); ?></div>
<div class="col-md-6"><?php echo render_input('profile_image', 'Profile Picture URL', $salesperson->profile_image ?? '', 'text'); ?></div>
<div class="col-md-6"><div class="form-group"><label for="profile_image_file">Upload Profile Picture</label><input type="file" name="profile_image_file" id="profile_image_file" class="form-control" accept="image/*"></div></div>
<div class="col-md-12"><?php echo render_textarea('address', 'address', $salesperson->address ?? '', ['rows'=>2]); ?></div>
<div class="col-md-4"><?php echo render_input('city', 'city', $salesperson->city ?? ''); ?></div>
<div class="col-md-4"><?php echo render_input('state', 'state', $salesperson->state ?? 'FL'); ?></div>
<div class="col-md-4"><?php echo render_input('zip', 'zip_code', $salesperson->zip ?? ''); ?></div>
<div class="col-md-12"><?php echo render_textarea('notes', 'notes', $salesperson->notes ?? '', ['rows'=>5]); ?></div>
<?php if (function_exists('render_custom_fields')) { ?><div class="col-md-12"><hr><?php echo render_custom_fields('sales_center', $salesperson->id ?? false); ?></div><?php } ?>
</div>
<?php if ($salesperson && !empty($salesperson->portal_token)) { ?>
<hr>
<div class="alert alert-info">
<strong>Sales Representative Portal Link:</strong><br>
<input type="text" class="form-control" readonly value="<?php echo sales_center_portal_profile_url($salesperson->portal_token); ?>">
</div>
<?php } ?>
<button type="submit" class="btn btn-primary pull-right"><?php echo _l('submit'); ?></button>
</div></div>
<?php echo form_close(); ?>
</div></div>
<?php init_tail(); ?>
