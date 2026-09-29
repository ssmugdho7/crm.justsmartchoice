<?php if(isset($inspection_template_forms) && count($inspection_template_forms) > 0){ ?>
    <?php foreach ($inspection_template_forms as $key => $inspection_template_form) { ?>
        <?php if(isset($form_active)){ ?>
            <li class="nav-item dragger <?php if($form_active == $inspection_template_form['id']){echo "active";} ?>">
            <?php }else{ ?>
                <li class="nav-item dragger <?php if($key == 0){echo "active";} ?>">
                <?php } ?>

                <input type="hidden" name="order" data-form_id="<?php echo new_html_entity_decode($inspection_template_form['id']) ?>" value="<?php echo new_html_entity_decode($inspection_template_form['form_order']) ?>">
                <a class="nav-link " id="template_form_<?php echo new_html_entity_decode($inspection_template_form['id']) ?>-tab" data-toggle="tab" href="#template_form_<?php echo new_html_entity_decode($inspection_template_form['id']) ?> " role="tab" aria-controls="template_form<?php echo new_html_entity_decode($inspection_template_form['id']) ?>" aria-selected="true" data-id="<?php echo new_html_entity_decode($inspection_template_form['id']) ?>">

                    <span class="tw-flex tw-text-justify">
                        <?php echo new_html_entity_decode($inspection_template_form['name']); ?>
                    </span>
                    <span class="pull-right padding-5">
                        <i class="fa fa-pen-to-square btn-primary btn btn-icon" onclick="inspection_template_form_modal(<?php echo new_html_entity_decode($inspection_template_form['id']) ?>); return false;"></i>
                        <i class="fa-regular fa-trash-can btn-danger  btn btn-icon" onclick="delete_inspection_template_form(this, <?php echo new_html_entity_decode($inspection_template_form['id']) ?>, <?php echo new_html_entity_decode($inspection_template_form['inspection_template_id']) ?>); return false;"></i>
                    </span>
                    <div class="clearfix"></div>
                </a>
            </li>
        <?php } ?>
        <?php } ?>