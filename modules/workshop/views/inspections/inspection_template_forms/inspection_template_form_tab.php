<?php if(isset($inspection_forms) && count($inspection_forms) > 0){ ?>
    <?php foreach ($inspection_forms as $key => $inspection_form) { ?>
        <?php if(isset($form_active)){ ?>
            <li class="nav-item dragger <?php if($form_active == $inspection_form['id']){echo "active";} ?>">
            <?php }else{ ?>
                <li class="nav-item dragger <?php if($key == 0){echo "active";} ?>">
                <?php } ?>

                <input type="hidden" name="order" data-form_id="<?php echo html_entity_decode($inspection_form['id']) ?>" value="<?php echo html_entity_decode($inspection_form['form_order']) ?>">
                <a class="nav-link " id="template_form_<?php echo html_entity_decode($inspection_form['id']) ?>-tab" data-toggle="tab" href="#template_form_<?php echo html_entity_decode($inspection_form['id']) ?> " role="tab" aria-controls="template_form<?php echo html_entity_decode($inspection_form['id']) ?>" aria-selected="true" data-id="<?php echo html_entity_decode($inspection_form['id']) ?>">

                    <span class="tw-flex tw-text-justify">
                        <?php echo html_entity_decode($inspection_form['name']); ?>
                    </span>
                    <span class="pull-right padding-5">
                        
                    </span>
                    <div class="clearfix"></div>
                </a>
            </li>
        <?php } ?>
        <?php } ?>