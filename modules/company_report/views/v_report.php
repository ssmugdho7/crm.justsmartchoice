<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">

    <div class="content">

        <?php echo form_open(admin_url('company_report/report') , [ 'id' => 'company_report' ] ); ?>

        <?php $this->load->view('v_report_inc') ?>

        <div class="row mbot20">

            <div class="col-md-3">
                <select class="selectpicker fnc_reload_filter"  name="year" data-width="100%" >
                    <?php
                    for( $year = $start_year ; $year <= date('Y') ; $year++ )
                    {
                        $selected = $year == $query_year ? "selected" : "" ;

                        echo "<option $selected value='$year'>$year</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <select class="selectpicker fnc_reload_filter" name="currency" data-width="100%">
                    <?php
                    foreach ( $currencies as $currency)
                    {
                        $selected = $currency->id == $query_currency ? "selected" : "" ;

                        echo "<option $selected value='$currency->id'>$currency->name</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-4">
                <select class="selectpicker fnc_reload_filter" name="staff_id" data-width="100%" data-none-selected-text="<?php echo _l('customer_report_select_staff')?>">
                    <option value=""></option>
                    <?php
                    foreach ( $staff as $staf )
                    {
                        $selected = $staf["staffid"] == $query_staff_id ? "selected" : "" ;

                        echo "<option $selected value='".$staf["staffid"]."'>". $staf["firstname"]." ".$staf["lastname"] ."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" id="btn_get_report_lists" class="btn-primary btn"> <?php echo _l('yearly_activity_get_reports')?> </button>
            </div>

            <div class="clearfix"></div>

        </div>

        <?php echo form_close()?>

        <div class="row">

            <div class="col-md-12">
                <p class="text-danger"><?php echo _l('customer_report_info')?></p>
            </div>

            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body panel-table-full">

                        <table class="table dt-table table-striped" >

                            <thead>
                                <tr>

                                    <th class="hidden">#</th>
                                    <?php

                                    if( !empty( $query_staff_id ) )
                                        echo "<th> "._l('custom_field_staff')." </th>";

                                    for ( $index = 0 ; $index < 13 ; $index++ )
                                    {
                                        echo "<th>". $records["reports"][ $index ] ."</th>";
                                    }

                                    ?>
                                    <th><?php echo _l('invoice_total')?></th>

                                </tr>
                            </thead>

                            <tbody>

                            <?php foreach ( $record_keys as $ind => $record_key ) { ?>

                                <tr>
                                    <td class="hidden"> <?php echo $ind+1 ?> </td>
                                    <?php


                                    if( !empty( $query_staff_id ) )
                                        echo "<td> ".get_staff_full_name($query_staff_id)." </td>";


                                    $total = 0;
                                    for ( $index = 0 ; $index < 13 ; $index++ )
                                    {
                                        $value = $records[$record_key][ $index ];

                                        if( !empty( $value ) && is_numeric($value) )
                                            $total += $value;

                                        if( $index != 0 && in_array( $record_key , $filter_amount_keys ) )
                                            $value = app_format_money( $value , $query_currency );

                                        echo "<th>$value</th>";
                                    }

                                    if( in_array( $record_key , $filter_amount_keys ) )
                                        $total = app_format_money( $total , $query_currency );

                                    ?>
                                    <td><?php echo $total?></td>
                                </tr>

                            <?php } ?>

                            </tbody>

                        </table>

                    </div>
                </div>

            </div>



            <?php

            $report_labels = [];
            for ( $index = 1 ; $index < 13 ; $index++ )
            {
                $report_labels[] = $records["reports"][ $index ] ;
            }

            $report_data = [
                'labels' => $report_labels ,
                'datasets' => []
            ];

            foreach ( $record_keys as $ind => $record_key )
            {


                if( in_array( $record_key , $filter_amount_keys ) )
                {

                    $dt_label = $records[$record_key][ 0 ];

                    $dt_data = [];

                    for ( $index = 1 ; $index < 13 ; $index++ )
                    {
                        $value = $records[$record_key][ $index ];

                        if( empty( $value ) )
                            $value = 0;

                        $dt_data[] = $value;

                    }


                    $report_data['datasets'][] = [

                        'label' => $dt_label ,

                        'data' => $dt_data ,

                        'backgroundColor' => $filter_color_keys[$record_key] ,

                        'borderColor' => $filter_color_keys[$record_key] ,

                        'borderWidth' => 1 ,


                    ];

                }

            }


            if ( !empty( $report_data['datasets'] ) ) { ?>

                <div class="col-md-12">

                    <div class="panel_s">

                        <div class="panel-body">

                            <div class="relative" style="max-height:600px;">

                                <canvas class="chart" height="600" id="customer_report_char"></canvas>

                            </div>

                        </div>

                    </div>

                </div>

            <?php } ?>



        </div>

    </div>

</div>


<?php init_tail(); ?>

<?php

$currency_data = get_currency( $query_currency );
$query_currency_symbol = !empty( $currency_data->symbol ) ? $currency_data->symbol : $query_currency ;

?>

<script>

    (function($) {
        "use strict";

        $(function() {

            $('.fnc_reload_filter').change(function (){

                $('#btn_get_report_lists').click();

            })

            $('#div_filter_keys').sortable();

            <?php if ( !empty( $report_data['datasets'] ) ) { ?>

                var customerReportChar = new Chart($('#customer_report_char'), {

                    type: 'bar',

                    data: <?php echo json_encode($report_data); ?>,

                    options: {

                        maintainAspectRatio: false,

                        tooltips: {

                            callbacks: {

                                label: function(tooltipItem, data) {

                                    return '<?php echo $query_currency_symbol?> ' + format_money(tooltipItem.yLabel , '<?php echo $query_currency_symbol?>' )

                                }

                            }

                        },

                        scales: {
                            yAxes: [{

                                ticks: {

                                    callback: function(value) {

                                        return '<?php echo $query_currency_symbol?> ' + format_money( value , '<?php echo $query_currency_symbol?>' )

                                    },

                                    beginAtZero: true,

                                }

                            }]

                        },

                    }

                });


            <?php }?>


        });

    })(jQuery);



</script>

<?php $this->load->view('v_report_js') ?>

</body>

</html>
