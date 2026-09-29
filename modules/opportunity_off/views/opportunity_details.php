<?php init_head();
if (!empty($opportunity_details->rel_id) && !empty($opportunity_details->rel_type)) {
    echo '<br />' . _l('task_related_to') . ': <a class="text-muted" href="' . task_rel_link($opportunity_details->rel_id, $opportunity_details->rel_type) . '">' . task_rel_name(null, $opportunity_details->rel_id, $opportunity_details->rel_type) . '</a>';
}
?>
    <div id="wrapper">
        <div class="content">
            <div class="row">
                <div class="panel panel-custom">
                    <div class="panel-heading">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                    class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel"><?= _l('opportunity_details') ?></h4>
                    </div>
                    <div class="modal-body wrap-modal wrap">
                        <div class="panel-body form-horizontal">
                            <div class="col-md-12 notice-details-margin">
                                <div class="col-sm-4 text-right">
                                    <label class="control-label"><strong><?= _l('title') ?> :</strong></label>
                                </div>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><?php if (!empty($opportunity_details->title)) echo $opportunity_details->title; ?></p>
                                </div>
                            </div>
                            <div class="col-md-12 notice-details-margin">
                                <div class="col-sm-4 text-right">
                                    <label class="control-label"><strong><?= _l('currency') ?> :</strong></label>
                                </div>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><?php if (!empty($opportunity_details->currenciesname)) echo $opportunity_details->currenciesname; ?></p>
                                </div>
                            </div>
                            <div class="col-md-12 notice-details-margin">
                                <div class="col-sm-4 text-right">
                                    <label class="control-label"><strong><?= _l('opportunity_value') ?> :</strong></label>
                                </div>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><?php if (!empty($opportunity_details->opportunity_value)) echo $opportunity_details->opportunity_value; ?></p>
                                </div>
                            </div>
                            <div class="col-md-12 notice-details-margin">
                                <div class="col-sm-4 text-right">
                                    <label class="control-label"><strong><?= _l('tags') ?> :</strong></label>
                                </div>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><?php echo get_tags($opportunity_details->tags, true); ?></p>
                                </div>
                            </div>
                            <div class="col-md-12 notice-details-margin">
                                <div class="col-sm-4 text-right">
                                    <label class="control-label"><strong><?= _l('source') ?> :</strong></label>
                                </div>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><?php if (!empty($opportunity_details->source_name)) echo $opportunity_details->source_name; ?></p>
                                </div>
                            </div>
                            <div class="col-md-12 notice-details-margin">
                                <div class="col-sm-4 text-right">
                                    <label class="control-label"><strong><?= _l('close') . ' ' . _l('date') ?>
                                            :</strong></label>
                                </div>
                                <div class="col-sm-8">
                                    <p class="form-control-static">
                                        <span><?= strftime(config_item('date_format'), strtotime($opportunity_details->days_to_close)) ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-12 notice-details-margin">
                                <div class="col-sm-4 text-right">
                                    <label class="control-label"><strong><?= _l('pipeline') ?> :</strong></label>
                                </div>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><?php if (!empty($opportunity_details->pipeline_name)) echo $opportunity_details->pipeline_name; ?></p>
                                </div>
                            </div>
                            <div class="col-md-12 notice-details-margin">
                                <div class="col-sm-4 text-right">
                                    <label class="control-label"><strong><?= _l('stage') ?> :</strong></label>
                                </div>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><?php if (!empty($opportunity_details->customer_group)) echo $opportunity_details->customer_group; ?></p>
                                </div>
                            </div>
                            <div class="col-md-12 notice-details-margin">
                                <div class="col-sm-4 text-right">
                                    <label class="control-label"><strong><?= _l('client_name') ?> :</strong></label>
                                </div>
                                <div class="col-sm-8">
                                    <p class="form-control-static">
                                        <?php
                                        $client_name = json_decode($opportunity_details->client_id);
                                        if (!empty($client_name)) {
                                            foreach ($client_name as $clientId) {
                                                echo opportunity_client_name($clientId) . '<br/';
                                            }
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-12 notice-details-margin">
                                <div class="col-sm-4 text-right">
                                    <label class="control-label"><strong><?= _l('assigne') ?> :</strong></label>
                                </div>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><?php if (!empty($opportunity_details->username)) echo $opportunity_details->username; ?></p>
                                </div>
                            </div>
                            <?php $custom_fields = get_custom_fields('opportunity');
                            if (!empty($custom_fields)) {
                                foreach ($custom_fields as $field) { ?>
                                    <?php $value = get_custom_field_value($opportunity_details->id, $field['id'], 'opportunity');
                                    if ($value == '') {
                                        continue;
                                    } ?>
                                    <div class="col-md-12 notice-details-margin">
                                        <div class="col-sm-4 text-right">
                                            <label class="control-label"><strong><?php echo $field['name']; ?>
                                                    :</strong></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <p class="form-control-static">
                                                <?php echo $value; ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php }
                            }
                            ?>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php init_tail(); ?>