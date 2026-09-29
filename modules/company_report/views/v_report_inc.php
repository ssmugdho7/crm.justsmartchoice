
<div class="mbot10">
    <a  href="#" onclick="company_report_column_settings_toggle(); return false;"> <?php echo _l('customer_report_show_hide')?> </a>
</div>

<div class="row mbot20 hide" id="column_settings">

    <div class="col-md-8">


        <div id="div_filter_keys">

            <?php
            foreach ( $filter_keys as $filter_key )
            {

                $checked = in_array( $filter_key , $record_keys ) ? "checked" : "" ;

                ?>

                <div class="filter_keys_items">

                    <div style="margin: 0px!important;" class="checkbox checkbox-info">

                        <input type="checkbox" <?php echo $checked;?> id="customer_report_<?php echo $filter_key?>" name="filter_keys[]" value="<?php echo $filter_key?>" class="company_report_filter_keys">

                        <label for="customer_report_<?php echo $filter_key?>"><?php echo _l('customer_report_'.$filter_key) ?></label>

                    </div>

                </div>

            <?php } ?>

        </div>

    </div>

    <div class="col-md-4">

        <h4><?php echo _l('customer_report_setting_message_1')?></h4>
        <h4><?php echo _l('customer_report_setting_message_2')?></h4>

        <a class="btn btn-primary" onclick="save_company_report_settings(); return false;"><?php echo _l('customer_report_setting_save')?></a>

    </div>

</div>


<style>

    #div_filter_keys .filter_keys_items{

        background-color: white;
        padding: 8px;
        margin-bottom: 2px;
        cursor: move;

    }

    #div_filter_keys .filter_keys_items label{

        cursor: move;

    }

</style>
