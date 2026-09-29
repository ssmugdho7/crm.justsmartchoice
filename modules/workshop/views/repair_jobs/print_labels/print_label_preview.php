<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal fade" id="print_label_preview" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="add-title"><?php echo _l('wshop_print_label'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <span class="tw-font-semibold tw-text-lg">
                            <?php echo _l('wshop_preview').': '; ?>
                        </span>
                        <span><?php echo _l('wshop_preview_size_25_50'); ?></span>
                    </div>
                    <div class="col-md-6 col-md-offset-4">
                        <div class="print-label">
                            <div class="row">
                                <div class="company-logo col-md-5">
                                    <?php 
                                    $companyUploadPath         = get_upload_path_by_type('company');
                                    $pdf_logo_Url = get_option('company_logo');
                                    $barcode_path  = site_url('modules/workshop/uploads/repair_job_barcodes/' . md5($repair_job->job_tracking_number ?? '').'.svg');
                                    ?>
                                    <img src="<?php echo base_url('uploads/company/' . $pdf_logo_Url); ?>" class="img-responsive">
                                </div>
                                <div class="col-md-7 tw-px-0.5">
                                    <p class="repair-number"><?php echo format_repair_job_number($repair_job->id) ?></p>
                                </div>
                            </div>

                            <p class="no-mbot"><?php echo get_company_name($repair_job->client_id) ?></p>
                            <p class="no-mbot"><?php echo wshop_get_branch_name($repair_job->branch_id) ?></p>
                            <div>
                                <img src="<?php echo html_entity_decode($barcode_path) ?>" alt="Barcode" width="120">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <a class="btn btn-info" href="<?php echo admin_url('workshop/repair_job_print_lable_pdf/' . $repair_job->id . '?print=true'); ?>"
                    target="_blank">
                    <?php echo _l('print'); ?>
                </a>
            </div>
        </div>
        <div id="box-loading"></div>
    </div>
</div>
