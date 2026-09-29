<?php

defined('BASEPATH') or exit('No direct script access allowed');

$where = [];

if(isset($_GET['client']) && !empty($_GET['client'])){
    $where = [
        'AND client_id=' . $_GET['client'],
    ];
}
if(isset($_GET['project']) && !empty($_GET['project'])){
    $where = [
        'AND project_id=' . $_GET['project'],
    ];
}
$aColumns = [
    'id',
    'domain_name',
    'registrar',
    'purchase_date',
    'expiry_date',
    'dns_hosting',
    'status',
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'domain_manager';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, []);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'domain_name') {
            $_data =  e($_data);

            $_data .= '<div class="row-options">';

         

           

            $_data .= ' <a href="' . admin_url('domain_manager/edit/' . $aRow['id']) . '">' . _l('edit') . '</a>';

            if (staff_can('delete',  'domain_manager')) {
                $_data .= ' | <a href="' . admin_url('domain_manager/delete/' . $aRow['id']) . '" class="_delete">' . _l('delete') . '</a>';
            }

            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'purchase_date' ) {

            if ($_data == '0000-00-00' || $_data == null) {
                $_data = " - ";
            }else{
                $_data = "<span>".e(_d($_data))."</span>";
            }
         
        }  elseif ($aColumns[$i] == 'expiry_date') {
            if ($_data == '0000-00-00' || $_data == null) {
                $_data = " - ";
            }else{
                // Convert the date string to a timestamp
                $expiryTime = strtotime($_data);
                $currentTime = time();
                // Calculate timestamp for one month from now
                $oneMonthAhead = strtotime("+1 month", $currentTime);

                // If the expiry date is in the past, it's expired
                if ($expiryTime < $currentTime) {
                    $_data = "<span class='text-danger'>".e(_d($_data))."</span>";
                }
                // If the expiry date is within the next month, it's expiring soon
                elseif ($expiryTime <= $oneMonthAhead) {
                    $_data = "<span class='text-warning'>".e(_d($_data))."</span>";
                }
                // Otherwise, it's still active
                else {
                    $_data = "<span class='text-success'>".e(_d($_data))."</span>";
                }
            }
        
          
        } elseif ($aColumns[$i] == 'dns_hosting') {
            if($_data == 'enabled'){
                $_data = '<span class="label text-success" style="border: 1px solid rgb(0, 175, 53);">'.strtoupper(_l($_data)).'</span>';
            }else{
                $_data = '<span class="label text-danger " style="border: 1px solid #ff1100;">'.strtoupper(_l($_data)).'</span>';
            }
           
        }elseif ($aColumns[$i] == 'status') {
            if($_data == 'active'){
                $_data = '<span class="label text-success" style="border: 1px solid rgb(0, 175, 53);">'.strtoupper(_l($_data)).'</span>';
            }else{
                $_data = '<span class="label text-danger " style="border: 1px solid #ff1100;">'._l($_data).'</span>';
            }
          
        }
        
        $row[] = $_data;
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
