<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'name',
    'type',
    'draf',
    'final_doc'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'eng_drawings';

$join = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], ['id']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
  $row = [];
  for ($i = 0; $i < count($aColumns); $i++) {
    $_data = $aRow[$aColumns[$i]];
    if ($aColumns[$i] == 'type') {
      $_data = drawing_type(true,$aRow['type']);
    }
    if ($aColumns[$i] == 'draf') {
        $_data = get_drawing_file($aRow['draf'], $aRow['type'], false);
    }
    if ($aColumns[$i] == 'final_doc') {
        $_data = get_drawing_file($aRow['final_doc'], $aRow['type'], true);
    }
    $row[] = $_data;
  }
  $options = icon_btn('engineering_projects/drawings/drawing/' . $aRow['id'], 'pencil-square-o', 'btn-default', [
      'data-id' => $aRow['id'],

  ]);
  $row[] = $options .= icon_btn('engineering_projects/drawings/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');

  //$row['DT_RowClass'] = 'has-row-options';
  $output['aaData'][] = $row;
}
