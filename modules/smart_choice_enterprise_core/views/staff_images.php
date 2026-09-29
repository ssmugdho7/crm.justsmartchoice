<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div id="sc-enterprise-app">
    <div class="content">
        <div class="panel_s">
            <div class="panel-body">
                <div class="sc-enterprise-header">
                    <div>
                        <h1><?php echo html_escape($title); ?></h1>
                        <p><?php echo _l('staff_image_health_description'); ?></p>
                    </div>
                    <div class="sc-toolbar">
<a href="<?php echo admin_url('smart_choice_enterprise_core/repair_staff_images'); ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-folder-plus"></i> <?php echo _l('enterprise_image_repair'); ?></a>
                        <button type="button" class="btn btn-default btn-sm" data-toggle="collapse" data-target="#sc-image-filters"><i class="fa-solid fa-filter"></i> <?php echo _l('filters'); ?></button>
                        <a href="<?php echo admin_url('smart_choice_enterprise_core/staff_images'); ?>" class="btn btn-default btn-sm"><i class="fa-solid fa-rotate"></i> <?php echo _l('reload'); ?></a>
                        <?php if (has_permission(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME, '', 'export')) { ?>
                            <a href="<?php echo admin_url('smart_choice_enterprise_core/export_staff_images'); ?>" class="btn btn-info btn-sm"><i class="fa-solid fa-file-export"></i> <?php echo _l('export'); ?></a>
                        <?php } ?>
                    </div>
                </div>
                <div id="sc-image-filters" class="collapse sc-filter-panel">
                    <label for="sc-status-filter"><?php echo _l('status'); ?></label>
                    <select id="sc-status-filter" class="form-control input-sm">
                        <option value=""><?php echo _l('all'); ?></option>
                        <option value="Found"><?php echo _l('found'); ?></option>
                        <option value="Missing"><?php echo _l('missing'); ?></option>
                    </select>
                </div>
                <div class="row sc-summary-row">
                    <div class="col-sm-4"><strong><?php echo _l('records'); ?>:</strong> <?php echo (int) $summary['total']; ?></div>
                    <div class="col-sm-4"><strong><?php echo _l('found'); ?>:</strong> <?php echo (int) $summary['found']; ?></div>
                    <div class="col-sm-4"><strong><?php echo _l('missing'); ?>:</strong> <?php echo (int) $summary['missing']; ?></div>
                </div>
                <div class="sc-staff-image-table-wrap">
                    <table id="sc-staff-image-table" class="table table-striped table-hover dt-table sc-staff-image-table">
                        <thead><tr>
                            <th class="sc-col-id"><?php echo _l('staff_id'); ?></th>
                            <th class="sc-col-photo"><?php echo _l('photo'); ?></th>
                            <th class="sc-col-employee"><?php echo _l('employee'); ?></th>
                            <th class="sc-col-email"><?php echo _l('email'); ?></th>
                            <th class="sc-col-file"><?php echo _l('database_filename'); ?></th>
                            <th class="sc-col-path"><?php echo _l('resolved_path'); ?></th>
                            <th class="sc-col-status"><?php echo _l('status'); ?></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($rows as $row) { ?>
                            <tr>
                                <td><?php echo (int) $row['staffid']; ?></td>
                                <td><img src="<?php echo html_escape($row['image_url']); ?>" alt="<?php echo html_escape($row['employee']); ?>" class="sc-staff-avatar"></td>
                                <td><?php echo html_escape($row['employee']); ?></td>
                                <td class="sc-cell-wrap" title="<?php echo html_escape($row['email']); ?>"><?php echo html_escape($row['email']); ?></td>
                                <td class="sc-cell-wrap" title="<?php echo html_escape($row['profile_image']); ?>"><?php echo html_escape($row['profile_image']); ?></td>
                                <td class="sc-cell-path" title="<?php echo html_escape($row['resolved_path']); ?>"><?php echo html_escape($row['resolved_path']); ?></td>
                                <td><span class="label <?php echo $row['status'] === 'Found' ? 'label-success' : 'label-warning'; ?>"><?php echo html_escape(_l(strtolower($row['status']))); ?></span></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mtop20"><?php echo _l('staff_image_upload_path_notice'); ?></div>
            </div>
        </div>
    </div>
</div>
</div><?php init_tail(); ?>
