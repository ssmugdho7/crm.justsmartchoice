<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><?php $this->load->view('_navigation'); ?>
<div class="row"><div class="col-md-5"><div class="panel_s"><div class="panel-body">
<h3><i class="fa fa-bell"></i> Add Daily Reminder</h3>
<?php echo form_open(admin_url('smart_daily_productivity/reminders')); ?>
<?php echo render_input('title', 'Reminder Title', '', 'text', ['required'=>'required', 'maxlength'=>'190']); ?>
<div class="form-group"><label for="remind_at">Reminder Date and Time</label><input class="form-control" type="datetime-local" id="remind_at" name="remind_at" required></div>
<?php echo render_textarea('notes', 'Instructions / Notes'); ?>
<div class="form-group"><label for="alarm">Alarm Audio</label><select class="form-control" id="alarm" name="alarm"><option value="alarm 1.mp3">Alarm 1 — Food Break</option><option value="alarm 2.mp3">Alarm 2 — Other Reminders</option><option value="none">No Sound</option></select></div>
<button class="btn sdp-reminder-button" type="submit"><i class="fa fa-save"></i> Save Reminder</button>
<?php echo form_close(); ?>
</div></div></div><div class="col-md-7"><div class="panel_s"><div class="panel-body"><h3>My Daily Reminders</h3>
<p class="text-muted">Reminders show an alert and play audio while this page remains open. Your browser may require a click to enable sound.</p>
<?php foreach($audio_files as $name=>$exists): ?><p><i class="fa <?php echo $exists ? 'fa-check-circle text-success' : 'fa-exclamation-triangle text-warning'; ?>"></i> <?php echo html_escape($name); ?> — <?php echo $exists ? 'Found' : 'Not uploaded'; ?></p><?php endforeach; ?>
<?php foreach($reminders as $r): ?><div class="sdp-reminder-card"><h4><?php echo html_escape($r['title']); ?></h4><p><?php echo html_escape($r['notes']); ?></p><small><?php echo html_escape($r['remind_at']); ?> · <?php echo html_escape($r['alarm_file']); ?> · <?php echo html_escape($r['status']); ?></small>
<?php if($r['status']==='pending'): ?><a class="btn btn-default btn-xs pull-right" href="<?php echo admin_url('smart_daily_productivity/reminders?complete='.(int)$r['id']); ?>">Mark Complete</a><?php endif; ?></div><?php endforeach; ?>
<?php if(!$reminders): ?><p>No reminders yet.</p><?php endif; ?>
</div></div></div></div></div></div>
<script>
(function(){
var seen={};var endpoint=<?php echo json_encode(admin_url('smart_daily_productivity/reminder_due')); ?>;
var audioBase=<?php echo json_encode(rtrim(site_url('media/Alarms/'), '/') . '/'); ?>;
function poll(){fetch(endpoint,{credentials:'same-origin'}).then(function(r){if(!r.ok)throw Error('network');return r.json()}).then(function(data){(data.reminders||[]).forEach(function(r){if(seen[r.id])return;seen[r.id]=true;var el=document.createElement('div');el.className='sdp-alarm-notice';var h=document.createElement('h4');h.textContent='Daily Reminder: '+r.title;var p=document.createElement('p');p.textContent=r.notes||'';var btn=document.createElement('button');btn.className='btn btn-default';btn.textContent='Dismiss';btn.onclick=function(){el.remove()};el.appendChild(h);el.appendChild(p);el.appendChild(btn);document.body.appendChild(el);if(r.alarm_file==='alarm 1.mp3'||r.alarm_file==='alarm 2.mp3'){var audio=new Audio(audioBase+encodeURIComponent(r.alarm_file));audio.play().catch(function(){var hint=document.createElement('p');hint.textContent='Click this reminder to enable audio.';el.appendChild(hint);el.addEventListener('click',function(){audio.play().catch(function(){});},{once:true});});}})}).catch(function(){});}
poll();setInterval(poll,30000);
})();
</script>
<?php init_tail(); ?>
