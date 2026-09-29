<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
function superman_health_label($value) {
    return ucwords(trim(preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', (string) $value))));
}
?>
<div id="wrapper">
    <div class="content superman-wrap">
        <div class="superman-hero">
            <div>
                <h1><i class="fa fa-heartbeat"></i> <?php echo _l('superman_health_check'); ?></h1>
                <p><?php echo _l('superman_health_help'); ?></p>
            </div>
            <a href="<?php echo admin_url('superman'); ?>" class="btn superman-btn-light"><?php echo _l('superman_control_center'); ?></a>
        </div>

        <?php $this->load->view('superman/partials/nav'); ?>

        <div class="panel_s superman-panel">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped superman-table">
                        <thead>
                            <tr>
                                <th><?php echo _l('name'); ?></th>
                                <th><?php echo _l('status'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report as $row) { ?>
                                <?php
                                $status = (string) ($row['status'] ?? '');
                                $statusClass = in_array($status, ['OK', _l('superman_enabled'), 'PHP 8.5'], true) || is_numeric($status) ? 'label-success' : 'label-danger';
                                ?>
                                <tr>
                                    <td><?php echo html_escape(superman_health_label($row['label'] ?? '')); ?></td>
                                    <td><span class="label <?php echo $statusClass; ?>"><?php echo html_escape($status); ?></span></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
