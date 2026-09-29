<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content toast-master-admin">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="toast-master-page-head">
                            <div>
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('toast_master_history'); ?></h4>
                                <p class="text-muted no-margin"><?php echo _l('toast_master_history_description'); ?></p>
                            </div>
                            <div>
                                <a href="<?php echo admin_url('toast_master/settings'); ?>" class="btn btn-default btn-sm"><i class="fa fa-cog"></i> <?php echo _l('settings'); ?></a>
                                <a href="<?php echo admin_url('toast_master/clear_history'); ?>" class="btn btn-danger btn-sm _delete"><i class="fa-regular fa-trash-can"></i> <?php echo _l('clear'); ?></a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover toast-master-table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('date'); ?></th>
                                        <th><?php echo _l('type'); ?></th>
                                        <th><?php echo _l('source'); ?></th>
                                        <th><?php echo _l('title'); ?></th>
                                        <th><?php echo _l('message'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($history)) { foreach ($history as $row) { ?>
                                        <tr>
                                            <td><?php echo _dt($row['datecreated']); ?></td>
                                            <td><span class="label label-<?php echo $row['type'] === 'error' || $row['type'] === 'danger' ? 'danger' : ($row['type'] === 'warning' ? 'warning' : ($row['type'] === 'success' ? 'success' : 'info')); ?>"><?php echo html_escape(ucfirst($row['type'])); ?></span></td>
                                            <td><?php echo html_escape($row['source']); ?></td>
                                            <td><?php echo html_escape($row['title']); ?></td>
                                            <td><?php echo html_escape($row['message']); ?></td>
                                        </tr>
                                    <?php }} else { ?>
                                        <tr><td colspan="5" class="text-center text-muted"><?php echo _l('no_entries_found'); ?></td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
