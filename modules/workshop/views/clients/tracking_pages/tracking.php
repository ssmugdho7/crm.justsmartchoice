<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<?php hooks()->do_action('app_customers_portal_head'); ?>

<div class="jumbotron kb-search-jumbotron !tw-py-4">
    <div class="kb-search">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="text-center">
                        <h2 class="mbot30 bold kb-search-heading"><?php echo _l('wshop_track_repair') ?></h2>

                        <?php echo form_open_multipart(site_url('workshop/client/track_repair'), array('id' => 'search_job')); ?>

                        <div class="form-group has-feedback has-feedback-left">
                            <div class="input-group">
                                <input type="search" name="search" placeholder="<?php echo _l('wshop_search_by_tracking_code') ?>" class="form-control kb-search-input" value="<?php if (isset($search)) {echo new_html_entity_decode($search);}?>">
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-success kb-search-button"><?php echo _l('wshop_search_upper') ?></button>
                                </span>
                                <i class="glyphicon glyphicon-search form-control-feedback kb-search-icon"></i>
                            </div>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($repair_job_total)) {?>
    <h2 class="title title-search text-danger hide"><a class="text-danger" href="#"><?php echo new_html_entity_decode($repair_job_total) . ' ' . $search . _l('job_for_you') ?> </a></h2>
<?php }?>

<?php if (isset($repair_jobs) && count($repair_jobs) > 0) {?>

    <?php foreach ($repair_jobs as $repair_job) {?>
        <div class="panel_s">
            <div class="panel-body" id="panel_body_job">

                <div class="col-md-12">
                    <div class="row">
                        <div class="_buttons col-md-12">
                            <span href="#" class="btn btn-info tw-text-left nav-justified tw-font-semibold tw-text-lg tw-cursor-context-menu"><?php echo _l('wshop_tracking_number').': '.$repair_job['job_tracking_number']; ?></span>
                        </div>
                        <div class="col-md-12">
                            <table class="table  no-mtop">
                                <tbody>
                                    <tr class="project-overview">
                                        <td class="tw-font-semibold" width="20%"><?php echo _l('wshop_status'); ?></td>
                                        <td colspan="7"><?php echo render_repair_job_status_html($repair_job['repair_job_id'], '', $repair_job['repair_job_status'], false) ; ?></td>
                                    </tr>
                                    <tr class="project-overview">
                                        <td class="tw-font-semibold" width="20%"><?php echo _l('wshop_device_name'); ?></td>
                                        <td width="20%"><?php echo html_entity_decode($repair_job['device_name']) ; ?></td>
                                        <td class="tw-font-semibold" width="10%"><?php echo _l('wshop_repair_location'); ?></td>
                                        <td><?php echo wshop_get_model_name($repair_job['branch_id']) ; ?></td>
                                        <td class="tw-font-semibold" width="10%"><?php echo _l('wshop_model'); ?></td>
                                        <td><?php echo wshop_get_model_name($repair_job['model_id']) ; ?></td>
                                        <td class="tw-font-semibold" width="20%"><?php echo _l('wshop_serial_no'); ?></td>
                                        <td><?php echo html_entity_decode($repair_job['serial_no']) ; ?></td>

                                    </tr>
                                    <tr class="project-overview">
                                        <td class="tw-font-semibold"><?php echo _l('wshop_repair_type'); ?></td>
                                        <td colspan="7"><?php echo wshop_get_appointment_type_name($repair_job['appointment_type_id']) ; ?></td>
                                    </tr>
                                    <tr class="project-overview">
                                        <td class="tw-font-semibold"><?php echo _l('wshop_appointment_date'); ?></td>
                                        <td><?php echo _dt($repair_job['appointment_date']) ; ?></td>
                                        <td class="tw-font-semibold"><?php echo _l('wshop_estimated_completion_date'); ?></td>
                                        <td colspan="5"><?php echo _dt($repair_job['estimated_completion_date']) ; ?></td>
                                    </tr>

                                    <tr class="project-overview">
                                        <td class="" colspan="8"><?php echo _l('wshop_track_note'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php if(is_client_logged_in()){ ?>
                                <a href="<?php echo site_url('workshop/client/repair_job_detail/'.$repair_job['repair_job_id']).'?tab=detail'; ?>" class="btn btn-info pull-right"><?php echo _l('wshop_view_detail'); ?></a>
                            <?php } ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php }?>


<?php }?>

<?php if (isset($repair_jobs) && count($repair_jobs) == 0) {?>
    <div class="panel_s">
        <div class="panel-body">
            <p class="no-margin text-center"><?php echo _l('wshop_not_repair_job_found'); ?></p>
        </div>
    </div>
<?php }?>


<div id="additional">
    <input type="hidden" name="current_page" value="<?php if (isset($page)) {echo new_html_entity_decode($page);} else {echo '2';}
    ;?>">
</div>


<?php workshop_client_init_tail(); ?>
