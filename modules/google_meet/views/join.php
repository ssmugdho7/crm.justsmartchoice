<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="google-meet-wrap smart-choice-normalized-module">
  <div class="google-meet-header"><div><h1><i class="fa fa-sign-in"></i> Join Google Meet</h1><p>Fast access to scheduled, live, and recently created meetings.</p></div><div><?php if (has_permission('google_meet', '', 'create')) { ?><a href="<?php echo admin_url('google_meet/create'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Meeting</a><?php } ?></div></div>
  <?php $this->load->view('google_meet/_nav'); ?>
  <div class="panel_s google-meet-card"><div class="panel-body">
    <div class="gm-table-toolbar"><input type="text" class="form-control input-sm gm-table-search" placeholder="Search meetings..."><a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/join'); ?>"><i class="fa fa-refresh"></i> Reload</a></div>
    <div class="table-responsive"><table class="table table-striped gm-compact-table gm-filter-table">
      <thead><tr><th>Meeting</th><th style="width:130px">Start</th><th style="width:95px">Status</th><th style="width:90px">Join</th><th style="width:90px">View</th></tr></thead><tbody>
      <?php foreach((array)$meetings as $m){ $title = $m['subject'] ?? $m['title'] ?? 'Google Meet Meeting'; ?><tr>
        <td class="gm-meeting-cell"><strong title="<?php echo html_escape($title); ?>"><?php echo html_escape($title); ?></strong><small><?php echo html_escape($m['meet_link'] ?? ''); ?></small></td>
        <td class="gm-nowrap"><?php echo !empty($m['start_time']) ? google_meet_display_datetime($m['start_time']) : ''; ?></td>
        <td><span class="gm-badge gm-badge-blue"><?php echo html_escape(ucfirst($m['status'] ?? 'scheduled')); ?></span></td>
        <td><?php if (!empty($m['meet_link'])) { ?><a class="btn btn-success btn-xs" href="<?php echo html_escape($m['meet_link']); ?>" target="_blank" rel="noopener"><i class="fa fa-sign-in"></i> Join</a><?php } ?></td>
        <td><button type="button" class="btn btn-default btn-xs gm-view-popup" data-id="<?php echo (int)$m['id']; ?>"><i class="fa fa-eye"></i> View</button></td>
      </tr><?php } ?>
      </tbody></table></div>
  </div></div>
</div></div></div>
<div class="modal fade" id="gm-meeting-view-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md"><div class="modal-content gm-meet-modal">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><i class="fa fa-video-camera"></i> <span id="gm-modal-title">Google Meet</span></h4></div>
    <div class="modal-body">
      <p class="text-muted" id="gm-modal-description"></p>
      <div class="gm-modal-link-box"><i class="fa fa-television"></i><div><strong>Meeting Link</strong><a id="gm-modal-link" href="#" target="_blank" rel="noopener"></a></div></div>
      <p class="gm-modal-meta"><strong>Start:</strong> <span id="gm-modal-start"></span> &nbsp; <strong>Status:</strong> <span id="gm-modal-status"></span></p>
    </div>
    <div class="modal-footer"><a id="gm-modal-join" href="#" target="_blank" rel="noopener" class="btn btn-success btn-sm"><i class="fa fa-sign-in"></i> Join Meeting</a><button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button></div>
  </div></div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.gm-view-popup').forEach(function(btn){
    btn.addEventListener('click',function(){
      var id=this.getAttribute('data-id');
      if(!id){return;}
      fetch('<?php echo admin_url('google_meet/meeting_modal/'); ?>'+id,{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(data){
        if(!data || !data.success){ alert(data && data.message ? data.message : 'Meeting not found.'); return; }
        document.getElementById('gm-modal-title').textContent=data.title||'Google Meet';
        document.getElementById('gm-modal-description').textContent=data.description||'No description saved for this meeting.';
        document.getElementById('gm-modal-start').textContent=data.start_time||'';
        document.getElementById('gm-modal-status').textContent=data.status||'';
        var link=data.meet_link||'#';
        document.getElementById('gm-modal-link').textContent=link;
        document.getElementById('gm-modal-link').href=link;
        document.getElementById('gm-modal-join').href=link;
        if(window.jQuery){ jQuery('#gm-meeting-view-modal').modal('show'); }
      });
    });
  });
});
</script>
<?php init_tail(); ?>
