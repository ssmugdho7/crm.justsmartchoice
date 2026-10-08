<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="google-meet-wrap smart-choice-normalized-module">
  <div class="google-meet-header"><div><span class="gm-provider-label">Powered by Jitsi</span><h1><i class="fa fa-video-camera" aria-hidden="true"></i> Video Meeting Dashboard</h1><p>Create shared Jitsi rooms, invite staff and customers, and keep meeting notes in the CRM.</p></div><div><?php if (has_permission('google_meet', '', 'create')) { ?><a href="<?php echo admin_url('google_meet/create'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Meeting</a><?php } ?></div></div>
  <?php $this->load->view('google_meet/_nav'); ?>
  <div class="row gm-stats">
    <div class="col-md-4"><div class="gm-card gm-stat"><span>Total Meetings</span><strong><?php echo (int)($summary['total'] ?? 0); ?></strong></div></div>
    <div class="col-md-4"><div class="gm-card gm-stat"><span>Completed</span><strong><?php echo (int)($summary['completed'] ?? 0); ?></strong></div></div>
    <div class="col-md-4"><div class="gm-card gm-stat"><span>Total Minutes</span><strong><?php echo (int)($summary['minutes'] ?? 0); ?></strong></div></div>
  </div>
  <div class="panel_s google-meet-card"><div class="panel-body">
    <?php echo form_open(admin_url('google_meet/mass_delete'), ['id'=>'gm-dashboard-mass-form']); ?>
    <div class="gm-table-toolbar"><div>
      <?php if (has_permission('google_meet', '', 'delete')) { ?><button type="button" class="btn btn-default btn-sm gm-check-all"><i class="fa fa-check-square-o"></i> Select</button><?php } ?>
      <?php if (has_permission('google_meet', '', 'delete')) { ?><button type="submit" class="btn btn-danger btn-sm gm-mass-delete"><i class="fa fa-trash"></i> Mass Delete</button><?php } ?>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/export_csv'); ?>"><i class="fa fa-download"></i> Export</a>
      <a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet'); ?>"><i class="fa fa-refresh"></i> Reload</a>
    </div><input type="text" class="form-control input-sm gm-table-search" placeholder="Search meetings..."></div>
    <div class="table-responsive"><table class="table table-striped gm-compact-table gm-filter-table">
      <thead><tr><th style="width:32px"></th><th class="gm-col-meeting">Meeting</th><th style="width:122px">Start</th><th style="width:112px">Employee</th><th style="width:78px">Status</th><th style="width:86px">Join</th><th style="width:190px">Actions</th></tr></thead>
      <tbody>
      <?php foreach ((array)$meetings as $meeting) { $title = $meeting['title'] ?? $meeting['subject'] ?? 'Video Meeting'; ?>
        <tr>
          <td><?php if (has_permission('google_meet', '', 'delete')) { ?><input type="checkbox" name="ids[]" value="<?php echo (int)$meeting['id']; ?><?php } ?>"></td>
          <td class="gm-meeting-cell"><strong title="<?php echo html_escape($title); ?>"><?php echo html_escape($title); ?></strong><small><?php echo html_escape($meeting['meet_link'] ?? ''); ?></small></td>
          <td class="gm-nowrap"><?php echo !empty($meeting['start_time']) ? google_meet_display_datetime($meeting['start_time']) : ''; ?></td>
          <td class="gm-smalltext"><?php echo html_escape($meeting['assigned_staff_name'] ?? ''); ?></td>
          <td><span class="gm-badge gm-badge-blue"><?php echo html_escape(ucfirst($meeting['status'] ?? 'scheduled')); ?></span></td>
          <td><?php if ($this->google_meet_model->is_real_meet_link($meeting['meet_link'] ?? '', (object)$meeting)) { ?><a class="btn btn-success btn-xs" href="<?php echo admin_url('google_meet/room/' . (int)$meeting['id']); ?>"><i class="fa fa-sign-in"></i> Join</a><?php } elseif (has_permission('google_meet', '', 'edit')) { ?><a class="btn btn-warning btn-xs" href="<?php echo admin_url('google_meet/create/' . (int)$meeting['id']); ?>" title="Add the shared Video Meeting URL"><i class="fa fa-link"></i> Add Link</a><?php } ?></td>
          <td class="gm-actions"><button type="button" class="btn btn-default btn-xs gm-view-popup" data-id="<?php echo (int)$meeting['id']; ?>"><i class="fa fa-eye"></i> View</button><?php if (has_permission('google_meet', '', 'edit')) { ?> <a class="btn btn-info btn-xs" href="<?php echo admin_url('google_meet/create/' . (int)$meeting['id']); ?>"><i class="fa fa-pencil"></i> Edit</a><?php } ?><?php if (has_permission('google_meet', '', 'delete')) { ?> <?php echo form_open(admin_url('google_meet/delete/' . (int)$meeting['id']), ['style'=>'display:inline', 'onsubmit'=>"return confirm('Delete this meeting and its history?');"]); ?><button class="btn btn-danger btn-xs" type="submit"><i class="fa fa-trash"></i> Delete</button><?= form_close(); ?><?php } ?></td>
        </tr>
      <?php } ?>
      </tbody>
    </table></div>
    <?php echo form_close(); ?>
  </div></div>
</div></div></div>
<link rel="stylesheet" href="<?= module_dir_url('google_meet', 'assets/css/meeting_details.css'); ?>?v=2">
<div class="modal fade" id="gm-meeting-view-modal" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="gm-modal-title">
  <div class="modal-dialog modal-md"><div class="modal-content gm-meet-modal">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button><h4 class="modal-title"><i class="fa fa-video-camera" aria-hidden="true"></i> <span id="gm-modal-title">Video Meeting</span></h4></div>
    <div class="modal-body">
      <p class="text-muted" id="gm-modal-description"></p>
      <div class="gm-modal-link-box"><i class="fa fa-link" aria-hidden="true"></i><div><strong>Shared meeting room</strong><a id="gm-modal-link" href="#" target="_blank" rel="noopener noreferrer"></a></div></div>
      <dl class="gm-preview-facts"><div><dt>Starts</dt><dd id="gm-modal-start"></dd></div><div><dt>Status</dt><dd><span id="gm-modal-status" class="gm-preview-status"></span></dd></div></dl>
    </div>
    <div class="modal-footer"><a id="gm-modal-details" href="#" class="btn btn-default"><i class="fa fa-file-text-o" aria-hidden="true"></i> Full meeting details</a><a id="gm-modal-join" href="#" class="btn gm-preview-primary"><i class="fa fa-video-camera" aria-hidden="true"></i> Join meeting</a><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
  </div></div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.gm-view-popup').forEach(function(btn){
    btn.addEventListener('click',function(){
      var id=this.getAttribute('data-id');
      if(!id || !/^[0-9]+$/.test(id)){return;}
      fetch('<?php echo admin_url('google_meet/meeting_modal/'); ?>'+id,{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(data){
        if(!data || !data.success){ alert(data && data.message ? data.message : 'Meeting not found.'); return; }
        document.getElementById('gm-modal-title').textContent=data.title||'Video Meeting';
        document.getElementById('gm-modal-description').textContent=data.description||'No description saved for this meeting.';
        document.getElementById('gm-modal-start').textContent=data.start_time||'';
        document.getElementById('gm-modal-status').textContent=data.status||'';
        var hasRoom=!!data.room_url;
        var linkNode=document.getElementById('gm-modal-link');
        linkNode.textContent=hasRoom ? data.meet_link : 'Meeting link pending';
        if(hasRoom){linkNode.href=data.meet_link;}else{linkNode.removeAttribute('href');}
        var join=document.getElementById('gm-modal-join');
        join.setAttribute('aria-disabled',hasRoom ? 'false' : 'true');
        if(hasRoom){join.href=data.room_url;join.removeAttribute('tabindex');}else{join.removeAttribute('href');join.setAttribute('tabindex','-1');}
        document.getElementById('gm-modal-details').href='<?php echo admin_url('google_meet/view/'); ?>'+id;
        if(window.jQuery){ jQuery('#gm-meeting-view-modal').modal('show'); }
      });
    });
  });
});
</script>
<?php init_tail(); ?>
