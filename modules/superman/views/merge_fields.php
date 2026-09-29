<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
if (!function_exists('superman_front_label')) {
    function superman_front_label($value) {
        return ucwords(trim(preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', (string) $value))));
    }
}
?>
<div id="wrapper">
    <div class="content superman-wrap">
        <div class="superman-hero">
            <div>
                <h1><i class="fa fa-search"></i> <?php echo _l('superman_merge_catalog'); ?></h1>
                <p><?php echo _l('superman_merge_catalog_help'); ?></p>
        <div class="superman-hero-actions mtop10">
            <a href="<?php echo admin_url('superman/merge_fields'); ?>" class="btn superman-btn-light"><i class="fa fa-refresh"></i> <?php echo _l('superman_refresh_merge_fields'); ?></a>
            <a href="<?php echo admin_url('superman/merge_fields?find=1'); ?>" class="btn superman-btn-dark"><i class="fa fa-search-plus"></i> <?php echo _l('superman_find_more_fields'); ?></a>
        </div>
            </div>
            <a href="<?php echo admin_url('superman'); ?>" class="btn superman-btn-light"><?php echo _l('superman_control_center'); ?></a>
        </div>

        <?php $this->load->view('superman/partials/nav'); ?>

        <?php if (!empty($find_mode)) { ?><div class="alert alert-info"><?php echo _l('superman_find_more_fields_help'); ?></div><?php } ?>
        <div class="panel_s superman-panel">
            <div class="panel-body">
                <input type="search" class="form-control superman-table-search" placeholder="<?php echo _l('search'); ?>">
                <div class="table-responsive superman-table-wrap mtop15">
                    <table class="table table-striped superman-table superman-searchable-table">
                        <thead>
                            <tr>
                                <th><?php echo _l('module'); ?></th>
                                <th><?php echo _l('field'); ?></th>
                                <th><?php echo _l('superman_merge_tag'); ?></th>
                                <th><?php echo _l('source'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($merge_catalog as $row) { ?>
                                <tr>
                                    <td><?php echo html_escape(superman_front_label($row['module'] ?? '')); ?></td>
                                    <td><?php echo html_escape(superman_front_label($row['field'] ?? '')); ?></td>
                                    <td><code><?php echo html_escape($row['merge_tag'] ?? ''); ?></code> <button class="btn btn-default btn-xs superman-copy" data-copy="<?php echo html_escape($row['merge_tag'] ?? ''); ?>"><?php echo _l('copy'); ?></button></td>
                                    <td><?php echo html_escape(superman_front_label($row['source'] ?? '')); ?></td>
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
