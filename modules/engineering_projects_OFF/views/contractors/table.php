<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'contractor',
    'email',
    'phone',
    'address'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'eng_contractors';

$join = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], ['id']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
  $row = [];
  for ($i = 0; $i < count($aColumns); $i++) {
    $_data = $aRow[$aColumns[$i]];
    if ($aColumns[$i] == 'contractor') {
      $_data = $aRow['contractor'];
    }
    $row[] = $_data;
  }
  $options = icon_btn('#' . $aRow['id'], 'pencil-square-o', 'btn-default', [
      'data-toggle' => 'modal',
      'data-target' => '#contractor_modal',
      'data-id' => $aRow['id'],

  ]);
  $row[] = $options .= icon_btn('engineering_projects/contractors/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');

  //$row['DT_RowClass'] = 'has-row-options';
  $output['aaData'][] = $row;
}
