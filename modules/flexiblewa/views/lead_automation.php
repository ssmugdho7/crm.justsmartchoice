<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
init_head();
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons tw-mb-2 sm:tw-mb-4">
                    <span class="tw-font-bold tw-text-xl">
                        <?php echo $title ?>
                    </span>
                    
                    <div class="clearfix"></div>
                </div>
                <div class="panel_s">
                    <div class="panel-heading">
                        <span class="tw-font-bold">
                            <?php echo $title ?>
                        </span>

                        <a href="#" data-toggle="modal" data-target="#flexiblewa_lead_rule_config" class="btn btn-link">
                            <i class="fa-regular fa-plus"></i>
                            <?php echo _l('flexiblewa_add_rule'); ?>
                        </a>
                        <a href="#" data-toggle="modal" data-target="#flexiblewa_action_sequence" class="btn btn-link">
                            <i class="fa-solid fa-boxes-stacked"></i>
                            <?php echo _l('flexiblewa_order_action'); ?>
                        </a>
                    </div>
                    <div class="panel-body">
                        <div class="panel-table-full">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <?php
                                            echo _l('flexiblewa_rule_name');
                                            ?>
                                        </th>
                                        <th>
                                            <?php
                                            echo _l('flexiblewa_when_event');
                                            ?>
                                        </th> 
                                        <th>
                                            <?php
                                            echo _l('flexiblewa_action');
                                            ?>
                                        </th>
                                        <th>
                                            <?php
                                            echo _l('flexiblewa_value');
                                            ?>
                                        </th>
                                        <th>
                                            <?php echo _l('flexiblewa_options'); ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rules as $rule) { ?>
                                        <tr class="has-row-options ">
                                            <td>
                                                <?php echo $rule['rule_name']; ?>
                                            </td>
                                            <td>
                                                <?php echo flexiblewa_get_when_event_name($rule['when_event']); ?>
                                            </td>
                                            <td>
                                                <?php echo _flexiblewa_lang($rule['rule_action']); ?>
                                            </td>
                                            <td>
                                                <?php echo $rule['display_value']; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo admin_url('flexiblewa/delete_generic_rule/' . $rule['id']); ?>/lead"
                                                    class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete"
                                                    title="<?php echo _l('flexiblewa_delete') ?>">
                                                    <i class="fa-regular fa-trash-can fa-lg"></i>
                                                </a>
                                            </td>
                                        </tr>
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
<input type="hidden" value="<?php echo admin_url('flexiblewa/ajax'); ?>" id="flexiblewa_ajax_url" />
<?php flexiblewa_modals(FLEXIBLEWA_LEAD_RULE_TYPE); ?>
<?php init_tail(); ?>
</body>

</html>