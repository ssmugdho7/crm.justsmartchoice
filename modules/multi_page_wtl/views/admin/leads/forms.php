<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<div class="_buttons">
<?php if (is_admin() || has_permission(MPWTL_MODULE_NAME,'','create')) { ?>
<a href="<?php echo admin_url(MPWTL_MODULE_NAME.'/leads/form'); ?>" class="btn btn-info pull-left"><?php echo _l('new_form'); ?></a>
<?php } ?>
</div><div class="clearfix"></div><hr class="hr-panel-heading" />
<?php render_datatable([_l('id'),_l('form_name'),_l('mpwtl_theme'),_l('total_submissions'),_l('leads_dt_datecreated')], 'web-to-lead'); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
<script>$(function(){initDataTable('.table-web-to-lead', window.location.href);});</script>
</body></html>
