<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('partials/nav'); ?>
<div class="panel_s"><div class="panel-body">
<h4><?php echo _l('master_crm_fix'); ?></h4>
<p class="text-muted"><?php echo _l('master_crm_fix_description'); ?></p>
<div class="alert alert-info"><?php echo html_escape($backup_root); ?></div>
<?php if(!$zip_available): ?><div class="alert alert-danger"><?php echo _l('master_crm_fix_zip_missing'); ?></div><?php endif; ?>
<?php if(!$writable): ?><div class="alert alert-danger"><?php echo _l('master_crm_fix_not_writable'); ?></div><?php endif; ?>
<div class="row">
<div class="col-md-3"><label><?php echo _l('master_crm_fix_pack_size'); ?></label><input id="pack_size" type="number" min="50" max="480" value="<?php echo $pack_size; ?>" class="form-control"></div>
<div class="col-md-3"><div class="checkbox checkbox-primary mtop25"><input id="inc_db" type="checkbox" <?php echo $db?'checked':''; ?>><label for="inc_db"><?php echo _l('master_crm_fix_include_database'); ?></label></div></div>
<div class="col-md-3"><div class="checkbox checkbox-primary mtop25"><input id="exc_arc" type="checkbox" <?php echo $archives?'checked':''; ?>><label for="exc_arc"><?php echo _l('master_crm_fix_exclude_archives'); ?></label></div></div>
<div class="col-md-3"><div class="checkbox checkbox-primary mtop25"><input id="inc_up" type="checkbox" <?php echo $uploads?'checked':''; ?>><label for="inc_up"><?php echo _l('master_crm_fix_include_uploads'); ?></label></div></div>
</div>
<button id="start_backup" class="btn btn-primary" <?php echo (!$zip_available||!$writable)?'disabled':''; ?>><i class="fa-solid fa-play"></i> <?php echo _l('master_crm_fix_create_backup'); ?></button>
<div id="prog_wrap" class="hide mtop20"><div class="progress"><div id="prog" class="progress-bar progress-bar-success" style="width:0%">0%</div></div><p id="status"></p></div>
</div></div>
<div class="panel_s"><div class="panel-body"><h4><?php echo _l('master_crm_fix_existing_backups'); ?></h4>
<div class="table-responsive"><table class="table table-striped"><thead><tr>
<th><?php echo _l('master_crm_fix_backup_id'); ?></th><th><?php echo _l('master_crm_fix_created'); ?></th><th><?php echo _l('master_crm_fix_status'); ?></th><th><?php echo _l('master_crm_fix_files'); ?></th><th><?php echo _l('master_crm_fix_actions'); ?></th>
</tr></thead><tbody>
<?php foreach($jobs as $j): ?><tr><td><?php echo html_escape($j['job_id']); ?></td><td><?php echo html_escape($j['created_at']); ?></td><td><?php echo ucwords(str_replace('_',' ',$j['status'])); ?></td><td><?php echo (int)$j['total_files']; ?></td><td>
<?php foreach(($j['output_files']??[]) as $f): ?><a class="btn btn-xs btn-default mbot5" href="<?php echo admin_url('master_crm_fix/download/'.rawurlencode($j['job_id']).'/'.rawurlencode($f['name'])); ?>"><i class="fa-solid fa-download"></i> <?php echo html_escape($f['name']); ?></a> <?php endforeach; ?>
<?php if(staff_can('delete',MASTER_CRM_FIX_MODULE_NAME)): ?><a class="btn btn-xs btn-danger _delete" href="<?php echo admin_url('master_crm_fix/delete/'.rawurlencode($j['job_id'])); ?>"><?php echo _l('delete'); ?></a><?php endif; ?>
</td></tr><?php endforeach; ?>
<?php if(!$jobs): ?><tr><td colspan="5" class="text-center text-muted"><?php echo _l('master_crm_fix_no_backups'); ?></td></tr><?php endif; ?>
</tbody></table></div></div></div>
<div class="alert alert-warning"><strong><?php echo _l('master_crm_fix_restore_notice_title'); ?></strong> <?php echo _l('master_crm_fix_restore_notice'); ?></div>
</div></div></div></div>
<script>
(function(){
 const b=document.getElementById('start_backup'),w=document.getElementById('prog_wrap'),p=document.getElementById('prog'),s=document.getElementById('status');
 if(!b)return;
 function set(v,t){w.classList.remove('hide');p.style.width=v+'%';p.textContent=v+'%';s.textContent=t||'';}
 function run(id){fetch(admin_url+'master_crm_fix/process/'+encodeURIComponent(id),{credentials:'same-origin'}).then(r=>r.json()).then(x=>{
  if(!x.success)throw new Error(x.message||'Backup failed');
  set(x.progress,'Status: '+x.status+' | Files: '+x.processed+' / '+x.total);
  if(x.status==='completed'){set(100,'<?php echo addslashes(_l('master_crm_fix_completed')); ?>');setTimeout(()=>location.reload(),1000);return;}
  if(x.status==='failed')throw new Error((x.errors||[]).join('\\n'));
  setTimeout(()=>run(id),350);
 }).catch(e=>{b.disabled=false;alert(e.message);});}
 b.addEventListener('click',()=>{
  b.disabled=true;set(0,'<?php echo addslashes(_l('master_crm_fix_starting')); ?>');
  const d=new FormData();d.append('pack_size_mb',document.getElementById('pack_size').value);
  d.append('include_database',document.getElementById('inc_db').checked?'1':'');
  d.append('exclude_archives',document.getElementById('exc_arc').checked?'1':'');
  d.append('include_uploads',document.getElementById('inc_up').checked?'1':'');
  d.append('<?php echo $this->security->get_csrf_token_name(); ?>','<?php echo $this->security->get_csrf_hash(); ?>');
  fetch(admin_url+'master_crm_fix/start',{method:'POST',credentials:'same-origin',body:d}).then(r=>r.json()).then(x=>{if(!x.success)throw new Error(x.message);run(x.job_id);}).catch(e=>{b.disabled=false;alert(e.message);});
 });
})();
</script>
<?php init_tail(); ?>