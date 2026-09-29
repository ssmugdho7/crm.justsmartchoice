<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
    <div class="dm-card dm-hero-card">
        <div><h3><i class="fa fa-heartbeat"></i> <?php echo _l('dmg_health_checker'); ?></h3><p><?php echo _l('dmg_health_checker_description'); ?></p></div>
        <a href="<?php echo admin_url('document_management/settings?tab=enterprise'); ?>" class="btn btn-default btn-sm"><i class="fa fa-cogs"></i> <?php echo _l('dmg_settings'); ?></a>
    </div>
    <div class="panel_s"><div class="panel-body table-responsive">
        <table class="table table-striped dt-table"><thead><tr><th><?php echo _l('dmg_check'); ?></th><th><?php echo _l('dmg_status'); ?></th><th><?php echo _l('dmg_message'); ?></th></tr></thead><tbody>
        <?php foreach($checks as $check){ ?>
            <tr>
                <td><?php echo html_escape($check['name']); ?></td>
                <td><?php if($check['status']){ ?><span class="label label-success"><?php echo _l('dmg_ok'); ?></span><?php } else { ?><span class="label label-danger"><?php echo _l('dmg_attention'); ?></span><?php } ?></td>
                <td><?php echo html_escape($check['message']); ?></td>
            </tr>
        <?php } ?>
        </tbody></table>
    </div></div>
</div></div></div></div>
<?php init_tail(); ?>
</body></html>
