<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php hooks()->do_action('app_customers_portal_head'); ?>

<div class="row">

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-6">
                                <h4>
                                    <?php echo html_entity_decode($device->code .' '. $device->name); ?>
                                    <?php if($device->status == 1){ ?>
                                        <span class="label label-success"><?php echo _l('wshop_active_label') ?></span>
                                    <?php } ?>
                                </h4>
                            </div>
                            <div class="col-md-6">
                                <?php if(has_permission('workshop_device', '', 'create') || has_permission('workshop_device', '', 'edit')){ ?>
                                    <div class=" pull-right">
                                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <?php echo _l('more'); ?> <span class="caret"></span> </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a href="#"
                                                onclick="device_modal(<?php echo html_entity_decode($device->id) ?>); return false;"><?php echo _l('edit'); ?></a>
                                            </li>
                                            <li><a href="#" onclick="transfer_ownership_modal(<?php echo html_entity_decode($device->id); ?>); return false;"><?php echo _l('wshop_transfer_ownership'); ?></a>
                                            </li>
                                        </ul>
                                    </div>

                                <?php } ?>
                                <a href="<?php echo site_url('workshop/client/devices'); ?>" class="btn btn-default pull-right display-block mright5">
                                    <?php echo _l('wshop_back'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="no-mbot">

                        <div class="row">
                            <div class="horizontal-scrollable-tabs preview-tabs-top">
                                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                                <div class="horizontal-tabs">
                                    <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                                        <li role="presentation" class="active">
                                            <a href="#detail" aria-controls="detail"  class="detail" role="tab" data-toggle="tab">
                                                <span class="fa-brands fa-usps"></span>&nbsp;<?php echo _l('wshop_detail'); ?>
                                            </a>
                                        </li>
                                        <li role="presentation" class="">
                                            <a href="#repair_order" aria-controls="repair_order"  class="repair_order" role="tab" data-toggle="tab">
                                                <span class="fa-solid fa-arrow-down-wide-short"></span>&nbsp;<?php echo _l('wshop_repair_orders'); ?>
                                            </a>
                                        </li>
                                        <li role="presentation">
                                            <a href="#inspection" aria-controls="inspection" role="tab" data-toggle="tab">
                                                <span class="fa-solid fa-bolt"></span>&nbsp;<?php echo _l('wshop_inspections'); ?>
                                            </a>
                                        </li>
                                        <li role="presentation">
                                            <a href="#workshop" aria-controls="workshop" role="tab" data-toggle="tab">
                                                <span class="fa fa-history"></span>&nbsp;<?php echo _l('wshop_workshops'); ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <br>
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="detail">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-7">
                                                <table class="table border table-striped no-mtop">
                                                    <tbody>
                                                        <?php 
                                                        $category_name = '';
                                                        $manufacturer_name = '';
                                                        $model = wshop_get_model($device->model_id);
                                                        if($model){
                                                            $category_name = wshop_get_category_name($model->category_id);
                                                            $manufacturer_name = wshop_get_manufacturer_name($model->manufacturer_id);
                                                        }
                                                        ?>

                                                        <tr class="project-overview">
                                                            <td class="bold" width="30%"><?php echo _l('client'); ?></td>
                                                            <td><?php echo get_company_name($device->client_id) ; ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_code'); ?></td>
                                                            <td><?php echo html_entity_decode($device->code) ; ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_category'); ?></td>
                                                            <td><?php echo html_entity_decode($category_name) ; ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_model'); ?></td>
                                                            <td><?php echo wshop_get_model_name($device->model_id) ; ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_manufacturer'); ?></td>
                                                            <td><?php echo html_entity_decode($manufacturer_name) ; ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_prod_date'); ?></td>
                                                            <td><?php echo _d($device->prod_date ?? '') ; ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_purchase_date'); ?></td>
                                                            <td><?php echo _d($device->purchase_date ?? '') ; ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_warranty_start_date'); ?></td>
                                                            <td><?php echo _d($device->warranty_start_date ?? '') ; ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_warranty_period_months'); ?></td>
                                                            <td><?php echo html_entity_decode($device->warranty_period_months ?? '').' '. _l('wshop_month_s') ; ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_warranty_expiry_date'); ?></td>
                                                            <td><?php echo _d($device->warranty_expiry_date ?? '') ; ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_warranty_expiring_alert'); ?></td>
                                                            <td><?php echo _d($device->warranty_expiring_alert ?? '').' '. _l('wshop_day_s'); ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_last_maintenance_date'); ?></td>
                                                            <td><?php echo _d($device->last_maintenance ?? '') ; ?></td>
                                                        </tr>
                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_next_maintenance_date'); ?></td>
                                                            <td><?php echo _d($device->next_maintenance ?? '') ; ?></td>
                                                        </tr>
                                                        <?php 
                                                        $warranty_status = '---';
                                                        if($device->warranty_expiry_date != null){
                                                            if(strtotime($device->warranty_expiry_date) > strtotime(date('Y-m-d'))){
                                                                $warranty_status = '<span class="label label-success">'._l('wshop_being_under_warranty').'</span>';
                                                            }else{
                                                                $warranty_status = '<span class="label label-warning">'._l('wshop_out_of_warranty').'</span>';
                                                            }
                                                        }
                                                        ?>

                                                        <tr class="project-overview">
                                                            <td class="bold"><?php echo _l('wshop_warranty_status'); ?></td>
                                                            <td><?php echo html_entity_decode($warranty_status) ; ?></td>
                                                        </tr>
                                                        
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="row">
                                                    <div class="col-md-12">

                                                        <?php
                                                        if(isset($device_images)){
                                                            $folder = 'commodity_item_file';

                                                            $large_img_list = '';
                                                            $small_img_list = '';
                                                            if(count($device_images) > 0){
                                                                $large_img_list .= '<div class="preview-pic tab-content">';
                                                                $small_img_list .= '<div class=""><ul class="preview-thumbnail nav nav-tabs no-mbot tw-flex">';
                                                                foreach ($device_images as $kimg => $f) {

                                                                    if($f['type'] == 'main_image'){

                                                                        $src = $f['site_url'];
                                                                        $small_src = $f['site_url'];

                                                                    }else{
                                                                        $src = site_url(DEVICE_UPLOAD_PATH.$f['rel_id'].'/'.$f['file_name']);
                                                                        $small_src = site_url(DEVICE_UPLOAD_PATH.$f['rel_id'].'/'.$f['file_name']);
                                                                    }


                                                                    $large_img_list .= '<a href="'.$src.'" class="contain_image containt-image tab-pane '.($kimg == 0 ? 'active' : '').'" id="pic-'.$kimg.'" data-lightbox="roadtrip"><img class="w-100 img img-rounded img-thumbnail property-view" src="'.$src.'"></a>';
                                                                    if($kimg < 3){
                                                                        $small_img_list .= '<div data-target="#pic-'.$kimg.'" data-toggle="tab" aria-expanded="true"><img class="w-100 img img-rounded img-thumbnail property-thumbnail" src="'.$small_src.'"></div>';
                                                                    }elseif($kimg == 3){
                                                                        $remaining_images = count($device_attachments)-3;
                                                                        $small_img_list .= '<div data-target="#pic-'.$kimg.'" data-toggle="tab" aria-expanded="true" class="epl-gallery-item epl-gallery-item--desktop epl-gallery-item-4"><img class="w-100 img img-rounded img-thumbnail property-thumbnail" src="'.$small_src.'">
                                                                        <div class="epl-gallery-remaining epl-gallery-remaining--desktop"><div class="epl-gallery-remaining__symbol"> <span class="epl-gallery-remaining__symbol">+</span><span class="epl-gallery-remaining__value">'.$remaining_images.'</span></div></div>
                                                                        </div>';
                                                                    }else{
                                                                        $small_img_list .= '<div data-target="#pic-'.$kimg.'" data-toggle="tab" aria-expanded="true"><img class="w-100 img img-rounded img-thumbnail property-thumbnail hide" src="'.$small_src.'"></div>';
                                                                    }

                                                                }
                                                                $large_img_list .= '</div>';
                                                                $small_img_list .= '</ul></div>';
                                                                echo new_html_entity_decode($large_img_list);
                                                            }
                                                            ?>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="col-md-12 tw-flex">
                                                        <?php echo new_html_entity_decode($small_img_list); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <hr class="no-mbot">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-12">
                                                <h4 class="tw-font-semibold"><?php echo _l('wshop_fieldset') ?></h4>
                                            </div>
                                            <?php   
                                            $fieldset_id = wshop_get_fieldset_id_by_model($device->model_id);
                                            $cf = wshop_get_custom_fields('fieldset_'.$fieldset_id);
                                            $custom_field_index = 0;

                                            ?>
                                            <?php if(count($cf) > 0){ ?>
                                                <div class="col-md-6">
                                                    <table class="table border table-striped no-mtop">
                                                        <tbody>
                                                            <?php 
                                                            foreach ($cf as $key => $custom_field) {
                                                                if(count($cf) > 1){
                                                                    if($key >= (int)(count($cf)/2)){
                                                                        continue;
                                                                    }
                                                                }
                                                                $custom_field_index = $key;
                                                                $val = wshop_get_custom_field_value($device->id, $custom_field['id'], 'fieldset_'.$fieldset_id);
                                                                if ($custom_field['type'] == 'textarea') {
                                                                    $val = clear_textarea_breaks($val);
                                                                }
                                                                $custom_field_value = $val;
                                                                if(is_null($val) || $val == ''){
                                                                    continue;
                                                                }
                                                                echo '<tr class="project-overview">
                                                                <td class="bold" width="30%">'.$custom_field['name'].'</td>
                                                                <td>'.check_for_links($custom_field_value).'</td>
                                                                </tr>';
                                                            } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <table class="table border table-striped no-mtop">
                                                        <tbody>
                                                            <?php 
                                                            foreach ($cf as $key => $custom_field) {
                                                                if($key < $custom_field_index+1){
                                                                    continue;
                                                                }

                                                                $val = wshop_get_custom_field_value($device->id, $custom_field['id'], 'fieldset_'.$fieldset_id);
                                                                if ($custom_field['type'] == 'textarea') {
                                                                    $val = clear_textarea_breaks($val);
                                                                }
                                                                $custom_field_value = $val;
                                                                if(is_null($val) || $val == ''){
                                                                    continue;
                                                                }
                                                                echo '<tr class="project-overview">
                                                                <td class="bold" width="30%">'.$custom_field['name'].'</td>
                                                                <td>'.check_for_links($custom_field_value).'</td>
                                                                </tr>';
                                                            } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-12">
                                                <h4 class="tw-font-semibold"><?php echo _l('wshop_customfields') ?></h4>
                                            </div>
                                            <?php   
                                            $cf = get_custom_fields('wshop_device');
                                            $custom_field_index = 0;
                                            ?>
                                            <?php if(count($cf) > 0){ ?>
                                                <div class="col-md-6">
                                                    <table class="table border table-striped no-mtop">
                                                        <tbody>
                                                            <?php 
                                                            foreach ($cf as $key => $custom_field) {
                                                                if(count($cf) > 1){
                                                                    if( $key >= (int)(count($cf)/2)){
                                                                        continue;
                                                                    }
                                                                }
                                                                $custom_field_index = $key;
                                                                $val = get_custom_field_value($device->id, $custom_field['id'], 'wshop_device');
                                                                if ($custom_field['type'] == 'textarea') {
                                                                    $val = clear_textarea_breaks($val);
                                                                }
                                                                $custom_field_value = $val;
                                                                if(is_null($val) || $val == ''){
                                                                    continue;
                                                                }
                                                                echo '<tr class="project-overview">
                                                                <td class="bold" width="30%">'.$custom_field['name'].'</td>
                                                                <td>'.check_for_links($custom_field_value).'</td>
                                                                </tr>';
                                                            } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <table class="table border table-striped no-mtop">
                                                        <tbody>
                                                            <?php 
                                                            foreach ($cf as $key => $custom_field) {
                                                                if($key < $custom_field_index+1){
                                                                    continue;
                                                                }

                                                                $val = get_custom_field_value($device->id, $custom_field['id'], 'wshop_device');
                                                                if ($custom_field['type'] == 'textarea') {
                                                                    $val = clear_textarea_breaks($val);
                                                                }
                                                                $custom_field_value = $val;
                                                                if(is_null($val) || $val == ''){
                                                                    continue;
                                                                }
                                                                echo '<tr class="project-overview">
                                                                <td class="bold" width="30%">'.$custom_field['name'].'</td>
                                                                <td>'.check_for_links($custom_field_value).'</td>
                                                                </tr>';
                                                            } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <hr class="no-mbot">
                                    </div>

                                    <div class="col-md-12">
                                        <h4 class="tw-font-semibold"><?php echo _l('wshop_description') ?></h4>
                                        <p class=""><?php echo new_html_entity_decode(check_for_links($device->description)); ?></p>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="repair_order">
                                	<div class="col-md-12">
                                		<?php $this->load->view('clients/repair_jobs/table_html'); ?>
                                	</div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="inspection">
                                    <div class="col-md-12">
                                    	<?php $this->load->view('clients/inspections/table_html'); ?>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="workshop">
                                    <div class="col-md-12">
                                    	<?php $this->load->view('clients/workshops/table_html'); ?>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        

                    </div>
                </div>
            </div>
        </div>
    </div>

<?php workshop_client_init_tail(); ?>