<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content ga-module"><div class="row"><div class="col-md-12">
<div class="panel_s"><div class="panel-body">
<div class="clearfix"><h4 class="pull-left"><i class="fa fa-line-chart"></i> <?php echo _l('ga_dashboard'); ?></h4>
<div class="pull-right"><a class="btn btn-default" href="<?php echo admin_url('google_analytics/websites'); ?>"><i class="fa fa-globe"></i> <?php echo _l('ga_websites'); ?></a> <a class="btn btn-default" href="<?php echo admin_url('google_analytics/clear_cache'); ?>"><i class="fa fa-refresh"></i> <?php echo _l('ga_clear_cache'); ?></a></div></div><hr>
<form method="get" class="form-inline"><select name="website_id" class="selectpicker" data-width="300px">
<?php foreach($websites as $site){ ?><option value="<?php echo (int)$site->id; ?>" <?php echo $website && $website->id==$site->id?'selected':''; ?>><?php echo html_escape($site->name); ?></option><?php } ?>
</select> <input name="start" class="form-control" value="<?php echo html_escape($this->input->get('start') ?: '30daysAgo'); ?>"> <input name="end" class="form-control" value="<?php echo html_escape($this->input->get('end') ?: 'today'); ?>"> <button class="btn btn-info"><i class="fa fa-filter"></i> <?php echo _l('apply'); ?></button> <?php if($website){ ?><a class="btn btn-default" href="<?php echo admin_url('google_analytics?website_id='.(int)$website->id.'&refresh=1'); ?>"><i class="fa fa-refresh"></i> <?php echo _l('ga_reload'); ?></a><?php } ?></form>
</div></div>
<?php if(!$websites){ ?><div class="alert alert-info"><?php echo _l('ga_add_website_first'); ?></div><?php } ?>
<?php if($error){ ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php } ?>
<?php if($report){ $vals=$report['summary']['rows'][0]['metricValues']??[]; $names=['activeUsers','sessions','engagedSessions','screenPageViews','eventCount','keyEvents']; ?>
<div class="row"><?php foreach($names as $i=>$name){ ?><div class="col-md-2"><div class="ga-kpi"><span><?php echo _l('ga_'.$name); ?></span><strong><?php echo html_escape($vals[$i]['value']??'0'); ?></strong></div></div><?php } ?></div>
<div class="row"><div class="col-md-6"><div class="panel_s"><div class="panel-body"><h4><?php echo _l('ga_top_pages'); ?></h4><div class="table-responsive"><table class="table table-striped"><thead><tr><th><?php echo _l('ga_page'); ?></th><th><?php echo _l('ga_views'); ?></th><th><?php echo _l('ga_users'); ?></th></tr></thead><tbody>
<?php foreach(($report['pages']['rows']??[]) as $row){ ?><tr><td><?php echo html_escape(($row['dimensionValues'][0]['value']??'').' '.($row['dimensionValues'][1]['value']??'')); ?></td><td><?php echo html_escape($row['metricValues'][0]['value']??'0'); ?></td><td><?php echo html_escape($row['metricValues'][1]['value']??'0'); ?></td></tr><?php } ?></tbody></table></div></div></div></div>
<div class="col-md-6"><div class="panel_s"><div class="panel-body"><h4><?php echo _l('ga_traffic_sources'); ?></h4><div class="table-responsive"><table class="table table-striped"><thead><tr><th><?php echo _l('ga_source'); ?></th><th><?php echo _l('ga_medium'); ?></th><th><?php echo _l('ga_sessions'); ?></th></tr></thead><tbody>
<?php foreach(($report['sources']['rows']??[]) as $row){ ?><tr><td><?php echo html_escape($row['dimensionValues'][0]['value']??''); ?></td><td><?php echo html_escape($row['dimensionValues'][1]['value']??''); ?></td><td><?php echo html_escape($row['metricValues'][0]['value']??'0'); ?></td></tr><?php } ?></tbody></table></div></div></div></div></div>
<?php } ?>
</div></div></div></div><?php init_tail(); ?></body></html>
