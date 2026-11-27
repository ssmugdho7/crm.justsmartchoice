<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?><div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view("_navigation"); ?>
<h4><?php echo smart_daily_productivity_label('smart_daily_productivity_reports'); ?></h4>
<p class="text-muted">Select two or more employees to compare their productivity, or leave the selection empty to view all staff. Click an employee's name to see the work recorded on that day.</p>
<?php echo form_open(admin_url('smart_daily_productivity/reports'), ['method'=>'get']); ?><div class="row">
<div class="col-md-3"><?php echo render_date_input('date_from', smart_daily_productivity_label('smart_daily_productivity_date_from'),$date_from); ?></div>
<div class="col-md-3"><?php echo render_date_input('date_to', smart_daily_productivity_label('smart_daily_productivity_date_to'),$date_to); ?></div>
<div class="col-md-4"><label>Compare Employees</label><select name="staff_ids[]" class="selectpicker" multiple data-live-search="true" data-actions-box="true" data-width="100%" title="Select employees"><?php foreach($staff as $member){ ?><option value="<?php echo (int)$member['staffid']; ?>" <?php echo in_array((int)$member['staffid'], $staff_ids ?? [], true)?'selected':''; ?>><?php echo html_escape($member['firstname'].' '.$member['lastname']); ?></option><?php } ?></select></div>
<div class="col-md-2"><label>&nbsp;</label><button class="btn btn-primary btn-block" type="submit">Compare / Refresh</button></div></div><?php echo form_close(); ?>
<hr><h4>Employee Productivity Comparison</h4><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Employee</th><th>Date</th><th>Score</th><th>Rating</th><th>Project</th><th>Contract</th><th>Client</th><th>Research</th><th>Hours</th></tr></thead><tbody>
<?php foreach($scores as $row){ ?><tr><td><a href="<?php echo admin_url('smart_daily_productivity/staff_day/'.(int)$row['staff_id'].'?date='.urlencode($row['score_date'])); ?>"><?php echo html_escape(trim($row['firstname'].' '.$row['lastname'])); ?></a></td><td><?php echo _d($row['score_date']); ?></td><td><?php echo html_escape($row['score_percent']); ?>%</td><td><?php echo html_escape($row['rating_label']); ?></td><td><?php echo (int)$row['project_tasks']; ?></td><td><?php echo (int)$row['contract_tasks']; ?></td><td><?php echo (int)$row['client_tasks']; ?></td><td><?php echo (int)$row['research_tasks']; ?></td><td><?php echo round(((int)$row['minutes_total'])/60,2); ?></td></tr><?php } ?></tbody></table></div>
<div class="sdp-chart-grid">
 <section class="sdp-chart-panel"><h4><i class="fa fa-bar-chart"></i> Employee Productivity Progress</h4><p class="text-muted">Horizontal bars keep employee names and dates easy to read.</p><div class="sdp-chart-canvas" id="sdpBarContainer"><canvas id="sdpComparisonChart" aria-label="Horizontal employee productivity comparison"></canvas></div></section>
 <section class="sdp-chart-panel"><h4><i class="fa fa-pie-chart"></i> Productivity Share</h4><p class="text-muted">Distribution of recorded productivity points across the selected employees and dates.</p><div class="sdp-chart-canvas sdp-donut"><canvas id="sdpShareChart" aria-label="Productivity share doughnut chart"></canvas></div><div id="sdpChartLegend" class="sdp-chart-legend"></div></section>
</div>
<hr><h4>Recorded Daily Work</h4><p class="text-muted">Manual daily entries and automatically captured activities. Click a name to inspect the full day's work.</p>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Employee</th><th>Date</th><th>Activity</th><th>Category</th><th>Hours</th><th>Work Description</th></tr></thead><tbody>
<?php foreach($entries as $entry){ ?><tr><td><a href="<?php echo admin_url('smart_daily_productivity/staff_day/'.(int)$entry['staff_id'].'?date='.urlencode($entry['entry_date'])); ?>"><?php echo html_escape(trim(($entry['firstname']??'').' '.($entry['lastname']??''))); ?></a></td><td><?php echo _d($entry['entry_date']); ?></td><td><?php echo html_escape($entry['title']); ?></td><td><?php echo html_escape($entry['category']); ?></td><td><?php echo round((int)$entry['minutes_spent']/60,2); ?></td><td style="white-space:pre-wrap;max-width:400px"><?php echo html_escape((string)$entry['description']); ?></td></tr><?php } ?>
<?php if (!$entries){ ?><tr><td colspan="6" class="text-muted text-center">No daily work entries in this date range.</td></tr><?php } ?></tbody></table></div>
</div></div></div></div><?php init_tail(); ?>
<script>
(function(){
 var bar=document.getElementById('sdpComparisonChart'), pie=document.getElementById('sdpShareChart');
 if(!bar||!pie||typeof Chart==='undefined')return;
 var rows=<?php echo json_encode(array_map(static function($r){return ['name'=>trim($r['firstname'].' '.$r['lastname']),'date'=>$r['score_date'],'score'=>(float)$r['score_percent']];},array_slice($scores,0,25)),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
 var names=rows.map(function(r){return [r.name,r.date]});
 var values=rows.map(function(r){return r.score});
 var colors=['#14856e','#f58220','#3b82f6','#8b5cf6','#06b6d4','#d97706','#64748b','#db2777','#84cc16','#0d9488'];
 var palette=rows.map(function(r,i){return colors[i%colors.length]});
 var barContainer=document.getElementById('sdpBarContainer');
 if(barContainer)barContainer.style.height=Math.max(260,rows.length*44+60)+'px';
 new Chart(bar.getContext('2d'),{type:'horizontalBar',data:{labels:names,datasets:[{label:'Productivity score (%)',data:values,backgroundColor:'#14856e',borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,legend:{display:false},scales:{xAxes:[{ticks:{beginAtZero:true,max:100},gridLines:{color:'#edf2f7'}}],yAxes:[{ticks:{autoSkip:false,fontSize:11},gridLines:{display:false}}]},tooltips:{callbacks:{label:function(item){return item.xLabel+'%'}}}}});
 var pieRows=rows.filter(function(r){return r.score>0});
 var legend=document.getElementById('sdpChartLegend');
 if(!pieRows.length){if(legend)legend.textContent='No productivity scores in this period.';pie.style.display='none';return;}
 new Chart(pie.getContext('2d'),{type:'doughnut',data:{labels:pieRows.map(function(r){return r.name+' — '+r.date}),datasets:[{data:pieRows.map(function(r){return r.score}),backgroundColor:pieRows.map(function(r,i){return colors[i%colors.length]}),borderColor:'#fff',borderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,cutoutPercentage:60,legend:{display:false},tooltips:{callbacks:{label:function(item,data){return data.labels[item.index]+': '+data.datasets[0].data[item.index]+' score points';}}}}});
 if(legend){pieRows.forEach(function(r,i){var line=document.createElement('div');line.className='sdp-legend-item';var dot=document.createElement('span');dot.className='sdp-legend-dot';dot.style.backgroundColor=colors[i%colors.length];var label=document.createElement('span');label.textContent=r.name+' · '+r.date;line.appendChild(dot);line.appendChild(label);legend.appendChild(line);});}
})();
</script>
