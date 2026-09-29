<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'ip',
    'created_at',
    '(SELECT file_path FROM ' . db_prefix() . 'quick_share_uploads WHERE id=' . db_prefix() . 'quick_share_downloads.download_id) as downloadedFile',
];

$sIndexColumn = 'download_id';
$sTable       = db_prefix() . 'quick_share_downloads';

$join = [];
$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'download_id',
]);

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $row[] = $aRow['downloadedFile'];
        $row[] = $aRow['ip'];
        $row[] = $aRow['created_at'];
    }

    $output['aaData'][] = $row;
}
