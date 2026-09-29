<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head();?>

<?php $has_tab = false; ?>

<div id="wrapper" >

    <div class="content">

        <div class="row">

            <div class="col-md-3">

                <h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

                    <?php echo _l('settings'); ?>

                </h4>

                <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked">

                    <?php foreach ( $tabs as $tab ) {

                        if ( $tab['slug'] == $active_tab )
                            $has_tab = true;

                        ?>

                        <li class="<?php echo $tab['slug'] == $active_tab ? 'active' : ''?>">

                            <a href="<?php echo admin_url('extra_setting/setting?tab='.$tab['slug'] ); ?>"

                               data-group="">

                                <i class="<?php echo $tab['icon']?> menu-icon"></i>

                                <?php echo $tab['text']?>

                            </a>

                        </li>

                    <?php } ?>


                </ul>


            </div>



            <div class="col-md-9" id="es_setting_content">

                <?php


                if ( $has_tab )
                {
                    $this->load->view('tabs/v_setting_'.$active_tab );
                }
                else
                {

                    echo "Not found setting";

                }

                ?>

            </div>

        </div>



    </div>

</div>




<?php init_tail(); ?>


<script>


    (function($) {
        "use strict";

        $(document).ready(function (){


            $('.extra_setting_checkbox').change(function ( e ){

                var setting_name    = $(this).attr('id');
                var setting_val     = $(this).prop('checked') ? 1 : 0 ;

                if( setting_name )
                {

                    $.post( admin_url+'extra_setting/setting/save_setting' , {'name' : setting_name , 'value' : setting_val} ).done(function(response) {

                        response = JSON.parse(response);

                        alert_float('success', response.message);

                    });

                }


            })

            $('.extra_setting_input').change(function ( e ){

                var setting_name    = $(this).attr('id');
                var setting_val     = $(this).val() ;

                if ( setting_name )
                {

                    $.post( admin_url+'extra_setting/setting/save_setting' , {'name' : setting_name , 'value' : setting_val} ).done(function(response) {

                        response = JSON.parse(response);

                        alert_float('success', response.message);

                    });

                }


            })


            $('.extra_setting_select').change(function ( e ){

                var setting_name    = $(this).attr('id');
                var setting_val     = $(this).val() ;

                if( setting_name )
                {

                    $.post( admin_url+'extra_setting/setting/save_setting' , {'name' : setting_name , 'value' : setting_val} ).done(function(response) {

                        response = JSON.parse(response);

                        alert_float('success', response.message);

                    });

                }


            })


            /**
             * Required selectbox field
             */
            $('.extra_setting_select_required').change(function ( e ){

                var setting_name    = $(this).attr('id');
                var setting_val     = $(this).val() ;


                if ( setting_name )
                {


                    $.post( admin_url+'extra_setting/setting/save_setting' , {'name' : setting_name , 'value' : setting_val} ).done(function(response) {

                        response = JSON.parse(response);

                        alert_float('success', response.message);

                    });

                }



            })


        });


    })(jQuery);


</script>

<style>

    #es_setting_content .es_setting_row
    {

        margin-left: 30px;

    }

</style>
