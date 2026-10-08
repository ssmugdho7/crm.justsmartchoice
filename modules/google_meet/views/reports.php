<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
  <div class="google-meet-header"><div><h1><i class="fa fa-bar-chart"></i> Video Meeting Reports</h1><p>Filter meetings by employee, date, status, notes, and topic.</p></div></div>
  <?php $this->load->view('google_meet/_nav'); ?>
  <div class="row">
    <div class="col-md-4"><div class="panel_s google-meet-card"><div class="panel-body gm-stat"><span>Total Meetings</span><strong><?php echo (int)($summary['total'] ?? 0); ?></strong></div></div></div>
    <div class="col-md-4"><div class="panel_s google-meet-card"><div class="panel-body gm-stat"><span>Completed</span><strong><?php echo (int)($summary['completed'] ?? 0); ?></strong></div></div></div>
    <div class="col-md-4"><div class="panel_s google-meet-card"><div class="panel-body gm-stat"><span>Total Minutes</span><strong><?php echo (int)($summary['minutes'] ?? 0); ?></strong></div></div></div>
  </div>
  <div class="panel_s google-meet-card"><div class="panel-body">
    <?php echo form_open(admin_url('google_meet/reports'), ['method'=>'get']); ?>
    <div class="row gm-filter-row">
      <div class="col-md-3"><label>Employee</label><select name="staff_id" <?php if (!has_permission('google_meet', '', 'view')) { echo 'disabled'; } ?> class="form-control selectpicker" data-live-search="true" data-width="100%"><option value="">All Employees</option><?php foreach((array)$staff as $s){$sid=(int)$s['staffid'];?><option value="<?php echo $sid; ?>" <?php echo ((int)($filters['staff_id']??0)===$sid)?'selected':''; ?>><?php echo html_escape(trim(($s['firstname']??'').' '.($s['lastname']??''))); ?></option><?php } ?></select></div>
      <div class="col-md-2"><?php echo render_input('date_from','From',$filters['date_from']??'','date'); ?></div>
      <div class="col-md-2"><?php echo render_input('date_to','To',$filters['date_to']??'','date'); ?></div>
      <div class="col-md-2"><label>Status</label><select name="status" class="form-control"><option value="">All</option><?php foreach(['scheduled','live','completed'] as $status){?><option value="<?php echo $status; ?>" <?php echo (($filters['status']??'')===$status)?'selected':''; ?>><?php echo ucfirst($status); ?></option><?php } ?></select></div>
      <div class="col-md-3"><?php echo render_input('q','Search',$filters['q']??'','text'); ?></div>
    </div>
    <button class="btn btn-primary btn-sm" type="submit"><i class="fa fa-search"></i> Filter Meetings</button>
    <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/reports'); ?>">Reset</a>
    <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/export_csv?' . http_build_query($filters)); ?>"><i class="fa fa-download"></i> Export</a>
    <?php echo form_close(); ?>
  </div></div>
  <div class="panel_s google-meet-card"><div class="panel-body">
    <?php echo form_open(admin_url('google_meet/mass_delete'), ['id'=>'gm-report-mass-form']); ?>
    <div class="gm-table-toolbar"><div><?php if (has_permission('google_meet', '', 'delete')) { ?><button type="button" class="btn btn-default btn-sm gm-check-all">Select</button><button type="submit" class="btn btn-danger btn-sm gm-mass-delete">Delete Selected</button><?php } ?><a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/reports'); ?>">Reload</a></div><input type="text" class="form-control input-sm gm-table-search" placeholder="Search report rows..."></div>
    <div class="table-responsive"><table class="table table-striped gm-compact-table gm-filter-table"><thead><tr><th></th><th>Meeting</th><th>Employee</th><th>Start</th><th>Status</th><th>Notes</th><th>Join</th><th>Actions</th></tr></thead><tbody>
    <?php foreach((array)$meetings as $m){ ?><tr>
      <td><?php if (has_permission('google_meet', '', 'delete')) { ?><input type="checkbox" name="ids[]" value="<?php echo (int)$m['id']; ?>"><?php } ?></td>
      <td class="gm-meeting-cell"><strong><?php echo html_escape($m['subject'] ?: $m['title'] ?: 'Video Meeting'); ?></strong><small><?php echo html_escape($m['description'] ?? ''); ?></small></td>
      <td class="gm-nowrap"><?php echo html_escape($m['assigned_staff_name'] ?: $m['created_by_name'] ?: ''); ?></td>
      <td class="gm-nowrap"><?php echo !empty($m['start_time']) ? google_meet_display_datetime($m['start_time']) : ''; ?></td>
      <td><span class="gm-badge gm-badge-blue"><?php echo html_escape($m['status'] ?? ''); ?></span></td>
      <td class="gm-smalltext"><?php echo html_escape($m['notes'] ?? ''); ?></td>
      <td><?php if(!empty($m['meet_link'])){?><a  class="btn btn-success btn-xs" href="<?php echo html_escape($m['meet_link']); ?>">Join</a><?php } ?></td>
      <td><a class="btn btn-default btn-xs" href="<?php echo admin_url('google_meet/view/' . (int)$m['id']); ?>">View</a></td>
    </tr><?php } ?></tbody></table></div><?php echo form_close(); ?>
  </div></div>
</div></div>
<script>
document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.gm-table-search').forEach(function(i){i.addEventListener('keyup',function(){var q=this.value.toLowerCase();document.querySelectorAll('.gm-filter-table tbody tr').forEach(function(r){r.style.display=r.innerText.toLowerCase().indexOf(q)>-1?'':'none';});});});document.querySelectorAll('.gm-check-all').forEach(function(b){b.addEventListener('click',function(){var f=this.closest('form');f.querySelectorAll('input[type=checkbox][name="ids[]"]').forEach(function(c){c.checked=!c.checked;});});});document.querySelectorAll('.gm-mass-delete').forEach(function(b){b.addEventListener('click',function(e){var f=this.closest('form');if(!f.querySelector('input[name="ids[]"]:checked')){e.preventDefault();alert('Please select at least one meeting.');return false;}if(!confirm('Are you sure you want to delete the selected meetings?')){e.preventDefault();return false;}});});});
</script>
<?php init_tail(); ?>
