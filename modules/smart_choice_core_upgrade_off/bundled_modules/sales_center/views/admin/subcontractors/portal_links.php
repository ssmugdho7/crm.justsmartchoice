<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $public_register_link = sales_center_portal_register_url(); ?>
<div id="wrapper">
    <div class="content">
<?php echo sales_center_admin_submenu('portal'); ?>

        <div class="panel_s smartsource-panel smartsource-filter-scope">
            <div class="panel-body">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 smartsource-header-row">
                    <div>
                        <h4 class="tw-m-0">Salesperson Portal</h4>
                        <p class="text-muted tw-mb-0">Send these links to salespersons so they can register, update profiles, upload documents, add license links, choose English or Spanish, and maintain company information.</p>
                    </div>
                    <div>
                        <a href="<?php echo admin_url('sales_center/portal_links'); ?>" class="btn btn-default"><i class="fa fa-refresh"></i> Refresh</a>
                    </div>
                </div>

                <div class="alert alert-success">
                    <h4 class="tw-mt-0">Main Public Registration Link</h4>
                    <p>Send this link to any new salesperson who does not have a profile yet. They can create their own profile, upload documents, and submit license information.</p>
                    <div class="input-group">
                        <input type="text" readonly class="form-control smartsource-copy-link" value="<?php echo html_escape($public_register_link); ?>">
                        <span class="input-group-btn">
                            <a href="<?php echo html_escape($public_register_link); ?>" target="_blank" class="btn btn-primary"><i class="fa fa-external-link"></i> Open Portal</a>
                        </span>
                    </div>
                    <p class="text-muted tw-mt-2">Click inside the link box to copy it.</p>
                </div>

                <div class="alert alert-info">
                    <strong>Individual Existing Salesperson Links:</strong><br>
                    Existing salespersons receive an individual secure profile link. Click inside any link box to copy it, then send it by email or text.
                </div>

                <div class="smartsource-filter-box">
                    <div class="row">
                        <div class="col-md-3"><input type="text" class="form-control" placeholder="Filter Company" data-smartsource-filter="company"></div>
                        <div class="col-md-3"><input type="text" class="form-control" placeholder="Filter Contact" data-smartsource-filter="contact"></div>
                        <div class="col-md-3"><input type="text" class="form-control" placeholder="Filter Email" data-smartsource-filter="email"></div>
                        <div class="col-md-3"><button type="button" class="btn btn-default btn-block smartsource-clear-filters"><i class="fa fa-eraser"></i> Clear</button></div>
                    </div>
                </div>

                <table class="table smartsource-data-table smartsource-table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Portal Status</th>
                            <th>Portal Link</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($salespersons as $salesperson) { ?>
                        <?php
                        $token = !empty($salesperson['portal_token']) ? $salesperson['portal_token'] : '';
                        $link = $token !== '' ? sales_center_portal_profile_url($token) : '';
                        ?>
                        <tr data-company="<?php echo html_escape($salesperson['company']); ?>" data-contact="<?php echo html_escape($salesperson['contact_name']); ?>" data-email="<?php echo html_escape($salesperson['email']); ?>">
                            <td><a href="<?php echo admin_url('sales_center/view/' . $salesperson['id']); ?>"><?php echo html_escape($salesperson['company']); ?></a></td>
                            <td><?php echo html_escape($salesperson['contact_name']); ?></td>
                            <td><?php echo html_escape($salesperson['email']); ?></td>
                            <td><?php echo !empty($salesperson['portal_enabled']) ? '<span class="label label-success">Enabled</span>' : '<span class="label label-default">Disabled</span>'; ?></td>
                            <td>
                                <?php if ($link !== '') { ?>
                                    <input type="text" readonly class="form-control input-sm smartsource-copy-link" value="<?php echo html_escape($link); ?>">
                                    <a href="<?php echo html_escape($link); ?>" target="_blank" class="btn btn-default btn-sm tw-mt-2"><i class="fa fa-external-link"></i> Open Portal</a>
                                <?php } else { ?>
                                    <span class="text-muted">Portal token missing. Edit and save this salesperson or run Repair Database.</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
