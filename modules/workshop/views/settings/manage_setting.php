<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">

            <div class="col-md-3">
                <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked">
                    <?php
                    $i = 0;
                    foreach($tab as $gr){
                        ?>
                        <li<?php if($i == 0){echo " class='active'"; } ?>>
                            <a href="<?php echo admin_url('workshop/setting?group='.$gr); ?>" data-group="<?php echo new_html_entity_decode($gr); ?>">
                                <?php

                                $icon['general_settings'] = '';
                                $icon['appointment_types'] = '';
                                $icon['holidays'] = '';
                                $icon['manufacturers'] = '';
                                $icon['categories'] = '';
                                $icon['models'] = '';
                                $icon['delivery_methods'] = '';
                                $icon['fieldsets'] = '';
                                $icon['intervals'] = '';
                                $icon['inspection_templates'] = '';
                                $icon['prefixs'] = '';
                                $icon['permissions'] = '';
                                $icon['reset_data'] = '';

                                echo new_html_entity_decode($icon[$gr] .' '. _l('wshop_'.$gr)); 

                                ?>
                            </a>
                        </li>
                        <?php $i++; } ?>
                    </ul>
                </div>
                <div class="col-md-9">
                    <div class="panel_s">
                        <div class="panel-body">

                            <?php $this->load->view($tabs['view']); ?>

                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <?php echo form_close(); ?>
            <div class="btn-bottom-pusher"></div>
        </div>
    </div>
    <div id="new_version"></div>
    <div id="modal_wrapper"></div>
    <?php init_tail(); ?>

    <?php 
    $viewuri = $_SERVER['REQUEST_URI'];
    ?>

    <?php if(!(strpos($viewuri,'admin/workshop/setting?group=categories') === false)){ 
        require 'modules/workshop/assets/js/settings/categories/manage_js.php';
    }elseif(!(strpos($viewuri,'admin/workshop/setting?group=holidays') === false)){
        require 'modules/workshop/assets/js/settings/holidays/manage_js.php';
    }elseif(!(strpos($viewuri,'admin/workshop/setting?group=manufacturers') === false)){
        require 'modules/workshop/assets/js/settings/manufacturers/manage_js.php';
    }elseif(!(strpos($viewuri,'admin/workshop/setting?group=delivery_methods') === false)){
        require 'modules/workshop/assets/js/settings/delivery_methods/manage_js.php';
    }elseif(!(strpos($viewuri,'admin/workshop/setting?group=intervals') === false)){
        require 'modules/workshop/assets/js/settings/intervals/manage_js.php';
    }elseif(!(strpos($viewuri,'admin/workshop/setting?group=models') === false)){
        require 'modules/workshop/assets/js/settings/models/manage_js.php';
    }elseif(!(strpos($viewuri,'admin/workshop/setting?group=appointment_types') === false)){
        require 'modules/workshop/assets/js/settings/appointment_types/manage_js.php';
    }elseif(!(strpos($viewuri,'admin/workshop/setting?group=fieldsets') === false)){
        require 'modules/workshop/assets/js/settings/fieldsets/manage_js.php';
    }elseif(!(strpos($viewuri,'admin/workshop/setting?group=inspection_templates') === false)){
        require 'modules/workshop/assets/js/settings/inspection_templates/manage_js.php';
    }elseif(!(strpos($viewuri,'admin/workshop/setting?group=custom_fields') === false)){
        require 'modules/workshop/assets/js/settings/custom_fields/manage_js.php';
    }elseif(!(strpos($viewuri,'admin/workshop/setting?group=permissions') === false)){
        require 'modules/workshop/assets/js/settings/permissions/permissions_js.php';
    }elseif(!(strpos($viewuri,'admin/workshop/setting?group=reset_data') === false)){
        require 'modules/workshop/assets/js/settings/reset_data/reset_data_js.php';
    }

    ?>
</body>
</html>
