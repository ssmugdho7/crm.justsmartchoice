<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'id',
    'mailwizz_marketing_campaign_name',
    'mailwizz_marketing_campaign_status',
    'mailwizz_marketing_campaign_group',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'mailwizz_marketing_campaign';
$filter = [];
$where = [];
$statusIds = [];
$join = [];
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {

    $row = [];
    $row[] = $aRow['id'];
    $row[] = $aRow['mailwizz_marketing_campaign_name'];
    $row[] = $aRow['mailwizz_marketing_campaign_status'];
    $row[] = $aRow['mailwizz_marketing_campaign_group'];
    $row[] = '<a href="' . admin_url('mailwizz_marketing/campaign/delete_campaign/' . $aRow['id']) . '">Delete</a>';

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
